<?php

namespace App\Services;

use App\Contracts\Services\DashboardServiceInterface;
use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Enrollment;
use App\Models\GuardianStudent;
use App\Models\Student;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService implements DashboardServiceInterface
{
    /**
     * Get dashboard data for guardian
     */
    public function getGuardianDashboardData(int $guardianId): array
    {
        // Get guardian's students
        $studentIds = GuardianStudent::where('guardian_id', $guardianId)
            ->pluck('student_id');

        $students = Student::whereIn('id', $studentIds)
            ->with(['enrollments' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->get();

        // Get recent enrollments
        $recentEnrollments = Enrollment::where('guardian_id', $guardianId)
            ->with('student')
            ->latest()
            ->limit(5)
            ->get();

        // Get pending payments
        $pendingPayments = Enrollment::where('guardian_id', $guardianId)
            ->whereIn('payment_status', [PaymentStatus::PENDING, PaymentStatus::PARTIAL])
            ->with('student')
            ->get();

        $totalBalance = $pendingPayments->sum('balance_cents') / 100;

        return [
            'students' => $students,
            'recent_enrollments' => $recentEnrollments,
            'pending_payments' => $pendingPayments,
            'total_balance' => $totalBalance,
            'announcements' => $this->getAnnouncements(),
            'statistics' => [
                'total_students' => $students->count(),
                'active_enrollments' => Enrollment::where('guardian_id', $guardianId)
                    ->where('status', EnrollmentStatus::ENROLLED)
                    ->count(),
                'pending_applications' => Enrollment::where('guardian_id', $guardianId)
                    ->where('status', EnrollmentStatus::PENDING)
                    ->count(),
                'total_paid' => Enrollment::where('guardian_id', $guardianId)
                    ->sum('amount_paid_cents') / 100,
            ],
        ];
    }

    /**
     * Get dashboard data for registrar
     */
    public function getRegistrarDashboardData(): array
    {
        $this->logActivity('getRegistrarDashboardData', []);

        return Cache::remember('registrar_dashboard', 300, function () {
            $currentYear = date('Y').'-'.(date('Y') + 1);

            return [
                'quick_stats' => $this->getQuickStats(),
                'enrollment_statistics' => $this->getEnrollmentStatistics(['school_year' => $currentYear]),
                'recent_activities' => $this->getRecentActivities(),
                'pending_tasks' => $this->getPendingTasks('registrar'),
                'payment_statistics' => $this->getPaymentStatistics(),
                'grade_distribution' => $this->getGradeLevelDistribution(),
                'enrollment_trends' => $this->getEnrollmentTrends(),
                'announcements' => $this->getAnnouncements(),
            ];
        });
    }

    /**
     * Get enrollment statistics
     */
    public function getEnrollmentStatistics(array $filters = []): array
    {
        $query = Enrollment::query();

        if (! empty($filters['school_year'])) {
            $query->where('school_year', $filters['school_year']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $total = $query->count();
        $pending = (clone $query)->where('status', EnrollmentStatus::PENDING)->count();
        $approved = (clone $query)->where('status', EnrollmentStatus::ENROLLED)->count();
        $rejected = (clone $query)->where('status', EnrollmentStatus::REJECTED)->count();
        $completed = (clone $query)->where('status', EnrollmentStatus::COMPLETED)->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'completed' => $completed,
            'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities(int $limit = 10): \Illuminate\Support\Collection
    {
        $enrollments = Enrollment::with(['student', 'guardian'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($enrollment) {
                return [
                    'type' => 'enrollment',
                    'message' => "New enrollment application from {$enrollment->student->full_name}",
                    'status' => $enrollment->status,
                    'created_at' => $enrollment->created_at,
                    'link' => route('registrar.enrollments.show', $enrollment->id),
                ];
            });

        $students = Student::latest()
            ->limit($limit)
            ->get()
            ->map(function ($student) {
                return [
                    'type' => 'student',
                    'message' => "New student registered: {$student->full_name}",
                    'created_at' => $student->created_at,
                    'link' => route('registrar.students.show', $student->id),
                ];
            });

        return $enrollments->concat($students)
            ->sortByDesc('created_at')
            ->take($limit);
    }

    /**
     * Get pending tasks
     */
    public function getPendingTasks(string $role): \Illuminate\Support\Collection
    {
        $tasks = collect();

        if ($role === 'registrar' || $role === 'administrator') {
            // Pending enrollments to review
            $pendingEnrollments = Enrollment::where('status', EnrollmentStatus::PENDING)
                ->count();

            if ($pendingEnrollments > 0) {
                $tasks->push([
                    'title' => 'Review Pending Enrollments',
                    'description' => "{$pendingEnrollments} enrollment applications awaiting review",
                    'priority' => 'high',
                    'link' => route('registrar.enrollments.index', ['status' => 'pending']),
                ]);
            }

            // Incomplete payments
            $incompletePayments = Enrollment::where('payment_status', PaymentStatus::PARTIAL)
                ->count();

            if ($incompletePayments > 0) {
                $tasks->push([
                    'title' => 'Follow-up on Partial Payments',
                    'description' => "{$incompletePayments} enrollments with partial payments",
                    'priority' => 'medium',
                    'link' => route('registrar.enrollments.index', ['payment_status' => 'partial']),
                ]);
            }
        }

        if ($role === 'guardian') {
            // Incomplete applications
            $incompleteApps = Enrollment::where('guardian_id', auth()->id())
                ->where('status', EnrollmentStatus::PENDING)
                ->count();

            if ($incompleteApps > 0) {
                $tasks->push([
                    'title' => 'Complete Enrollment Applications',
                    'description' => "{$incompleteApps} pending enrollment applications",
                    'priority' => 'high',
                    'link' => route('guardian.enrollments.index'),
                ]);
            }

            // Unpaid balances
            $unpaidBalance = Enrollment::where('guardian_id', auth()->id())
                ->where('payment_status', '!=', PaymentStatus::PAID)
                ->sum('balance_cents');

            if ($unpaidBalance > 0) {
                $tasks->push([
                    'title' => 'Outstanding Balance',
                    'description' => 'Total balance: ₱'.number_format($unpaidBalance / 100, 2),
                    'priority' => 'high',
                    'link' => route('guardian.billing.index'),
                ]);
            }
        }

        return $tasks;
    }

    /**
     * Get system announcements
     */
    public function getAnnouncements(bool $activeOnly = true): \Illuminate\Support\Collection
    {
        // This would fetch from an announcements table
        // For now, returning sample announcements
        return collect([
            [
                'id' => 1,
                'title' => 'Enrollment Period Open',
                'content' => 'Enrollment for School Year 2025-2026 is now open.',
                'type' => 'info',
                'created_at' => now()->subDays(2),
            ],
            [
                'id' => 2,
                'title' => 'Early Bird Discount',
                'content' => 'Get 5% discount for enrollments completed before May 31.',
                'type' => 'success',
                'created_at' => now()->subDays(5),
            ],
        ]);
    }

    /**
     * Get quick stats
     */
    public function getQuickStats(): array
    {
        $this->logActivity('getQuickStats', []);

        $currentYear = date('Y').'-'.(date('Y') + 1);

        return [
            'total_students' => Student::count(),
            'active_enrollments' => Enrollment::where('school_year', $currentYear)
                ->where('status', EnrollmentStatus::ENROLLED)
                ->count(),
            'pending_enrollments' => Enrollment::where('status', EnrollmentStatus::PENDING)->count(),
            'total_revenue' => Enrollment::where('school_year', $currentYear)
                ->sum('amount_paid_cents') / 100,
            'recent_enrollments' => $this->getRecentActivities(5)->where('type', 'enrollment'),
            'enrollment_trend' => $this->getEnrollmentTrend(6),
            'revenue_chart' => $this->getRevenueChart(6),
            'grade_distribution' => $this->getGradeDistribution(),
            'collection_rate' => $this->calculateCollectionRate(),
        ];
    }

    /**
     * Get enrollment trends
     */
    public function getEnrollmentTrends(string $period = 'monthly'): array
    {
        $data = [];

        if ($period === 'monthly') {
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $count = Enrollment::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();

                $data[] = [
                    'label' => $date->format('M Y'),
                    'value' => $count,
                ];
            }
        } elseif ($period === 'daily') {
            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $count = Enrollment::whereDate('created_at', $date->format('Y-m-d'))
                    ->count();

                $data[] = [
                    'label' => $date->format('M d'),
                    'value' => $count,
                ];
            }
        }

        return $data;
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStatistics(): array
    {
        $currentYear = date('Y').'-'.(date('Y') + 1);

        $totalExpected = Enrollment::where('school_year', $currentYear)
            ->sum('net_amount_cents');

        $totalCollected = Enrollment::where('school_year', $currentYear)
            ->sum('amount_paid_cents');

        $totalBalance = Enrollment::where('school_year', $currentYear)
            ->sum('balance_cents');

        return [
            'total_expected' => $totalExpected / 100,
            'total_collected' => $totalCollected / 100,
            'total_balance' => $totalBalance / 100,
            'collection_rate' => $totalExpected > 0
                ? round(($totalCollected / $totalExpected) * 100, 2)
                : 0,
            'by_status' => [
                'paid' => Enrollment::where('school_year', $currentYear)
                    ->where('payment_status', PaymentStatus::PAID)
                    ->count(),
                'partial' => Enrollment::where('school_year', $currentYear)
                    ->where('payment_status', PaymentStatus::PARTIAL)
                    ->count(),
                'pending' => Enrollment::where('school_year', $currentYear)
                    ->where('payment_status', PaymentStatus::PENDING)
                    ->count(),
            ],
        ];
    }

    /**
     * Get grade level distribution
     */
    public function getGradeLevelDistribution(): array
    {
        $currentYear = date('Y').'-'.(date('Y') + 1);

        $distribution = Enrollment::where('school_year', $currentYear)
            ->where('status', EnrollmentStatus::ENROLLED)
            ->select('grade_level', DB::raw('count(*) as count'))
            ->groupBy('grade_level')
            ->orderBy('grade_level')
            ->get()
            ->pluck('count', 'grade_level')
            ->toArray();

        // Ensure all grade levels are represented
        $gradeLevels = [
            'Kinder', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4',
            'Grade 5', 'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10',
        ];

        $result = [];
        foreach ($gradeLevels as $level) {
            $result[] = [
                'grade_level' => $level,
                'count' => $distribution[$level] ?? 0,
            ];
        }

        return $result;
    }

    /**
     * Get enrollment trend data
     */
    public function getEnrollmentTrend(int $months = 6): array
    {
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = Enrollment::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->where('status', EnrollmentStatus::APPROVED)
                ->count();

            $data[] = [
                'month' => $date->format('M Y'),
                'count' => $count,
            ];
        }

        return $data;
    }

    /**
     * Get revenue chart data
     */
    public function getRevenueChart(int $months = 6): array
    {
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $revenue = \App\Models\Payment::whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('amount');

            $data[] = [
                'month' => $date->format('M Y'),
                'revenue' => $revenue,
            ];
        }

        return $data;
    }

    /**
     * Get grade distribution
     */
    public function getGradeDistribution(): \Illuminate\Support\Collection
    {
        return Student::select('grade_level', DB::raw('count(*) as count'))
            ->groupBy('grade_level')
            ->orderBy('grade_level')
            ->get()
            // @phpstan-ignore-next-line
            ->map(static function (object $item): array {
                return [
                    'grade' => $item->grade_level,
                    'count' => $item->count,
                ];
            });
    }

    /**
     * Calculate collection rate
     */
    protected function calculateCollectionRate(): float
    {
        $currentYear = date('Y').'-'.(date('Y') + 1);

        $totalExpected = Enrollment::where('school_year', $currentYear)
            ->sum('net_amount_cents');

        $totalCollected = Enrollment::where('school_year', $currentYear)
            ->sum('amount_paid_cents');

        return $totalExpected > 0 ? round(($totalCollected / $totalExpected) * 100, 2) : 0;
    }

    /**
     * Get admin dashboard data
     */
    public function getAdminDashboardData(): array
    {
        $this->logActivity('getAdminDashboardData', []);

        $currentYear = date('Y').'-'.(date('Y') + 1);

        return [
            'total_students' => Student::count(),
            'active_enrollments' => Enrollment::where('school_year', $currentYear)
                ->where('status', EnrollmentStatus::ENROLLED)
                ->count(),
            'pending_enrollments' => Enrollment::where('status', EnrollmentStatus::PENDING)->count(),
            'total_revenue' => Enrollment::where('school_year', $currentYear)
                ->sum('amount_paid_cents') / 100,
            'recent_enrollments' => $this->getRecentActivities(5)->where('type', 'enrollment'),
            'enrollment_trend' => $this->getEnrollmentTrend(6),
            'revenue_chart' => $this->getRevenueChart(6),
            'grade_distribution' => $this->getGradeDistribution(),
            'collection_rate' => $this->calculateCollectionRate(),
        ];
    }

    /**
     * Get parent dashboard data
     */
    public function getParentDashboardData(int $guardianId): array
    {
        $students = Student::where('guardian_id', $guardianId)->get();
        $enrollments = Enrollment::where('guardian_id', $guardianId)
            ->with('student')
            ->get();

        $pendingPayments = $enrollments->where('payment_status', '!=', PaymentStatus::PAID)
            ->sum('balance_cents') / 100;

        $recentActivities = $this->getRecentActivities(5);
        $upcomingDeadlines = $this->getUpcomingDeadlines(30);

        $this->logActivity('getParentDashboardData', ['guardian_id' => $guardianId]);

        return [
            'students' => $students,
            'enrollments' => $enrollments,
            'pending_payments' => $pendingPayments,
            'recent_activities' => $recentActivities,
            'upcoming_deadlines' => $upcomingDeadlines,
        ];
    }

    /**
     * Get student dashboard data
     */
    public function getStudentDashboardData(int $studentId): array
    {
        $student = Student::findOrFail($studentId);
        $enrollment = Enrollment::where('student_id', $studentId)
            ->latest()
            ->first();

        $this->logActivity('getStudentDashboardData', ['student_id' => $studentId]);

        return [
            'profile' => $student,
            'enrollment_status' => $enrollment ? $enrollment->status->label() : 'Not Enrolled',
            'current_grade' => $student->grade_level,
            'school_year' => $enrollment ? $enrollment->school_year : null,
            'announcements' => $this->getAnnouncements(),
        ];
    }

    /**
     * Get upcoming deadlines
     */
    public function getUpcomingDeadlines(int $days = 30): array
    {
        $invoices = \App\Models\Invoice::where('due_date', '>', now())
            ->where('due_date', '<=', now()->addDays($days))
            ->where('status', '!=', \App\Enums\InvoiceStatus::PAID)
            ->get();

        return $invoices->map(function ($invoice) {
            return [
                'type' => 'payment',
                'title' => 'Payment Due',
                'description' => 'Invoice #'.$invoice->invoice_number,
                'date' => $invoice->due_date,
                'amount' => $invoice->total_amount - $invoice->paid_amount,
            ];
        })->toArray();
    }

    /**
     * Get payment method distribution
     */
    public function getPaymentMethodDistribution(): \Illuminate\Support\Collection
    {
        return \App\Models\Payment::select('payment_method', \Illuminate\Support\Facades\DB::raw('count(*) as count'), \Illuminate\Support\Facades\DB::raw('sum(amount) as total'))
            ->groupBy('payment_method')
            ->get()
            // @phpstan-ignore-next-line
            ->map(static function (object $item): array {
                return [
                    'method' => $item->payment_method->label(),
                    'count' => $item->count,
                    'total' => $item->total,
                ];
            });
    }

    /**
     * Get enrollment status distribution
     */
    public function getEnrollmentStatusDistribution(): \Illuminate\Support\Collection
    {
        return Enrollment::select('status', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            // @phpstan-ignore-next-line
            ->map(static function (object $item): array {
                return [
                    'status' => $item->status->label(),
                    'count' => $item->count,
                ];
            });
    }

    /**
     * Get cache key for data caching
     */
    protected function getCacheKey(string $role, string $type): string
    {
        return "dashboard.{$role}.{$type}";
    }

    /**
     * Log activity (simple implementation)
     */
    protected function logActivity(string $action, array $data = []): void
    {
        \Illuminate\Support\Facades\Log::info("Service action: {$action}", $data);
    }
}

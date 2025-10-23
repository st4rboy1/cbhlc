<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Services\DashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService) {}

    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $currentYear = date('Y').'-'.(date('Y') + 1);
        $paymentStats = $this->dashboardService->getPaymentStatistics();
        $enrollmentStats = $this->dashboardService->getEnrollmentStatistics();

        // Guardian Journey Metrics
        $totalGuardians = Guardian::count();
        $guardiansWithStudents = Guardian::has('children')->count();
        $guardiansWithoutStudents = $totalGuardians - $guardiansWithStudents;

        // Get guardians with students but no enrollments
        $guardiansWithStudentsNoEnrollments = Guardian::has('children')
            ->whereDoesntHave('children.enrollments')
            ->count();

        // User verification metrics
        $unverifiedUsers = User::whereNull('email_verified_at')->count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();

        // Student enrollment journey
        $studentsWithEnrollments = Student::has('enrollments')->count();
        $studentsWithoutEnrollments = Student::count() - $studentsWithEnrollments;

        // Payment journey metrics
        $enrollmentsNeedingPayment = Enrollment::where('status', EnrollmentStatus::ENROLLED)
            ->where('payment_status', '!=', \App\Enums\PaymentStatus::PAID)
            ->count();

        // Financial projections
        $totalExpected = Enrollment::where('school_year', $currentYear)
            ->sum('net_amount_cents') / 100;
        $potentialRevenue = Enrollment::where('school_year', $currentYear)
            ->sum('balance_cents') / 100;

        $stats = [
            // Core metrics
            'totalStudents' => Student::count(),
            'activeEnrollments' => Enrollment::where('school_year', $currentYear)
                ->where('status', EnrollmentStatus::ENROLLED)
                ->count(),
            'newEnrollments' => Enrollment::where('created_at', '>=', now()->startOfMonth())->count(),
            'pendingApplications' => Enrollment::where('status', EnrollmentStatus::PENDING)->count(),
            'totalStaff' => User::role(['super_admin', 'administrator', 'registrar'])->count(),

            // User Journey Metrics
            'totalUsers' => User::count(),
            'verifiedUsers' => $verifiedUsers,
            'unverifiedUsers' => $unverifiedUsers,

            // Guardian Journey Metrics
            'totalGuardians' => $totalGuardians,
            'guardiansWithStudents' => $guardiansWithStudents,
            'guardiansWithoutStudents' => $guardiansWithoutStudents,
            'guardiansWithStudentsNoEnrollments' => $guardiansWithStudentsNoEnrollments,

            // Student Journey Metrics
            'studentsWithEnrollments' => $studentsWithEnrollments,
            'studentsWithoutEnrollments' => $studentsWithoutEnrollments,

            // Enrollment metrics
            'approvedEnrollments' => $enrollmentStats['approved'],
            'completedEnrollments' => $enrollmentStats['completed'],
            'rejectedEnrollments' => $enrollmentStats['rejected'],
            'enrollmentsNeedingPayment' => $enrollmentsNeedingPayment,

            // Payment metrics
            'totalInvoices' => Invoice::count(),
            'paidInvoices' => $paymentStats['by_status']['paid'],
            'partialPayments' => $paymentStats['by_status']['partial'],
            'pendingPayments' => $paymentStats['by_status']['pending'],
            'totalCollected' => $paymentStats['total_collected'],
            'totalBalance' => $paymentStats['total_balance'],
            'collectionRate' => $paymentStats['collection_rate'],

            // Financial Projections
            'totalExpectedRevenue' => $totalExpected,
            'potentialIncomingRevenue' => $potentialRevenue,

            // Transaction metrics
            'totalPayments' => Payment::count(),
            'recentPaymentsCount' => Payment::where('created_at', '>=', now()->subDays(7))->count(),
            'totalRevenue' => Enrollment::where('school_year', $currentYear)
                ->sum('amount_paid_cents') / 100,
        ];

        $recentActivities = Enrollment::with('student')
            ->latest()
            ->take(5)
            ->get()
            ->map(function (Enrollment $enrollment) {
                if ($enrollment->student) {
                    return [
                        'id' => $enrollment->id,
                        'message' => 'New enrollment application from '.$enrollment->student->full_name,
                        'time' => $enrollment->created_at->diffForHumans(),
                    ];
                }

                return null;
            })
            ->filter();

        return Inertia::render('admin/dashboard', [
            'stats' => $stats,
            'recentActivities' => $recentActivities,
        ]);
    }
}

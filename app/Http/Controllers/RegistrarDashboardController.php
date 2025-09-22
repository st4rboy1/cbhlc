<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Enrollment;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegistrarDashboardController extends Controller
{
    /**
     * Display the registrar dashboard.
     */
    public function index()
    {
        // Get enrollment statistics
        $enrollmentStats = [
            'pending' => Enrollment::where('status', EnrollmentStatus::PENDING)->count(),
            'approved' => Enrollment::where('status', EnrollmentStatus::ENROLLED)->count(),
            'rejected' => Enrollment::where('status', EnrollmentStatus::REJECTED)->count(),
            'total' => Enrollment::count(),
        ];

        // Get recent applications (last 10)
        $recentApplications = Enrollment::with(['student', 'guardian'])
            ->latest('created_at')
            ->take(10)
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'student_name' => $enrollment->student->first_name.' '.
                                     ($enrollment->student->middle_name ? $enrollment->student->middle_name.' ' : '').
                                     $enrollment->student->last_name,
                    'grade_level' => $enrollment->grade_level,
                    'status' => $enrollment->status->value,
                    'submission_date' => $enrollment->created_at->format('Y-m-d'),
                    'payment_status' => $enrollment->payment_status->value,
                ];
            });

        // Get student statistics
        $studentStats = [
            'total_students' => Student::count(),
            'new_students' => Student::whereDoesntHave('enrollments', function ($query) {
                $query->where('status', EnrollmentStatus::COMPLETED);
            })->count(),
            'enrolled_students' => Student::whereHas('enrollments', function ($query) {
                $query->where('status', EnrollmentStatus::ENROLLED);
            })->count(),
        ];

        // Get payment statistics for enrolled students
        $paymentStats = [
            'pending' => Enrollment::where('payment_status', PaymentStatus::PENDING)->count(),
            'partial' => Enrollment::where('payment_status', PaymentStatus::PARTIAL)->count(),
            'paid' => Enrollment::where('payment_status', PaymentStatus::PAID)->count(),
            'overdue' => Enrollment::where('payment_status', PaymentStatus::OVERDUE)->count(),
        ];

        // Get upcoming deadlines
        $currentYear = date('Y');
        $upcomingDeadlines = [
            [
                'title' => 'Early Registration Deadline',
                'date' => Carbon::parse("$currentYear-05-31")->format('Y-m-d'),
                'daysLeft' => Carbon::now()->diffInDays(Carbon::parse("$currentYear-05-31"), false),
            ],
            [
                'title' => 'Regular Registration Deadline',
                'date' => Carbon::parse("$currentYear-06-30")->format('Y-m-d'),
                'daysLeft' => Carbon::now()->diffInDays(Carbon::parse("$currentYear-06-30"), false),
            ],
            [
                'title' => 'Late Registration Deadline',
                'date' => Carbon::parse("$currentYear-07-15")->format('Y-m-d'),
                'daysLeft' => Carbon::now()->diffInDays(Carbon::parse("$currentYear-07-15"), false),
            ],
        ];

        // Get grade level distribution
        $gradeLevelDistribution = Enrollment::where('status', EnrollmentStatus::ENROLLED)
            ->select('grade_level')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('grade_level')
            ->orderBy('grade_level')
            ->get()
            ->map(function ($item) {
                return [
                    'grade' => $item->grade_level,
                    'count' => (int) $item->count,
                ];
            })->toArray();

        return Inertia::render('registrar/dashboard', [
            'enrollmentStats' => $enrollmentStats,
            'recentApplications' => $recentApplications,
            'studentStats' => $studentStats,
            'paymentStats' => $paymentStats,
            'upcomingDeadlines' => $upcomingDeadlines,
            'gradeLevelDistribution' => $gradeLevelDistribution,
        ]);
    }

    /**
     * Quick approve enrollment application.
     */
    public function quickApprove(Request $request, $enrollmentId)
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);

        if ($enrollment->status !== EnrollmentStatus::PENDING) {
            return back()->with('error', 'Only pending applications can be approved.');
        }

        $enrollment->update([
            'status' => EnrollmentStatus::ENROLLED,
            'approval_date' => now(),
            'reviewer_id' => auth()->id(),
        ]);

        return back()->with('success', 'Enrollment application approved successfully.');
    }

    /**
     * Quick reject enrollment application.
     */
    public function quickReject(Request $request, $enrollmentId)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $enrollment = Enrollment::findOrFail($enrollmentId);

        if ($enrollment->status !== EnrollmentStatus::PENDING) {
            return back()->with('error', 'Only pending applications can be rejected.');
        }

        $enrollment->update([
            'status' => EnrollmentStatus::REJECTED,
            'review_date' => now(),
            'reviewer_id' => auth()->id(),
            'notes' => $validated['reason'],
        ]);

        return back()->with('success', 'Enrollment application rejected.');
    }
}

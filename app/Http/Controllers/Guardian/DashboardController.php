<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\GuardianStudent;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the guardian dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Get all students for this guardian
        $studentIds = GuardianStudent::where('guardian_id', $user->id)
            ->pluck('student_id');

        $students = Student::whereIn('id', $studentIds)->get();

        // Get enrollments for guardian's children
        $enrollments = Enrollment::with(['student'])
            ->whereIn('student_id', $studentIds)
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'student_name' => $enrollment->student->first_name.' '.
                                     ($enrollment->student->middle_name ? $enrollment->student->middle_name.' ' : '').
                                     $enrollment->student->last_name,
                    'grade_level' => $enrollment->grade_level,
                    'school_year' => $enrollment->school_year,
                    'status' => $enrollment->status->value,
                    'payment_status' => $enrollment->payment_status->value,
                    'created_at' => $enrollment->created_at->format('Y-m-d'),
                ];
            });

        // Get enrollment statistics
        $enrollmentStats = [
            'total' => Enrollment::whereIn('student_id', $studentIds)->count(),
            'pending' => Enrollment::whereIn('student_id', $studentIds)
                ->where('status', EnrollmentStatus::PENDING)->count(),
            'enrolled' => Enrollment::whereIn('student_id', $studentIds)
                ->where('status', EnrollmentStatus::ENROLLED)->count(),
            'rejected' => Enrollment::whereIn('student_id', $studentIds)
                ->where('status', EnrollmentStatus::REJECTED)->count(),
        ];

        // School announcements (placeholder)
        $announcements = [
            [
                'id' => 1,
                'title' => 'Welcome to CBHLC Online Enrollment',
                'content' => 'Our new online enrollment system is now active. Please submit your applications early.',
                'date' => now()->format('Y-m-d'),
                'priority' => 'info',
            ],
        ];

        return Inertia::render('guardian/dashboard', [
            'students' => $students,
            'enrollments' => $enrollments,
            'enrollmentStats' => $enrollmentStats,
            'announcements' => $announcements,
        ]);
    }
}

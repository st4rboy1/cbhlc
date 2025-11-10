<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Guardian;
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
        // Get Guardian model for authenticated user
        $guardian = Guardian::where('user_id', Auth::id())->firstOrFail();

        // Get all students for this guardian
        $studentIds = GuardianStudent::where('guardian_id', $guardian->id)
            ->pluck('student_id');

        $students = Student::whereIn('id', $studentIds)->get();

        // Get enrollments for guardian's children
        $enrollments = Enrollment::with(['student', 'schoolYear'])
            ->whereIn('student_id', $studentIds)
            ->latest('created_at')
            ->take(5)
            ->get()
            /** @phpstan-ignore-next-line */
            ->map(function (Enrollment $enrollment): array {
                return [
                    'id' => $enrollment->id,
                    'student_name' => $enrollment->student->first_name.' '.
                                     ($enrollment->student->middle_name ? $enrollment->student->middle_name.' ' : '').
                                     $enrollment->student->last_name,
                    'grade_level' => $enrollment->grade_level,
                    'school_year_name' => $enrollment->schoolYear->name,
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

        // Format students as "children" for the frontend
        /** @phpstan-ignore-next-line */
        $children = $students->map(function (Student $student) {
            $latestEnrollment = Enrollment::where('student_id', $student->id)
                ->latest('created_at')
                ->first();

            return [
                'id' => $student->id,
                'name' => trim($student->first_name.' '.
                    ($student->middle_name ? $student->middle_name.' ' : '').
                    $student->last_name),
                'grade' => $student->grade_level ? ($student->grade_level->label ?? $student->grade_level->value) : 'N/A',
                'enrollmentStatus' => $latestEnrollment ? $latestEnrollment->status->value : 'No Enrollment',
                'enrollmentId' => $latestEnrollment ? $latestEnrollment->id : null,
                'photo' => null, // Placeholder for future profile photos
            ];
        });

        // Format announcements for frontend
        $formattedAnnouncements = collect($announcements)->map(function ($announcement) {
            return [
                'id' => $announcement['id'],
                'title' => $announcement['title'],
                'message' => $announcement['content'],
                'date' => $announcement['date'],
                'type' => $announcement['priority'],
            ];
        });

        // Upcoming events (placeholder)
        $upcomingEvents = [
            ['date' => 'Dec 20, 2024', 'event' => 'Christmas Break Starts'],
            ['date' => 'Jan 15, 2025', 'event' => 'Classes Resume'],
            ['date' => 'Feb 14, 2025', 'event' => 'Parent-Teacher Conference'],
        ];

        return Inertia::render('guardian/dashboard', [
            'children' => $children,
            'announcements' => $formattedAnnouncements,
            'upcomingEvents' => $upcomingEvents,
            'students' => $students, // Keep for backward compatibility
            'enrollments' => $enrollments,
            'enrollmentStats' => $enrollmentStats,
        ]);
    }
}

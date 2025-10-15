<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $stats = [
            'totalStudents' => Student::count(),
            'newEnrollments' => Enrollment::where('created_at', '>=', now()->startOfMonth())->count(),
            'pendingApplications' => Enrollment::where('status', EnrollmentStatus::PENDING)->count(),
            'totalStaff' => User::role(['super_admin', 'administrator', 'registrar'])->count(),
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

<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Guardian;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuardianDashboardController extends Controller
{
    /**
     * Display the guardian dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $guardian = Guardian::where('user_id', $user->id)->first();

        // Get guardian's children with latest enrollment info
        $children = [];
        if ($guardian) {
            $children = $guardian->children()
                ->with(['enrollments' => function ($query) {
                    $query->latest()->limit(1);
                }])
                ->get()
                ->map(function ($student) {
                    $latestEnrollment = $student->enrollments->first();

                    return [
                        'id' => $student->id,
                        'name' => trim($student->first_name.' '.$student->last_name),
                        'grade' => $student->grade_level?->value ?? 'Not Enrolled',
                        'enrollmentStatus' => $latestEnrollment
                            ? ucfirst($latestEnrollment->enrollment_status->value)
                            : 'Not Enrolled',
                        'photo' => null,
                    ];
                });
        }

        // Mock announcements data (can be replaced with real data later)
        $announcements = [
            [
                'id' => 1,
                'title' => 'Parent-Teacher Conference',
                'message' => 'Scheduled for October 15, 2025. Please confirm your attendance.',
                'date' => '2025-09-15',
                'type' => 'event',
            ],
            [
                'id' => 2,
                'title' => 'School Holiday',
                'message' => 'No classes on September 21, 2025 - National Holiday',
                'date' => '2025-09-14',
                'type' => 'holiday',
            ],
            [
                'id' => 3,
                'title' => 'Tuition Payment Reminder',
                'message' => 'Monthly tuition fee due on September 30, 2025',
                'date' => '2025-09-13',
                'type' => 'payment',
            ],
        ];

        // Mock upcoming events (can be replaced with real data later)
        $upcomingEvents = [
            ['date' => '2025-10-01', 'event' => 'Foundation Day Celebration'],
            ['date' => '2025-10-15', 'event' => 'Parent-Teacher Conference'],
            ['date' => '2025-10-30', 'event' => 'Halloween Program'],
            ['date' => '2025-11-01', 'event' => 'All Saints Day - No Classes'],
        ];

        return Inertia::render('guardian/dashboard', [
            'children' => $children,
            'announcements' => $announcements,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}
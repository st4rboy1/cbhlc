<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\GradeLevelFee;
use App\Models\Guardian;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TuitionController extends Controller
{
    /**
     * Display the tuition fees page
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $enrollments = collect();

        // Get enrollments based on user role
        if ($user->hasRole(['super_admin', 'administrator', 'registrar'])) {
            // Admin users can see all enrollments
            $enrollments = Enrollment::with(['student', 'guardian'])
                ->latest()
                ->paginate(10);
        } elseif ($user->hasRole('guardian')) {
            // Guardians can only see their children's enrollments
            $guardian = Guardian::where('user_id', $user->id)->first();
            if ($guardian) {
                $studentIds = $guardian->children()->pluck('id');
                $enrollments = Enrollment::with(['student', 'guardian'])
                    ->whereIn('student_id', $studentIds)
                    ->latest()
                    ->paginate(10);
            }
        }

        // Get configurable grade level fees for current school year
        $gradeLevelFees = GradeLevelFee::currentSchoolYear()
            ->active()
            ->get()
            ->mapWithKeys(
                /** @phpstan-ignore-next-line */
                function (GradeLevelFee $fee): array {
                    return [
                        $fee->grade_level->value => [
                            'tuition' => $fee->tuition_fee,
                            'miscellaneous' => $fee->miscellaneous_fee,
                            'laboratory' => $fee->laboratory_fee,
                            'library' => $fee->library_fee,
                            'sports' => $fee->sports_fee,
                            'total' => $fee->total_fee,
                        ],
                    ];
                }
            );

        $settings = Setting::pluck('value', 'key');

        return Inertia::render('shared/tuition', [
            'enrollments' => $enrollments,
            'gradeLevelFees' => $gradeLevelFees,
            'settings' => $settings,
        ]);
    }
}

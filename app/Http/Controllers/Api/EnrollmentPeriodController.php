<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentPeriod;

class EnrollmentPeriodController extends Controller
{
    /**
     * Get the currently active enrollment period.
     *
     * This public API endpoint allows anyone to check if there is
     * an active enrollment period and get its details.
     */
    public function active()
    {
        $activePeriod = EnrollmentPeriod::with('schoolYear')
            ->active()
            ->first();

        if (! $activePeriod) {
            return response()->json([
                'data' => null,
                'message' => 'No active enrollment period at this time.',
            ], 200);
        }

        return response()->json([
            'data' => [
                'id' => $activePeriod->id,
                'school_year' => $activePeriod->schoolYear
                    ? "{$activePeriod->schoolYear->start_year}-{$activePeriod->schoolYear->end_year}"
                    : null,
                'start_date' => $activePeriod->start_date->toDateString(),
                'end_date' => $activePeriod->end_date->toDateString(),
                'early_registration_deadline' => $activePeriod->early_registration_deadline?->toDateString(),
                'regular_registration_deadline' => $activePeriod->regular_registration_deadline->toDateString(),
                'late_registration_deadline' => $activePeriod->late_registration_deadline?->toDateString(),
                'is_open' => $activePeriod->isOpen(),
                'days_remaining' => $activePeriod->getDaysRemaining(),
                'allow_new_students' => $activePeriod->allow_new_students ?? true,
                'allow_returning_students' => $activePeriod->allow_returning_students ?? true,
            ],
            'message' => 'Active enrollment period retrieved successfully.',
        ]);
    }
}

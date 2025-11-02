<?php

namespace App\Http\Controllers;

use App\Enums\PaymentPlan;
use App\Models\GradeLevelFee;
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

        $paymentPlans = collect(PaymentPlan::cases())->map(fn ($plan) => [
            'value' => $plan->value,
            'label' => $plan->label(),
            'installments' => $plan->installments(),
            'description' => $plan->description(),
        ]);

        return Inertia::render('shared/tuition', [
            'gradeLevelFees' => $gradeLevelFees,
            'settings' => $settings,
            'paymentPlans' => $paymentPlans,
        ]);
    }
}

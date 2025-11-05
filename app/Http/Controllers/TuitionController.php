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
        $gradeLevelFees = GradeLevelFee::currentSchoolYear()
            ->active()
            ->get();

        $feesByGradeAndPlan = [];

        foreach ($gradeLevelFees as $fee) {
            $feesByGradeAndPlan[$fee->grade_level->value] = [
                'tuition' => $fee->tuition_fee,
                'miscellaneous' => $fee->miscellaneous_fee,
                'laboratory' => $fee->laboratory_fee,
                'library' => $fee->library_fee,
                'sports' => $fee->sports_fee,
                'total' => $fee->total_fee,
                'payment_plans' => [],
            ];

            foreach (PaymentPlan::cases() as $plan) {
                $installments = $plan->installments();
                $amountPerInstallment = $installments > 0 ? $fee->total_fee / $installments : $fee->total_fee;

                $feesByGradeAndPlan[$fee->grade_level->value]['payment_plans'][$plan->value] = [
                    'label' => $plan->label(),
                    'installments' => $installments,
                    'amount_per_installment' => $amountPerInstallment,
                ];
            }
        }

        $settings = Setting::pluck('value', 'key');

        $paymentPlans = collect(PaymentPlan::cases())->map(fn ($plan) => [
            'value' => $plan->value,
            'label' => $plan->label(),
            'installments' => $plan->installments(),
            'description' => $plan->description(),
        ]);

        return Inertia::render('shared/tuition', [
            'gradeLevelFees' => $feesByGradeAndPlan,
            'settings' => $settings,
            'paymentPlans' => $paymentPlans,
        ]);
    }
}

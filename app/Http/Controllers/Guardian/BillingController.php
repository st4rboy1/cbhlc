<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentPlan;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\GradeLevelFee;
use App\Models\Guardian;
use App\Models\GuardianStudent;
use App\Services\CurrencyService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BillingController extends Controller
{
    private CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Display billing information for guardian's children.
     */
    public function index()
    {
        // Get Guardian model for authenticated user
        $guardian = Guardian::where('user_id', Auth::id())->firstOrFail();

        // Get student IDs for this guardian
        $studentIds = GuardianStudent::where('guardian_id', $guardian->id)
            ->pluck('student_id');

        // Get enrollments with billing information
        /** @var \Illuminate\Support\Collection<int, array<string, mixed>> $enrollments */
        $enrollments = Enrollment::with(['student', 'schoolYear', 'enrollmentPeriod'])
            ->whereIn('student_id', $studentIds)
            ->where('status', '!=', EnrollmentStatus::REJECTED)
            ->get()
            /** @phpstan-ignore-next-line */
            ->map(function (Enrollment $enrollment) {
                // Use enrollment's stored fee amounts if available, otherwise lookup from grade level fees
                if ($enrollment->total_amount_cents > 0) {
                    $tuitionFee = $enrollment->tuition_fee_cents / 100;
                    $miscFee = $enrollment->miscellaneous_fee_cents / 100;
                    $totalFee = $enrollment->total_amount_cents / 100;
                } else {
                    // Fallback: Find the fee for the enrollment's grade level
                    $fee = GradeLevelFee::where('grade_level', $enrollment->grade_level)
                        ->where('enrollment_period_id', $enrollment->enrollment_period_id)
                        ->where('is_active', true)
                        ->first();

                    $tuitionFee = $fee ? $fee->tuition_fee : 0;
                    $miscFee = $fee ? $fee->miscellaneous_fee : 0;
                    $totalFee = $tuitionFee + $miscFee;
                }

                return [
                    'id' => $enrollment->id,
                    'student_name' => $enrollment->student->first_name.' '.
                                     ($enrollment->student->middle_name ? $enrollment->student->middle_name.' ' : '').
                                     $enrollment->student->last_name,
                    'student_id' => $enrollment->student->student_id,
                    'school_year_name' => $enrollment->schoolYear->name,
                    'grade_level' => $enrollment->grade_level,
                    'status' => $enrollment->status->value,
                    'payment_status' => $enrollment->payment_status->value,
                    'tuition_fee' => $this->currencyService->format($tuitionFee),
                    'miscellaneous_fee' => $this->currencyService->format($miscFee),
                    'total_amount' => $this->currencyService->format($totalFee),
                    'raw_total' => $totalFee, // For calculations
                ];
            });

        // Calculate totals
        $totalDue = $enrollments->where('payment_status', '!=', 'paid')->sum('raw_total');
        $totalPaid = $enrollments->where('payment_status', 'paid')->sum('raw_total');

        // Get payment plans
        $paymentPlans = [
            [
                'name' => 'Annual',
                'description' => 'Pay in full at the beginning of the school year',
                'discount' => '5%',
            ],
            [
                'name' => 'Semestral',
                'description' => 'Pay in two installments per semester',
                'discount' => '2%',
            ],
            [
                'name' => 'Monthly',
                'description' => 'Pay monthly installments',
                'discount' => '0%',
            ],
        ];

        return Inertia::render('guardian/billing/index', [
            'enrollments' => $enrollments,
            'summary' => [
                'total_due' => $this->currencyService->format($totalDue),
                'total_paid' => $this->currencyService->format($totalPaid),
                'pending_count' => $enrollments->where('payment_status', 'pending')->count(),
                'overdue_count' => $enrollments->where('payment_status', 'overdue')->count(),
            ],
            'paymentPlans' => $paymentPlans,
        ]);
    }

    /**
     * Display billing details for a specific enrollment.
     */
    public function show(Enrollment $enrollment)
    {
        // Get Guardian model for authenticated user
        $guardian = Guardian::where('user_id', Auth::id())->firstOrFail();

        // Verify this guardian has access to this enrollment
        $hasAccess = GuardianStudent::where('guardian_id', $guardian->id)
            ->where('student_id', $enrollment->student_id)
            ->exists();

        if (! $hasAccess) {
            abort(403, 'You do not have access to view this billing information.');
        }

        $enrollment->load(['student', 'schoolYear']);

        // Use enrollment's stored fee amounts if available, otherwise lookup from grade level fees
        if ($enrollment->total_amount_cents > 0) {
            $tuitionFee = $enrollment->tuition_fee_cents / 100;
            $miscFee = $enrollment->miscellaneous_fee_cents / 100;
            $totalFee = $enrollment->total_amount_cents / 100;
        } else {
            $fee = GradeLevelFee::where('grade_level', $enrollment->grade_level)
                ->where('enrollment_period_id', $enrollment->enrollment_period_id)
                ->first();

            $tuitionFee = $fee ? $fee->tuition_fee : 0;
            $miscFee = $fee ? $fee->miscellaneous_fee : 0;
            $totalFee = $tuitionFee + $miscFee;
        }

        $paymentPlan = $enrollment->payment_plan;
        $paymentSchedule = [];

        $schoolYearName = $enrollment->schoolYear->name;
        $startYear = substr($schoolYearName, 0, 4);
        $endYear = substr($schoolYearName, 5, 4);

        if ($paymentPlan) {
            switch ($paymentPlan) {
                case PaymentPlan::ANNUAL:
                    $paymentSchedule = [
                        [
                            'period' => 'Annual Payment',
                            'due_date' => 'August 15, '.$startYear,
                            'amount' => $this->currencyService->format($totalFee),
                            'status' => 'pending',
                        ],
                    ];
                    break;
                case PaymentPlan::SEMESTRAL:
                    $semestralAmount = $totalFee / 2;
                    $paymentSchedule = [
                        [
                            'period' => 'First Semester',
                            'due_date' => 'August 15, '.$startYear,
                            'amount' => $this->currencyService->format($semestralAmount),
                            'status' => 'pending',
                        ],
                        [
                            'period' => 'Second Semester',
                            'due_date' => 'January 15, '.$endYear,
                            'amount' => $this->currencyService->format($semestralAmount),
                            'status' => 'pending',
                        ],
                    ];
                    break;
                case PaymentPlan::MONTHLY:
                default:
                    $monthlyAmount = $totalFee / 10;
                    $months = [
                        'August', 'September', 'October', 'November', 'December',
                        'January', 'February', 'March', 'April', 'May',
                    ];
                    $paymentSchedule = [];
                    foreach ($months as $index => $month) {
                        $year = ($index < 5) ? $startYear : $endYear;
                        $paymentSchedule[] = [
                            'period' => $month.' Payment',
                            'due_date' => $month.' 15, '.$year,
                            'amount' => $this->currencyService->format($monthlyAmount),
                            'status' => 'pending',
                        ];
                    }
                    break;
            }
        }

        return Inertia::render('guardian/billing/show', [
            'enrollment' => [
                'id' => $enrollment->id,
                'student_name' => $enrollment->student->first_name.' '.
                                 ($enrollment->student->middle_name ? $enrollment->student->middle_name.' ' : '').
                                 $enrollment->student->last_name,
                'student_id' => $enrollment->student->student_id,
                'school_year_name' => $enrollment->schoolYear->name,
                'grade_level' => $enrollment->grade_level,
                'status' => $enrollment->status->value,
                'payment_status' => $enrollment->payment_status->value,
                'payment_plan' => $enrollment->payment_plan?->label(),
            ],
            'billing' => [
                'tuition_fee' => $this->currencyService->format($tuitionFee),
                'miscellaneous_fee' => $this->currencyService->format($miscFee),
                'total_amount' => $this->currencyService->format($totalFee),
                'payment_schedule' => $paymentSchedule,
            ],
        ]);
    }
}

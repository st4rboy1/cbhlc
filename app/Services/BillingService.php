<?php

namespace App\Services;

use App\Contracts\Services\BillingServiceInterface;
use App\Enums\PaymentStatus;
use App\Models\Enrollment;
use App\Models\GradeLevelFee;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BillingService implements BillingServiceInterface
{
    protected CurrencyService $currencyService;

    /**
     * BillingService constructor.
     */
    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Get billing information for an enrollment
     */
    public function getBillingDetails(Enrollment $enrollment): array
    {
        $enrollment->load(['student', 'guardian']);

        return [
            'enrollment_id' => $enrollment->id,
            'student' => [
                'id' => $enrollment->student->id,
                'name' => $enrollment->student->full_name,
                'student_id' => $enrollment->student->student_id,
                'grade_level' => $enrollment->grade_level,
            ],
            'fees' => [
                'tuition' => $this->currencyService->formatCents($enrollment->tuition_fee_cents),
                'miscellaneous' => $this->currencyService->formatCents($enrollment->miscellaneous_fee_cents),
                'laboratory' => $this->currencyService->formatCents($enrollment->laboratory_fee_cents),
                'total' => $this->currencyService->formatCents($enrollment->total_amount_cents),
                'discount' => $this->currencyService->formatCents(
                    $enrollment->total_amount_cents - $enrollment->net_amount_cents
                ),
                'net_total' => $this->currencyService->formatCents($enrollment->net_amount_cents),
            ],
            'payment' => [
                'paid' => $this->currencyService->formatCents($enrollment->amount_paid_cents),
                'balance' => $this->currencyService->formatCents($enrollment->balance_cents),
                'status' => $enrollment->payment_status,
                'status_label' => $enrollment->payment_status->label(),
                'status_color' => $enrollment->payment_status->color(),
            ],
            'payment_plan' => $enrollment->payment_plan ?? 'full',
            'due_dates' => $this->calculateDueDates($enrollment),
        ];
    }

    /**
     * Get billing summary for guardian's students
     */
    public function getGuardianBillingSummary(int $guardianId): \Illuminate\Support\Collection
    {
        $enrollments = Enrollment::where('guardian_id', $guardianId)
            ->whereIn('status', ['enrolled', 'completed'])
            ->with('student')
            ->get();

        return $enrollments->map(function ($enrollment) {
            return [
                'enrollment' => $enrollment,
                'billing' => $this->getBillingDetails($enrollment),
                'payment_history' => $this->getPaymentHistory($enrollment),
            ];
        });
    }

    /**
     * Calculate payment plan
     */
    public function calculatePaymentPlan(float $totalAmount, string $plan): array
    {
        $plans = [
            'full' => [
                'name' => 'Full Payment',
                'installments' => 1,
                'discount' => 0.05, // 5% discount
                'schedule' => [
                    [
                        'due_date' => now()->addDays(30),
                        'amount' => $totalAmount * 0.95,
                    ],
                ],
            ],
            'semestral' => [
                'name' => 'Semestral Payment',
                'installments' => 2,
                'discount' => 0.03, // 3% discount
                'schedule' => [
                    [
                        'due_date' => now()->addDays(30),
                        'amount' => ($totalAmount * 0.97) / 2,
                    ],
                    [
                        'due_date' => now()->addMonths(6),
                        'amount' => ($totalAmount * 0.97) / 2,
                    ],
                ],
            ],
            'quarterly' => [
                'name' => 'Quarterly Payment',
                'installments' => 4,
                'discount' => 0,
                'schedule' => [
                    [
                        'due_date' => now()->addDays(30),
                        'amount' => $totalAmount / 4,
                    ],
                    [
                        'due_date' => now()->addMonths(3),
                        'amount' => $totalAmount / 4,
                    ],
                    [
                        'due_date' => now()->addMonths(6),
                        'amount' => $totalAmount / 4,
                    ],
                    [
                        'due_date' => now()->addMonths(9),
                        'amount' => $totalAmount / 4,
                    ],
                ],
            ],
            'monthly' => [
                'name' => 'Monthly Payment',
                'installments' => 10,
                'discount' => 0,
                'schedule' => [],
            ],
        ];

        // Generate monthly schedule
        if ($plan === 'monthly') {
            for ($i = 1; $i <= 10; $i++) {
                $plans['monthly']['schedule'][] = [
                    'due_date' => now()->addMonths($i),
                    'amount' => $totalAmount / 10,
                ];
            }
        }

        return $plans[$plan] ?? $plans['full'];
    }

    /**
     * Process payment
     */
    public function processPayment(Enrollment $enrollment, float $amount, array $paymentDetails = []): array
    {
        return DB::transaction(function () use ($enrollment, $amount, $paymentDetails) {
            $amountCents = $this->currencyService->toCents($amount);
            $newPaidAmount = $enrollment->amount_paid_cents + $amountCents;
            $newBalance = $enrollment->net_amount_cents - $newPaidAmount;

            // Determine new payment status
            $newStatus = PaymentStatus::PENDING;
            if ($newBalance <= 0) {
                $newStatus = PaymentStatus::PAID;
            } elseif ($newPaidAmount > 0) {
                $newStatus = PaymentStatus::PARTIAL;
            }

            // Update enrollment
            $enrollment->update([
                'amount_paid_cents' => $newPaidAmount,
                'balance_cents' => max(0, $newBalance),
                'payment_status' => $newStatus,
            ]);

            // Log payment (you might want to create a payments table)
            $paymentRecord = [
                'enrollment_id' => $enrollment->id,
                'amount' => $amount,
                'payment_method' => $paymentDetails['method'] ?? 'cash',
                'reference_number' => $paymentDetails['reference'] ?? null,
                'notes' => $paymentDetails['notes'] ?? null,
                'processed_at' => now(),
                'processed_by' => auth()->id(),
            ];

            return [
                'success' => true,
                'enrollment' => $enrollment->fresh(),
                'payment' => $paymentRecord,
                'message' => 'Payment processed successfully',
            ];
        });
    }

    /**
     * Generate invoice
     */
    public function generateInvoice(Enrollment $enrollment): array
    {
        $billing = $this->getBillingDetails($enrollment);

        return [
            'invoice_number' => 'INV-'.str_pad((string) $enrollment->id, 8, '0', STR_PAD_LEFT),
            'date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'student' => $billing['student'],
            'guardian' => [
                'name' => $enrollment->guardian->name ?? 'N/A',
                'email' => $enrollment->guardian->email ?? 'N/A',
                'phone' => $enrollment->guardian->phone ?? 'N/A',
            ],
            'items' => [
                [
                    'description' => 'Tuition Fee',
                    'amount' => $billing['fees']['tuition'],
                ],
                [
                    'description' => 'Miscellaneous Fee',
                    'amount' => $billing['fees']['miscellaneous'],
                ],
                [
                    'description' => 'Laboratory Fee',
                    'amount' => $billing['fees']['laboratory'],
                ],
            ],
            'subtotal' => $billing['fees']['total'],
            'discount' => $billing['fees']['discount'],
            'total' => $billing['fees']['net_total'],
            'paid' => $billing['payment']['paid'],
            'balance' => $billing['payment']['balance'],
            'status' => $billing['payment']['status'],
        ];
    }

    /**
     * Get payment history
     */
    public function getPaymentHistory(Enrollment $enrollment): \Illuminate\Support\Collection
    {
        // This would fetch from a payments table if available
        // For now, returning a mock collection
        return collect([
            [
                'date' => $enrollment->created_at->format('Y-m-d'),
                'description' => 'Initial enrollment',
                'amount' => 0,
                'balance' => $this->currencyService->formatCents($enrollment->net_amount_cents),
            ],
        ]);
    }

    /**
     * Calculate late fees
     */
    public function calculateLateFees(Enrollment $enrollment): float
    {
        if ($enrollment->payment_status === PaymentStatus::PAID) {
            return 0;
        }

        $dueDate = $enrollment->created_at->addDays(30);
        $daysLate = max(0, now()->diffInDays($dueDate, false) * -1);

        if ($daysLate <= 0) {
            return 0;
        }

        // 2% per month late fee
        $monthsLate = ceil($daysLate / 30);
        $lateFeePercentage = 0.02 * $monthsLate;

        return ($enrollment->balance_cents / 100) * $lateFeePercentage;
    }

    /**
     * Apply discount
     */
    public function applyDiscount(Enrollment $enrollment, string $discountType, float $discountValue): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $discountType, $discountValue) {
            $discountAmount = 0;

            if ($discountType === 'percentage') {
                $discountAmount = ($enrollment->total_amount_cents / 100) * ($discountValue / 100);
            } elseif ($discountType === 'fixed') {
                $discountAmount = $discountValue;
            }

            $discountCents = $this->currencyService->toCents($discountAmount);
            $newNetAmount = $enrollment->total_amount_cents - $discountCents;
            $newBalance = $newNetAmount - $enrollment->amount_paid_cents;

            $enrollment->update([
                'net_amount_cents' => max(0, $newNetAmount),
                'balance_cents' => max(0, $newBalance),
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
            ]);

            return $enrollment->fresh();
        });
    }

    /**
     * Get fee structure by grade level
     */
    public function getFeeStructure(string $gradeLevel): array
    {
        $gradeLevelFee = GradeLevelFee::where('grade_level', $gradeLevel)->first();

        if (! $gradeLevelFee) {
            return [
                'grade_level' => $gradeLevel,
                'tuition_fee' => 0,
                'miscellaneous_fee' => 0,
                'laboratory_fee' => 0,
                'total' => 0,
                'payment_plans' => [],
            ];
        }

        $total = ($gradeLevelFee->tuition_fee +
            $gradeLevelFee->miscellaneous_fee +
            $gradeLevelFee->laboratory_fee) / 100;

        return [
            'grade_level' => $gradeLevel,
            'tuition_fee' => $this->currencyService->formatCents((int) $gradeLevelFee->tuition_fee),
            'miscellaneous_fee' => $this->currencyService->formatCents((int) $gradeLevelFee->miscellaneous_fee),
            'laboratory_fee' => $this->currencyService->formatCents((int) $gradeLevelFee->laboratory_fee),
            'total' => $this->currencyService->format($total),
            'payment_plans' => [
                'full' => $this->calculatePaymentPlan($total, 'full'),
                'semestral' => $this->calculatePaymentPlan($total, 'semestral'),
                'quarterly' => $this->calculatePaymentPlan($total, 'quarterly'),
                'monthly' => $this->calculatePaymentPlan($total, 'monthly'),
            ],
        ];
    }

    /**
     * Calculate due dates for enrollment
     */
    protected function calculateDueDates(Enrollment $enrollment): array
    {
        $plan = $enrollment->payment_plan ?? 'full';
        $totalAmount = $enrollment->net_amount_cents / 100;

        $paymentPlan = $this->calculatePaymentPlan($totalAmount, $plan);

        return $paymentPlan['schedule'] ?? [];
    }
}

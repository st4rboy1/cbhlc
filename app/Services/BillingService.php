<?php

namespace App\Services;

use App\Contracts\Services\BillingServiceInterface;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Enrollment;
use App\Models\GradeLevelFee;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BillingService extends BaseService implements BillingServiceInterface
{
    /**
     * BillingService constructor
     */
    public function __construct(Invoice $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated invoices with filters
     */
    public function getPaginatedInvoices(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['enrollment', 'payments']);

        // Apply status filter
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply enrollment filter
        if (! empty($filters['enrollment_id'])) {
            $query->where('enrollment_id', $filters['enrollment_id']);
        }

        // Apply date range filter
        if (! empty($filters['due_from'])) {
            $query->where('due_date', '>=', $filters['due_from']);
        }
        if (! empty($filters['due_to'])) {
            $query->where('due_date', '<=', $filters['due_to']);
        }

        $this->logActivity('getPaginatedInvoices', ['filters' => $filters]);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Find invoice with payment history
     */
    public function findWithPayments(int $invoiceId): Invoice
    {
        $this->logActivity('findWithPayments', ['invoice_id' => $invoiceId]);

        /** @var Invoice */
        return $this->model->with(['payments', 'enrollment'])->findOrFail($invoiceId);
    }

    /**
     * Generate invoice for enrollment
     */
    public function generateInvoice(Enrollment $enrollment): Invoice
    {
        return DB::transaction(function () use ($enrollment) {
            $enrollment->load('gradeLevelFee');

            $gradeLevelFee = $enrollment->gradeLevelFee ?? GradeLevelFee::where('grade_level', $enrollment->grade_level)->first();

            $totalAmount = 0;
            if ($gradeLevelFee) {
                $totalAmount = $gradeLevelFee->tuition_fee + $gradeLevelFee->registration_fee + $gradeLevelFee->miscellaneous_fee;
            }

            // Create invoice
            /** @var Invoice $invoice */
            $invoice = $this->model->create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'enrollment_id' => $enrollment->id,
                'invoice_date' => now(),
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'status' => InvoiceStatus::DRAFT,
                'due_date' => now()->addDays(30),
            ]);

            // Create invoice items
            if ($gradeLevelFee) {
                $items = [
                    ['description' => 'Tuition Fee', 'amount' => $gradeLevelFee->tuition_fee],
                    ['description' => 'Registration Fee', 'amount' => $gradeLevelFee->registration_fee],
                    ['description' => 'Miscellaneous Fee', 'amount' => $gradeLevelFee->miscellaneous_fee],
                ];

                foreach ($items as $item) {
                    if ($item['amount'] > 0) {
                        $invoice->items()->create([
                            'description' => $item['description'],
                            'quantity' => 1,
                            'unit_price' => $item['amount'],
                            'amount' => $item['amount'],
                        ]);
                    }
                }
            }

            $this->logActivity('generateInvoice', ['enrollment_id' => $enrollment->id, 'invoice_id' => $invoice->id]);

            /** @var Invoice $invoice */
            return $invoice->load('items');
        });
    }

    /**
     * Record payment for invoice
     */
    public function recordPayment(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $amount = $data['amount'];

            // Check for overpayment
            if ($amount > $invoice->remaining_balance) {
                throw new \Exception('Payment amount exceeds remaining balance');
            }

            // Create payment record
            $payment = $invoice->payments()->create($data);

            // Update invoice paid amount
            $invoice->paid_amount += $amount;

            // Update invoice status
            if ($invoice->paid_amount >= $invoice->total_amount) {
                $invoice->status = InvoiceStatus::PAID;
                $invoice->paid_at = now();
            } elseif ($invoice->paid_amount > 0) {
                $invoice->status = InvoiceStatus::PARTIALLY_PAID;
            }

            $invoice->save();

            $this->logActivity('recordPayment', [
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'amount' => $amount,
            ]);

            /** @var Payment $payment */
            return $payment;
        });
    }

    /**
     * Calculate payment plan
     */
    public function calculatePaymentPlan(float $totalAmount, string $plan): array
    {
        $plans = [
            'full' => ['installments' => 1, 'discount' => 0.05],
            'semestral' => ['installments' => 2, 'discount' => 0.03],
            'quarterly' => ['installments' => 4, 'discount' => 0],
            'monthly' => ['installments' => 10, 'discount' => 0],
        ];

        $planDetails = $plans[$plan] ?? $plans['monthly'];
        $discount = $planDetails['discount'];
        $installments = $planDetails['installments'];
        $finalAmount = $totalAmount * (1 - $discount);
        $installmentAmount = $finalAmount / $installments;

        $schedule = [];
        for ($i = 0; $i < $installments; $i++) {
            $schedule[] = [
                'installment' => $i + 1,
                'amount' => $installmentAmount,
                'due_date' => now()->addMonths($i)->format('Y-m-d'),
            ];
        }

        return [
            'plan' => $plan === '' || ! isset($plans[$plan]) ? 'monthly' : $plan,
            'installments' => $installments,
            'discount' => $discount,
            'final_amount' => $finalAmount,
            'schedule' => $schedule,
        ];
    }

    /**
     * Get overdue invoices
     */
    public function getOverdueInvoices(): Collection
    {
        return $this->model
            ->where('due_date', '<', now())
            ->whereNotIn('status', [InvoiceStatus::PAID, InvoiceStatus::CANCELLED])
            ->with(['enrollment.student'])
            ->get();
    }

    /**
     * Get payments by enrollment
     */
    public function getPaymentsByEnrollment(int $enrollmentId): Collection
    {
        return Payment::whereHas('invoice', function ($query) use ($enrollmentId) {
            $query->where('enrollment_id', $enrollmentId);
        })->with('invoice')->get();
    }

    /**
     * Get billing statistics
     */
    public function getStatistics(?string $fromDate = null, ?string $toDate = null): array
    {
        $query = $this->model->newQuery();

        if ($fromDate) {
            $query->where('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('created_at', '<=', $toDate);
        }

        $totalInvoices = $query->count();
        $totalAmount = $query->sum('total_amount');
        $totalPaid = $query->sum('paid_amount');
        $totalPending = $totalAmount - $totalPaid;

        return [
            'total_invoices' => $totalInvoices,
            'total_amount' => $totalAmount,
            'total_paid' => $totalPaid,
            'total_pending' => $totalPending,
        ];
    }

    /**
     * Format invoice for display
     */
    public function formatInvoiceForDisplay(Invoice $invoice): array
    {
        return [
            'invoice_number' => $invoice->invoice_number,
            'formatted_total' => CurrencyService::formatCents($invoice->total_amount * 100),
            'formatted_paid' => CurrencyService::formatCents($invoice->paid_amount * 100),
            'formatted_balance' => CurrencyService::formatCents(($invoice->total_amount - $invoice->paid_amount) * 100),
        ];
    }

    /**
     * Generate unique invoice number
     */
    protected function generateInvoiceNumber(): string
    {
        return 'INV-'.str_pad((string) random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);
    }

    /**
     * Get billing information for an enrollment
     */
    public function getBillingDetails(Enrollment $enrollment): array
    {
        $enrollment->load(['student', 'guardian']);

        // Get the grade level fee
        $gradeLevelFee = GradeLevelFee::where('grade_level', $enrollment->grade_level)->first();

        if (! $gradeLevelFee) {
            throw new \Exception('Grade level fee not found for '.$enrollment->grade_level->value);
        }

        $tuitionFee = (int) $gradeLevelFee->tuition_fee;
        $miscellaneousFee = (int) $gradeLevelFee->miscellaneous_fee;
        $laboratoryFee = (int) $gradeLevelFee->laboratory_fee;
        $libraryFee = (int) $gradeLevelFee->library_fee;
        $sportsFee = (int) $gradeLevelFee->sports_fee;

        $totalAmount = $tuitionFee + $miscellaneousFee + $laboratoryFee + $libraryFee + $sportsFee;
        $discount = 0; // This should be calculated based on business rules
        $netAmount = $totalAmount - $discount;

        // Update enrollment with billing details
        $enrollment->update([
            'tuition_fee_cents' => $tuitionFee,
            'miscellaneous_fee_cents' => $miscellaneousFee,
            'laboratory_fee_cents' => $laboratoryFee,
            'library_fee_cents' => $libraryFee,
            'sports_fee_cents' => $sportsFee,
            'total_amount_cents' => $totalAmount,
            'discount_cents' => $discount,
            'net_amount_cents' => $netAmount,
            'balance_cents' => $netAmount,
        ]);

        return [
            'student' => $enrollment->student,
            'guardian' => $enrollment->guardian,
            'fees' => [
                'tuition' => CurrencyService::formatCents($tuitionFee),
                'miscellaneous' => CurrencyService::formatCents($miscellaneousFee),
                'laboratory' => CurrencyService::formatCents($laboratoryFee),
                'library' => CurrencyService::formatCents($libraryFee),
                'sports' => CurrencyService::formatCents($sportsFee),
            ],
            'total' => CurrencyService::formatCents($totalAmount),
            'discount' => CurrencyService::formatCents($discount),
            'net_amount' => CurrencyService::formatCents($netAmount),
            'payment_status' => $enrollment->payment_status->label(),
            'amount_paid' => CurrencyService::formatCents($enrollment->amount_paid_cents),
            'balance' => CurrencyService::formatCents($enrollment->balance_cents),
            'payment_plans' => $this->getPaymentPlans($netAmount),
        ];
    }

    /**
     * Get available payment plans
     */
    public function getPaymentPlans(float $totalAmount): array
    {
        return [
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
    }

    /**
     * Process payment
     */
    public function processPayment(Enrollment $enrollment, float $amount, array $paymentDetails = []): array
    {
        return DB::transaction(function () use ($enrollment, $amount, $paymentDetails) {
            $amountCents = CurrencyService::toCents($amount);
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

            return [
                'enrollment' => $enrollment->fresh(),
                'payment_details' => array_merge($paymentDetails, [
                    'amount' => CurrencyService::formatCents($amountCents),
                    'new_balance' => CurrencyService::formatCents(max(0, $newBalance)),
                    'payment_status' => $newStatus->label(),
                ]),
            ];
        });
    }

    /**
     * Get guardian billing summary
     */
    public function getGuardianBillingSummary(int $guardianId): Collection
    {
        /** @var Collection */
        return Enrollment::where('guardian_id', $guardianId)
            ->with(['student', 'invoices'])
            ->get()
            ->map(function ($enrollment) {
                return [
                    'student' => $enrollment->student->first_name.' '.$enrollment->student->last_name,
                    'grade_level' => $enrollment->grade_level,
                    'total_amount' => $enrollment->net_amount,
                    'amount_paid' => $enrollment->amount_paid,
                    'balance' => $enrollment->balance,
                    'payment_status' => $enrollment->payment_status->label(),
                ];
            });
    }

    /**
     * Get payment history
     */
    public function getPaymentHistory(Enrollment $enrollment): Collection
    {
        return $enrollment->payments()->with('invoice')->orderBy('created_at', 'desc')->get();
    }

    /**
     * Calculate late fees
     */
    public function calculateLateFees(Enrollment $enrollment): float
    {
        if ($enrollment->payment_status === PaymentStatus::PAID) {
            return 0;
        }

        $daysLate = now()->diffInDays($enrollment->payment_due_date, false);
        if ($daysLate <= 0) {
            return 0;
        }

        // 5% late fee after 30 days
        if ($daysLate > 30) {
            return $enrollment->balance * 0.05;
        }

        return 0;
    }

    /**
     * Apply discount
     */
    public function applyDiscount(Enrollment $enrollment, string $discountType, float $discountValue): Enrollment
    {
        $discountAmount = 0;

        if ($discountType === 'percentage') {
            $discountAmount = $enrollment->total_amount * ($discountValue / 100);
        } else {
            $discountAmount = $discountValue;
        }

        $enrollment->update([
            'discount_cents' => CurrencyService::toCents($discountAmount),
            'net_amount_cents' => $enrollment->total_amount_cents - CurrencyService::toCents($discountAmount),
            'balance_cents' => $enrollment->total_amount_cents - CurrencyService::toCents($discountAmount) - $enrollment->amount_paid_cents,
        ]);

        return $enrollment->fresh();
    }

    /**
     * Get fee structure by grade level
     */
    public function getFeeStructure(string $gradeLevel): array
    {
        $gradeLevelFee = GradeLevelFee::where('grade_level', $gradeLevel)->first();

        if (! $gradeLevelFee) {
            return [
                'tuition_fee' => 0,
                'registration_fee' => 0,
                'miscellaneous_fee' => 0,
                'total' => 0,
            ];
        }

        return [
            'tuition_fee' => $gradeLevelFee->tuition_fee,
            'registration_fee' => $gradeLevelFee->registration_fee,
            'miscellaneous_fee' => $gradeLevelFee->miscellaneous_fee,
            'laboratory_fee' => $gradeLevelFee->laboratory_fee,
            'library_fee' => $gradeLevelFee->library_fee,
            'sports_fee' => $gradeLevelFee->sports_fee,
            'total' => $gradeLevelFee->tuition_fee + $gradeLevelFee->registration_fee +
                      $gradeLevelFee->miscellaneous_fee + $gradeLevelFee->laboratory_fee +
                      $gradeLevelFee->library_fee + $gradeLevelFee->sports_fee,
        ];
    }
}

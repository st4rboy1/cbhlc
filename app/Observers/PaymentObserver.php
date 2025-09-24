<?php

namespace App\Observers;

use App\Models\Payment;

class PaymentObserver
{
    /**
     * Handle the Payment "creating" event.
     */
    public function creating(Payment $payment): void
    {
        // Generate reference number if not provided
        if (empty($payment->reference_number)) {
            $payment->reference_number = $this->generateReferenceNumber();
        }

        // Set payment date if not provided
        if (empty($payment->payment_date)) {
            $payment->payment_date = now();
        }

        // Set status if not provided
        if (empty($payment->status)) {
            $payment->status = 'completed';
        }
    }

    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        // Update invoice balance
        if ($payment->invoice) {
            $invoice = $payment->invoice;
            $invoice->amount_paid_cents += $payment->amount_cents;
            $invoice->balance_cents = $invoice->total_amount_cents - $invoice->amount_paid_cents;

            // Update invoice status
            if ($invoice->balance_cents <= 0) {
                $invoice->status = 'paid';
                $invoice->paid_at = now();
            } else {
                $invoice->status = 'partial';
            }

            $invoice->save();
        }

        // Update enrollment if payment is for an enrollment invoice
        if ($payment->invoice && $payment->invoice->enrollment) {
            $enrollment = $payment->invoice->enrollment;
            $enrollment->amount_paid_cents += $payment->amount_cents;
            $enrollment->balance_cents = $enrollment->total_amount * 100 - $enrollment->amount_paid_cents;

            // Update payment status
            if ($enrollment->balance_cents <= 0) {
                $enrollment->payment_status = 'paid';
            } else {
                $enrollment->payment_status = 'partial';
            }

            $enrollment->save();
        }

        // Log payment creation
        activity()
            ->performedOn($payment)
            ->causedBy(auth()->user())
            ->log('Payment recorded: ' . number_format($payment->amount_cents / 100, 2) . ' for ' . $payment->invoice->invoice_number);
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        // Recalculate invoice balance if amount changed
        if ($payment->wasChanged('amount_cents')) {
            $oldAmount = $payment->getOriginal('amount_cents');
            $difference = $payment->amount_cents - $oldAmount;

            if ($payment->invoice) {
                $invoice = $payment->invoice;
                $invoice->amount_paid_cents += $difference;
                $invoice->balance_cents = $invoice->total_amount_cents - $invoice->amount_paid_cents;

                // Update invoice status
                if ($invoice->balance_cents <= 0) {
                    $invoice->status = 'paid';
                    $invoice->paid_at = now();
                } elseif ($invoice->amount_paid_cents > 0) {
                    $invoice->status = 'partial';
                } else {
                    $invoice->status = 'pending';
                }

                $invoice->save();
            }
        }

        // Log significant changes
        if ($payment->wasChanged(['amount_cents', 'status'])) {
            activity()
                ->performedOn($payment)
                ->causedBy(auth()->user())
                ->withProperties(['changes' => $payment->getChanges()])
                ->log('Payment updated: ' . $payment->reference_number);
        }
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        // Restore invoice balance
        if ($payment->invoice) {
            $invoice = $payment->invoice;
            $invoice->amount_paid_cents -= $payment->amount_cents;
            $invoice->balance_cents = $invoice->total_amount_cents - $invoice->amount_paid_cents;

            // Update invoice status
            if ($invoice->amount_paid_cents > 0) {
                $invoice->status = 'partial';
            } else {
                $invoice->status = 'pending';
                $invoice->paid_at = null;
            }

            $invoice->save();
        }

        // Log payment deletion
        activity()
            ->performedOn($payment)
            ->causedBy(auth()->user())
            ->log('Payment deleted: ' . $payment->reference_number);
    }

    /**
     * Generate a unique reference number.
     */
    private function generateReferenceNumber(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));

        return "PAY-{$date}-{$random}";
    }
}
<?php

namespace App\Observers;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
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
    }

    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        // Update invoice balance
        if ($payment->invoice) {
            $invoice = $payment->invoice;
            $invoice->paid_amount += ($payment->amount_cents / 100);

            // Update invoice status
            $balance = $invoice->total_amount - $invoice->paid_amount;
            if ($balance <= 0) {
                $invoice->status = InvoiceStatus::PAID;
                $invoice->paid_at = now();
            } else {
                $invoice->status = InvoiceStatus::PARTIALLY_PAID;
            }

            $invoice->save();
        }

        // Update enrollment if payment is for an enrollment invoice
        if ($payment->invoice && $payment->invoice->enrollment) {
            $enrollment = $payment->invoice->enrollment;
            $enrollment->amount_paid += ($payment->amount_cents / 100);
            $enrollment->balance = $enrollment->total_amount - $enrollment->amount_paid;

            // Update payment status
            if ($enrollment->balance <= 0) {
                $enrollment->payment_status = PaymentStatus::PAID;
            } else {
                $enrollment->payment_status = PaymentStatus::PARTIAL;
            }

            $enrollment->save();
        }

        // Note: Activity logging is handled automatically by LogsActivity trait
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
                $invoice->paid_amount += ($difference / 100);

                // Update invoice status
                $balance = $invoice->total_amount - $invoice->paid_amount;
                if ($balance <= 0) {
                    $invoice->status = InvoiceStatus::PAID;
                    $invoice->paid_at = now();
                } elseif ($invoice->paid_amount > 0) {
                    $invoice->status = InvoiceStatus::PARTIALLY_PAID;
                } else {
                    $invoice->status = InvoiceStatus::SENT;
                }

                $invoice->save();
            }
        }

        // Note: Activity logging is handled automatically by LogsActivity trait
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        // Restore invoice balance
        if ($payment->invoice) {
            $invoice = $payment->invoice;
            $invoice->paid_amount -= ($payment->amount_cents / 100);

            // Update invoice status
            if ($invoice->paid_amount > 0) {
                $invoice->status = InvoiceStatus::PARTIALLY_PAID;
            } else {
                $invoice->status = InvoiceStatus::SENT;
                $invoice->paid_at = null;
            }

            $invoice->save();
        }

        // Note: Activity logging is handled automatically by LogsActivity trait
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

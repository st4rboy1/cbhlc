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
    }

    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        // Update invoice balance and status
        if ($payment->invoice) {
            /** @var \App\Models\Invoice $invoice */
            $invoice = $payment->invoice;
            $invoice->updatePaidAmount();
        }

        // Note: Activity logging is handled automatically by LogsActivity trait

        // Notify guardian about the payment
        if ($payment->invoice && $payment->invoice->enrollment && $payment->invoice->enrollment->guardian && $payment->invoice->enrollment->guardian->user) {
            $payment->invoice->enrollment->guardian->user->notify(new \App\Notifications\PaymentReceivedNotification($payment));
        }
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        // Recalculate invoice balance if amount changed
        if ($payment->wasChanged('amount_cents')) {
            if ($payment->invoice) {
                /** @var \App\Models\Invoice $invoice */
                $invoice = $payment->invoice;
                $invoice->updatePaidAmount();
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
            /** @var \App\Models\Invoice $invoice */
            $invoice = $payment->invoice;
            $invoice->updatePaidAmount();
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

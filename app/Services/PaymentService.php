<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Notifications\PaymentReceivedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class PaymentService
{
    /**
     * Process a payment for an invoice
     */
    public function processPayment(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            // Create payment
            $payment = Payment::create([
                'invoice_id' => $data['invoice_id'],
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Update invoice status
            $invoice = $payment->invoice;
            if ($invoice instanceof Invoice) {
                $this->updateInvoiceStatus($invoice);
            }

            // Notify guardian
            if ($payment->invoice?->enrollment?->guardian?->user) {
                Notification::send($payment->invoice->enrollment->guardian->user, new PaymentReceivedNotification($payment->fresh('invoice')));
            }

            return $payment->fresh('invoice');
        });
    }

    /**
     * Update invoice status based on payments
     */
    public function updateInvoiceStatus(Invoice $invoice): Invoice
    {
        $totalPaid = $invoice->payments()->sum('amount');

        if ($totalPaid >= $invoice->total_amount) {
            $invoice->update(['status' => InvoiceStatus::PAID]);
        } elseif ($totalPaid > 0) {
            $invoice->update(['status' => InvoiceStatus::PARTIALLY_PAID]);
        } elseif ($invoice->due_date < now()) {
            $invoice->update(['status' => InvoiceStatus::OVERDUE]);
        }

        // Update enrollment payment status
        $enrollment = $invoice->enrollment;
        if ($enrollment) {
            $enrollment->update([
                'amount_paid_cents' => $totalPaid * 100,
                'balance_cents' => $enrollment->net_amount_cents - ($totalPaid * 100),
                'payment_status' => $enrollment->balance_cents <= 0 ? 'paid' : 'partial',
            ]);
        }

        return $invoice->fresh();
    }

    /**
     * Process a refund for a payment
     */
    public function processRefund(Payment $payment, float $amount, string $reason): Payment
    {
        return DB::transaction(function () use ($payment, $amount, $reason) {
            // Create refund payment
            $refund = Payment::create([
                'invoice_id' => $payment->invoice_id,
                'payment_date' => now(),
                'amount' => -$amount, // Negative amount for refund
                'payment_method' => $payment->payment_method,
                'reference_number' => $payment->reference_number ? "REFUND-{$payment->reference_number}" : null,
                'notes' => "Refund: {$reason}",
            ]);

            // Update invoice status
            $invoice = $payment->invoice;
            if ($invoice instanceof Invoice) {
                $this->updateInvoiceStatus($invoice);
            }

            return $refund;
        });
    }
}

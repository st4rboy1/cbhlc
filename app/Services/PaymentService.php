<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Process a payment for an invoice
     */
    public function processPayment(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            // Generate payment reference
            $data['payment_reference'] = $this->generatePaymentReference();

            // Create payment
            $payment = Payment::create([
                'invoice_id' => $data['invoice_id'],
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'payment_reference' => $data['payment_reference'],
                'status' => $data['status'] ?? 'completed',
                'notes' => $data['notes'] ?? null,
                'processed_by' => $data['processed_by'] ?? auth()->id(),
            ]);

            // Update invoice status
            if ($payment->invoice) {
                $this->updateInvoiceStatus($payment->invoice);
            }

            return $payment->fresh('invoice');
        });
    }

    /**
     * Update invoice status based on payments
     */
    public function updateInvoiceStatus(Invoice $invoice): Invoice
    {
        $totalPaid = $invoice->payments()
            ->where('status', 'completed')
            ->sum('amount');

        if ($totalPaid >= $invoice->total_amount) {
            $invoice->update(['status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $invoice->update(['status' => 'partially_paid']);
        } elseif ($invoice->due_date < now()) {
            $invoice->update(['status' => 'overdue']);
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
                'reference_number' => $payment->reference_number,
                'payment_reference' => $this->generatePaymentReference(),
                'status' => 'refunded',
                'notes' => "Refund for payment {$payment->payment_reference}: {$reason}",
                'processed_by' => auth()->id(),
                'parent_payment_id' => $payment->id,
            ]);

            // Update original payment status if fully refunded
            if ($amount >= $payment->amount) {
                $payment->update(['status' => 'refunded']);
            }

            // Update invoice status
            if ($payment->invoice) {
                $this->updateInvoiceStatus($payment->invoice);
            }

            return $refund;
        });
    }

    /**
     * Generate unique payment reference
     */
    protected function generatePaymentReference(): string
    {
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(Str::random(4));

        return "PAY-{$timestamp}-{$random}";
    }
}
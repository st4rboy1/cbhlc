<?php

namespace App\Observers;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;

class InvoiceObserver
{
    /**
     * Handle the Invoice "creating" event.
     */
    public function creating(Invoice $invoice): void
    {
        // Generate invoice number if not provided
        if (empty($invoice->invoice_number)) {
            $invoice->invoice_number = $this->generateInvoiceNumber();
        }

        // Set default status if not provided
        if (empty($invoice->status)) {
            $invoice->status = InvoiceStatus::SENT;
        }

        // Calculate balance if total amount is set
        if (! empty($invoice->total_amount) && empty($invoice->paid_amount)) {
            $invoice->paid_amount = 0;
        }
    }

    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        // Log invoice creation
        activity()
            ->performedOn($invoice)
            ->causedBy(auth()->user())
            ->log('Invoice created: '.$invoice->invoice_number);
    }

    /**
     * Handle the Invoice "updating" event.
     */
    public function updating(Invoice $invoice): void
    {
        // Update status based on payment
        if ($invoice->isDirty('paid_amount')) {
            $balance = $invoice->total_amount - $invoice->paid_amount;

            if ($balance <= 0) {
                $invoice->status = InvoiceStatus::PAID;
                $invoice->paid_at = now();
            } elseif ($invoice->paid_amount > 0) {
                $invoice->status = InvoiceStatus::PARTIALLY_PAID;
            }
        }
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        // Log significant changes
        if ($invoice->wasChanged(['status', 'paid_amount'])) {
            activity()
                ->performedOn($invoice)
                ->causedBy(auth()->user())
                ->withProperties(['changes' => $invoice->getChanges()])
                ->log('Invoice updated: '.$invoice->invoice_number);
        }

        // Update enrollment payment status if invoice is fully paid
        if ($invoice->wasChanged('status') && $invoice->status === InvoiceStatus::PAID && $invoice->enrollment) {
            $invoice->enrollment->update([
                'payment_status' => 'paid',
                'amount_paid' => $invoice->paid_amount,
                'balance' => 0,
            ]);
        }
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        activity()
            ->performedOn($invoice)
            ->causedBy(auth()->user())
            ->log('Invoice deleted: '.$invoice->invoice_number);
    }

    /**
     * Generate a unique invoice number.
     */
    private function generateInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');

        // Get the latest invoice for this month
        $latestInvoice = Invoice::where('invoice_number', 'like', "INV-{$year}{$month}%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($latestInvoice) {
            // Extract the sequence number and increment
            $sequence = intval(substr($latestInvoice->invoice_number, -4)) + 1;
        } else {
            // Start with 1 if no invoices for this month
            $sequence = 1;
        }

        return sprintf('INV-%s%s%04d', $year, $month, $sequence);
    }
}

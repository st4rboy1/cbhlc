<?php

namespace App\Observers;

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
            $invoice->status = 'pending';
        }

        // Calculate total amount from items if not set
        if (empty($invoice->total_amount_cents) && $invoice->items) {
            $total = $invoice->items->sum('amount_cents');
            $invoice->total_amount_cents = $total;
            $invoice->balance_cents = $total - ($invoice->amount_paid_cents ?? 0);
        }

        // Set issue date if not provided
        if (empty($invoice->issue_date)) {
            $invoice->issue_date = now();
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
            ->log('Invoice created: ' . $invoice->invoice_number);
    }

    /**
     * Handle the Invoice "updating" event.
     */
    public function updating(Invoice $invoice): void
    {
        // Recalculate balance when payment is made
        if ($invoice->isDirty('amount_paid_cents')) {
            $invoice->balance_cents = $invoice->total_amount_cents - $invoice->amount_paid_cents;

            // Update status based on payment
            if ($invoice->balance_cents <= 0) {
                $invoice->status = 'paid';
                $invoice->paid_at = now();
            } elseif ($invoice->amount_paid_cents > 0) {
                $invoice->status = 'partial';
            }
        }
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        // Log significant changes
        if ($invoice->wasChanged(['status', 'amount_paid_cents'])) {
            activity()
                ->performedOn($invoice)
                ->causedBy(auth()->user())
                ->withProperties(['changes' => $invoice->getChanges()])
                ->log('Invoice updated: ' . $invoice->invoice_number);
        }

        // Update enrollment payment status if invoice is fully paid
        if ($invoice->wasChanged('status') && $invoice->status === 'paid' && $invoice->enrollment) {
            $invoice->enrollment->update([
                'payment_status' => 'paid',
                'amount_paid_cents' => $invoice->amount_paid_cents,
                'balance_cents' => 0,
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
            ->log('Invoice deleted: ' . $invoice->invoice_number);
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
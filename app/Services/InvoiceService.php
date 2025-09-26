<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Create a new invoice with items
     */
    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            // Generate unique invoice number
            $data['invoice_number'] = $this->generateInvoiceNumber();
            $data['status'] = $data['status'] ?? 'draft';

            // Calculate total
            $total = collect($data['items'])->sum('amount');
            $data['total_amount'] = $total;

            // Create invoice
            $invoice = Invoice::create([
                'enrollment_id' => $data['enrollment_id'],
                'invoice_number' => $data['invoice_number'],
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'],
                'total_amount' => $data['total_amount'],
                'status' => $data['status'],
            ]);

            // Create invoice items
            foreach ($data['items'] as $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'amount' => $item['amount'],
                ]);
            }

            return $invoice->fresh('items');
        });
    }

    /**
     * Recalculate invoice totals
     */
    public function recalculateTotals(Invoice $invoice): Invoice
    {
        $total = $invoice->items()->sum('amount');
        $invoice->update(['total_amount' => $total]);

        return $invoice->fresh();
    }

    /**
     * Generate unique invoice number
     */
    protected function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $month = date('m');

        // Get the last invoice number for this month
        $lastInvoice = Invoice::where('invoice_number', 'like', "INV-{$year}{$month}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "INV-{$year}{$month}-{$newNumber}";
    }
}

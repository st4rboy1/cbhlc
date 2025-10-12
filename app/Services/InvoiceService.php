<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Enrollment;
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
            $data['status'] = $data['status'] ?? InvoiceStatus::DRAFT;

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
     * Generate invoice from enrollment
     */
    public function createInvoiceFromEnrollment(Enrollment $enrollment): Invoice
    {
        return DB::transaction(function () use ($enrollment) {
            // Calculate invoice details
            $items = [];

            if ($enrollment->tuition_fee_cents > 0) {
                $items[] = [
                    'description' => 'Tuition Fee - '.$enrollment->grade_level->label(),
                    'quantity' => 1,
                    'unit_price' => $enrollment->tuition_fee_cents / 100,
                    'amount' => $enrollment->tuition_fee_cents / 100,
                ];
            }

            if ($enrollment->miscellaneous_fee_cents > 0) {
                $items[] = [
                    'description' => 'Miscellaneous Fee',
                    'quantity' => 1,
                    'unit_price' => $enrollment->miscellaneous_fee_cents / 100,
                    'amount' => $enrollment->miscellaneous_fee_cents / 100,
                ];
            }

            if ($enrollment->laboratory_fee_cents > 0) {
                $items[] = [
                    'description' => 'Laboratory Fee',
                    'quantity' => 1,
                    'unit_price' => $enrollment->laboratory_fee_cents / 100,
                    'amount' => $enrollment->laboratory_fee_cents / 100,
                ];
            }

            if ($enrollment->library_fee_cents > 0) {
                $items[] = [
                    'description' => 'Library Fee',
                    'quantity' => 1,
                    'unit_price' => $enrollment->library_fee_cents / 100,
                    'amount' => $enrollment->library_fee_cents / 100,
                ];
            }

            if ($enrollment->sports_fee_cents > 0) {
                $items[] = [
                    'description' => 'Sports Fee',
                    'quantity' => 1,
                    'unit_price' => $enrollment->sports_fee_cents / 100,
                    'amount' => $enrollment->sports_fee_cents / 100,
                ];
            }

            // Apply discount if any
            if ($enrollment->discount_cents > 0) {
                $items[] = [
                    'description' => 'Discount',
                    'quantity' => 1,
                    'unit_price' => -($enrollment->discount_cents / 100),
                    'amount' => -($enrollment->discount_cents / 100),
                ];
            }

            // Create the invoice
            return $this->createInvoice([
                'enrollment_id' => $enrollment->id,
                'invoice_date' => now(),
                'due_date' => $enrollment->payment_due_date ?? now()->addDays(30),
                'status' => InvoiceStatus::SENT,
                'items' => $items,
            ]);
        });
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

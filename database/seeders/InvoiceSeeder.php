<?php

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Idempotent: Checks if invoices already exist before creating
     */
    public function run(): void
    {
        // Check if invoices already exist
        if (Invoice::count() > 0) {
            $this->command->info('Invoices already exist. Skipping invoice seeder.');

            return;
        }

        $this->command->info('Seeding invoices...');

        // Get enrollments without invoices (approved, enrolled, or completed status)
        $enrollments = Enrollment::with(['student', 'guardian'])
            ->whereNull('invoice_id')
            ->whereIn('status', ['approved', 'enrolled', 'completed'])
            ->get();

        if ($enrollments->isEmpty()) {
            $this->command->warn('No enrollments found without invoices (approved/enrolled/completed status). Please run EnrollmentSeeder first.');

            return;
        }

        $invoiceService = app(InvoiceService::class);
        $invoiceCount = 0;
        $itemCount = 0;

        foreach ($enrollments as $enrollment) {
            // Create invoice data
            $invoiceData = [
                'enrollment_id' => $enrollment->id,
                'invoice_date' => now()->subDays(rand(0, 30))->format('Y-m-d'),
                'due_date' => now()->addDays(rand(15, 60))->format('Y-m-d'),
                'items' => [],
            ];

            // Add tuition fee item
            if ($enrollment->tuition_fee_cents > 0) {
                $invoiceData['items'][] = [
                    'description' => 'Tuition Fee - '.$enrollment->grade_level->label(),
                    'quantity' => 1,
                    'unit_price' => $enrollment->tuition_fee,
                    'amount' => $enrollment->tuition_fee,
                ];
                $itemCount++;
            }

            // Add miscellaneous fee item
            if ($enrollment->miscellaneous_fee_cents > 0) {
                $invoiceData['items'][] = [
                    'description' => 'Miscellaneous Fee',
                    'quantity' => 1,
                    'unit_price' => $enrollment->miscellaneous_fee,
                    'amount' => $enrollment->miscellaneous_fee,
                ];
                $itemCount++;
            }

            // Add laboratory fee item if applicable
            if ($enrollment->laboratory_fee_cents > 0) {
                $invoiceData['items'][] = [
                    'description' => 'Laboratory Fee',
                    'quantity' => 1,
                    'unit_price' => $enrollment->laboratory_fee,
                    'amount' => $enrollment->laboratory_fee,
                ];
                $itemCount++;
            }

            // Add library fee item if applicable
            if ($enrollment->library_fee_cents > 0) {
                $invoiceData['items'][] = [
                    'description' => 'Library Fee',
                    'quantity' => 1,
                    'unit_price' => $enrollment->library_fee,
                    'amount' => $enrollment->library_fee,
                ];
                $itemCount++;
            }

            // Add sports fee item if applicable
            if ($enrollment->sports_fee_cents > 0) {
                $invoiceData['items'][] = [
                    'description' => 'Sports Fee',
                    'quantity' => 1,
                    'unit_price' => $enrollment->sports_fee,
                    'amount' => $enrollment->sports_fee,
                ];
                $itemCount++;
            }

            // Create invoice using the service
            $invoice = $invoiceService->createInvoice($invoiceData);

            // Update enrollment with invoice_id
            $enrollment->update(['invoice_id' => $invoice->id]);

            // Randomly set invoice status based on enrollment payment status
            $status = match ($enrollment->payment_status->value) {
                'paid' => InvoiceStatus::PAID,
                'partial' => InvoiceStatus::PARTIALLY_PAID,
                default => fake()->randomElement([InvoiceStatus::SENT, InvoiceStatus::DRAFT]),
            };

            // Update invoice status and paid amount
            $invoice->update([
                'status' => $status,
                'paid_amount' => $enrollment->amount_paid,
            ]);

            // If invoice is paid, set paid_at
            if ($status === InvoiceStatus::PAID) {
                $invoice->update(['paid_at' => now()->subDays(rand(1, 20))]);
            }

            $invoiceCount++;
        }

        $this->command->info("Created {$invoiceCount} invoices with {$itemCount} total items successfully.");
    }
}

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

        // Define invoice statuses to distribute evenly
        $statuses = [
            InvoiceStatus::DRAFT,
            InvoiceStatus::SENT,
            InvoiceStatus::PARTIALLY_PAID,
            InvoiceStatus::PAID,
            InvoiceStatus::OVERDUE,
        ];

        foreach ($enrollments as $index => $enrollment) {
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

            // Cycle through statuses to create variety
            $status = $statuses[$index % count($statuses)];

            // Set paid amount and paid_at based on status
            $updateData = ['status' => $status];

            switch ($status) {
                case InvoiceStatus::PAID:
                    $updateData['paid_amount'] = $invoice->total_amount;
                    $updateData['paid_at'] = now()->subDays(rand(1, 20));
                    break;

                case InvoiceStatus::PARTIALLY_PAID:
                    // Random percentage between 30% and 70%
                    $percentage = mt_rand(30, 70) / 100;
                    $updateData['paid_amount'] = $invoice->total_amount * $percentage;
                    break;

                case InvoiceStatus::OVERDUE:
                    // Overdue invoices have past due dates
                    $updateData['due_date'] = now()->subDays(rand(1, 30));
                    $updateData['paid_amount'] = 0;
                    break;

                case InvoiceStatus::DRAFT:
                case InvoiceStatus::SENT:
                default:
                    $updateData['paid_amount'] = 0;
                    break;
            }

            $invoice->update($updateData);

            $invoiceCount++;
        }

        $this->command->info("Created {$invoiceCount} invoices with {$itemCount} total items successfully.");
    }
}

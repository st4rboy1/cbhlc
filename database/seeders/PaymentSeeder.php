<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus as EnrollmentPaymentStatus;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Idempotent: Checks if payments already exist before creating
     */
    public function run(): void
    {
        // Check if payments already exist
        if (Payment::count() > 0) {
            $this->command->info('Payments already exist. Skipping payment seeder.');

            return;
        }

        $this->command->info('Seeding payments...');

        // Get enrollments that have been paid (partially or fully)
        $enrollments = Enrollment::with(['invoices', 'student', 'guardian'])
            ->whereIn('payment_status', [EnrollmentPaymentStatus::PARTIAL, EnrollmentPaymentStatus::PAID])
            ->has('invoices') // Ensure enrollment has at least one invoice
            ->get();

        if ($enrollments->isEmpty()) {
            $this->command->warn('No enrollments found with payments. Please run EnrollmentSeeder and InvoiceSeeder first.');

            return;
        }

        $paymentCount = 0;
        $paymentMethods = [
            PaymentMethod::CASH,
            PaymentMethod::BANK_TRANSFER,
            PaymentMethod::GCASH,
            PaymentMethod::CREDIT_CARD,
        ];

        foreach ($enrollments as $enrollment) {
            foreach ($enrollment->invoices as $invoice) {
                $amountPaidCents = $invoice->paid_amount * 100; // Use invoice's paid_amount

                if ($amountPaidCents <= 0) {
                    continue;
                }

                // Determine number of payments based on payment status
                if ($invoice->status->isPaid()) { // Use invoice status
                    // For fully paid, create 1-3 payments
                    $numPayments = rand(1, 3);
                } else {
                    // For partial, create 1-2 payments
                    $numPayments = rand(1, 2);
                }

                $remainingAmount = $amountPaidCents;
                $paymentsCreated = 0;

                for ($i = 0; $i < $numPayments && $remainingAmount > 0; $i++) {
                    // Calculate payment amount
                    if ($i === $numPayments - 1) {
                        // Last payment gets the remaining amount
                        $paymentAmountCents = $remainingAmount;
                    } else {
                        // Split remaining amount
                        $maxPayment = (int) ($remainingAmount * 0.7);
                        $minPayment = (int) ($remainingAmount * 0.3);
                        $paymentAmountCents = rand($minPayment, $maxPayment);
                    }

                    // Create payment
                    $faker = \Faker\Factory::create();
                    $payment = Payment::create([
                        'invoice_id' => $invoice->id,
                        'amount' => $paymentAmountCents / 100, // Convert cents to decimal
                        'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                        'payment_date' => now()->subDays(rand(1, 60)),
                        'reference_number' => 'REF-'.strtoupper($faker->bothify('???###???')),
                        'receipt_number' => 'RCP-'.now()->format('Ym').'-'.str_pad((string) ($paymentCount + 1), 6, '0', STR_PAD_LEFT),
                        'notes' => $i === 0 ? 'Initial payment' : 'Installment payment '.($i + 1),
                        'processed_by' => $enrollment->approved_by, // Assuming approved_by is still relevant
                    ]);

                    $remainingAmount -= $paymentAmountCents;
                    $paymentCount++;
                    $paymentsCreated++;
                }

                $this->command->info("Created {$paymentsCreated} payment(s) for invoice {$invoice->invoice_number} (Enrollment: {$enrollment->enrollment_id})");
            }
        }

        $this->command->info("Created {$paymentCount} payments successfully.");
    }
}

<?php

namespace Tests\Unit\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->paymentService = new PaymentService();
    }

    public function test_process_payment(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $invoice = Invoice::factory()->create([
            'total_amount' => 5000,
            'status' => 'sent',
        ]);

        $data = [
            'invoice_id' => $invoice->id,
            'payment_date' => now()->format('Y-m-d'),
            'amount' => 5000,
            'payment_method' => 'cash',
            'reference_number' => 'REF123',
            'notes' => 'Full payment',
        ];

        $payment = $this->paymentService->processPayment($data);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals(5000, $payment->amount);
        $this->assertEquals('cash', $payment->payment_method->value);
        $this->assertEquals('REF123', $payment->reference_number);

        // Check invoice status was updated
        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::PAID, $invoice->status);
    }

    public function test_partial_payment_updates_invoice_status(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $invoice = Invoice::factory()->create([
            'total_amount' => 5000,
            'status' => 'sent',
        ]);

        $data = [
            'invoice_id' => $invoice->id,
            'payment_date' => now()->format('Y-m-d'),
            'amount' => 2000, // Partial payment
            'payment_method' => 'cash',
        ];

        $payment = $this->paymentService->processPayment($data);

        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::PARTIALLY_PAID, $invoice->status);
    }

    public function test_update_invoice_status_with_full_payment(): void
    {
        $invoice = Invoice::factory()->create([
            'total_amount' => 1000,
            'status' => 'sent',
        ]);

        // Create payment equal to invoice total
        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 1000,
        ]);

        $invoice = $this->paymentService->updateInvoiceStatus($invoice);

        $this->assertEquals(InvoiceStatus::PAID, $invoice->status);
    }

    public function test_update_invoice_status_with_partial_payment(): void
    {
        $invoice = Invoice::factory()->create([
            'total_amount' => 1000,
            'status' => 'sent',
        ]);

        // Create partial payment
        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 500,
        ]);

        $invoice = $this->paymentService->updateInvoiceStatus($invoice);

        $this->assertEquals(InvoiceStatus::PARTIALLY_PAID, $invoice->status);
    }

    public function test_update_invoice_status_overdue(): void
    {
        $invoice = Invoice::factory()->create([
            'total_amount' => 1000,
            'status' => 'sent',
            'due_date' => now()->subDay(), // Past due date
        ]);

        $invoice = $this->paymentService->updateInvoiceStatus($invoice);

        $this->assertEquals(InvoiceStatus::OVERDUE, $invoice->status);
    }

    public function test_process_refund(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $invoice = Invoice::factory()->create([
            'total_amount' => 5000,
        ]);

        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 5000,
            'reference_number' => 'PAY-123',
        ]);

        $refund = $this->paymentService->processRefund($payment, 2000, 'Customer request');

        $this->assertInstanceOf(Payment::class, $refund);
        $this->assertEquals(-2000, $refund->amount);
        $this->assertStringContainsString('Customer request', $refund->notes);
    }

    public function test_process_full_refund(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $invoice = Invoice::factory()->create([
            'total_amount' => 5000,
        ]);

        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 5000,
        ]);

        $refund = $this->paymentService->processRefund($payment, 5000, 'Full refund');

        $this->assertEquals(-5000, $refund->amount);
    }

    public function test_payment_with_reference_number(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $invoice = Invoice::factory()->create();

        $data = [
            'invoice_id' => $invoice->id,
            'payment_date' => now()->format('Y-m-d'),
            'amount' => 1000,
            'payment_method' => 'bank_transfer',
            'reference_number' => 'BANK-REF-12345',
        ];

        $payment = $this->paymentService->processPayment($data);

        $this->assertEquals('BANK-REF-12345', $payment->reference_number);
    }
}
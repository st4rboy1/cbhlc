<?php

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\BillingService;
use App\Services\CurrencyService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles and permissions for each test
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->service = new BillingService(new Invoice);
});

test('getPaginatedInvoices returns paginated results with relationships', function () {
    Invoice::factory()->count(15)->create();

    $result = $this->service->getPaginatedInvoices([], 10);

    expect($result)->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class);
    expect($result->count())->toBe(10);
    expect($result->total())->toBe(15);
});

test('getPaginatedInvoices applies status filter', function () {
    Invoice::factory()->create(['status' => InvoiceStatus::DRAFT]);
    Invoice::factory()->create(['status' => InvoiceStatus::SENT]);
    Invoice::factory()->create(['status' => InvoiceStatus::PAID]);

    $result = $this->service->getPaginatedInvoices(['status' => InvoiceStatus::SENT->value], 10);

    expect($result->count())->toBe(1);
    expect($result->first()->status)->toBe(InvoiceStatus::SENT);
});

test('getPaginatedInvoices applies enrollment filter', function () {
    $enrollment1 = Enrollment::factory()->create();
    $enrollment2 = Enrollment::factory()->create();

    Invoice::factory()->create(['enrollment_id' => $enrollment1->id]);
    Invoice::factory()->create(['enrollment_id' => $enrollment2->id]);

    $result = $this->service->getPaginatedInvoices(['enrollment_id' => $enrollment1->id], 10);

    expect($result->count())->toBe(1);
    expect($result->first()->enrollment_id)->toBe($enrollment1->id);
});

test('getPaginatedInvoices applies date range filter', function () {
    Invoice::factory()->create(['due_date' => now()->subDays(5)]);
    Invoice::factory()->create(['due_date' => now()->subDays(2)]);
    Invoice::factory()->create(['due_date' => now()->addDays(5)]);

    $result = $this->service->getPaginatedInvoices([
        'due_from' => now()->subDays(3)->toDateString(),
        'due_to' => now()->addDays(1)->toDateString(),
    ], 10);

    expect($result->count())->toBe(1);
});

test('findWithPayments returns invoice with payment history', function () {
    $invoice = Invoice::factory()->create();
    Payment::factory()->count(3)->create(['invoice_id' => $invoice->id]);

    $result = $this->service->findWithPayments($invoice->id);

    expect($result)->toBeInstanceOf(Invoice::class);
    expect($result->relationLoaded('payments'))->toBe(true);
    expect($result->payments)->toHaveCount(3);
});

test('generateInvoice creates invoice for enrollment', function () {
    // Create a grade level fee for testing
    \App\Models\GradeLevelFee::factory()->create([
        'grade_level' => 'Grade 1',
        'tuition_fee_cents' => 5000000,
        'registration_fee_cents' => 500000,
        'miscellaneous_fee_cents' => 1000000,
    ]);
    $enrollment = Enrollment::factory()->create(['grade_level' => 'Grade 1']);

    $result = $this->service->generateInvoice($enrollment);

    expect($result)->toBeInstanceOf(Invoice::class);
    expect($result->enrollment_id)->toBe($enrollment->id);
    expect($result->total_amount)->toBeGreaterThan(0);
    expect($result->status)->toBe(InvoiceStatus::DRAFT);
    expect($result->invoice_number)->toStartWith('INV-');
});

test('generateInvoice creates invoice items', function () {
    // Create a grade level fee for testing
    \App\Models\GradeLevelFee::factory()->create([
        'grade_level' => 'Grade 1',
        'tuition_fee_cents' => 5000000,
        'registration_fee_cents' => 500000,
        'miscellaneous_fee_cents' => 1000000,
    ]);
    $enrollment = Enrollment::factory()->create(['grade_level' => 'Grade 1']);

    $result = $this->service->generateInvoice($enrollment);

    expect($result->items->count())->toBeGreaterThan(0);
    expect($result->items->first()->description)->toContain('Fee');
});

test('generateInvoice uses database transaction', function () {
    DB::shouldReceive('transaction')
        ->once()
        ->andReturnUsing(function ($callback) {
            return $callback();
        });

    $enrollment = Enrollment::factory()->create();

    $this->service->generateInvoice($enrollment);
});

test('recordPayment creates payment record and updates invoice', function () {
    $invoice = Invoice::factory()->create([
        'total_amount' => 10000,
        'paid_amount' => 0,
        'status' => InvoiceStatus::SENT,
    ]);

    $data = [
        'amount' => 5000,
        'payment_method' => PaymentMethod::CASH->value,
        'payment_date' => now()->toDateString(),
        'reference_number' => 'PAY-123456',
    ];

    $result = $this->service->recordPayment($invoice, $data);

    expect($result)->toBeInstanceOf(Payment::class);
    expect((float) $result->amount)->toBe(5000.0);
    expect($result->payment_method)->toBe(PaymentMethod::CASH);

    $invoice->refresh();
    expect((float) $invoice->paid_amount)->toBe(5000.0);
    expect($invoice->status)->toBe(InvoiceStatus::PARTIALLY_PAID);
});

test('recordPayment marks invoice as paid when fully paid', function () {
    $invoice = Invoice::factory()->create([
        'total_amount' => 10000,
        'paid_amount' => 5000,
        'status' => InvoiceStatus::PARTIALLY_PAID,
    ]);

    $data = [
        'amount' => 5000,
        'payment_method' => PaymentMethod::BANK_TRANSFER->value,
        'payment_date' => now()->toDateString(),
    ];

    $this->service->recordPayment($invoice, $data);

    $invoice->refresh();
    expect((float) $invoice->paid_amount)->toBe(10000.0);
    expect($invoice->status)->toBe(InvoiceStatus::PAID);
    expect($invoice->paid_at)->not->toBeNull();
});

test('recordPayment throws exception for overpayment', function () {
    $invoice = Invoice::factory()->create([
        'total_amount' => 10000,
        'paid_amount' => 9000,
    ]);

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Payment amount exceeds remaining balance');

    $this->service->recordPayment($invoice, ['amount' => 2000]);
});

test('calculatePaymentPlan returns installment schedule', function () {
    $result = $this->service->calculatePaymentPlan(100000, 'monthly');

    expect($result)->toHaveKeys(['plan', 'installments', 'discount', 'final_amount', 'schedule']);
    expect($result['plan'])->toBe('monthly');
    expect($result['installments'])->toBe(10);
    expect($result['discount'])->toBe(0);
    expect($result['final_amount'])->toBe(100000.0);
    expect($result['schedule'])->toHaveCount(10);
    expect($result['schedule'][0]['amount'])->toBe(10000.0);
});

test('calculatePaymentPlan applies discount for full payment', function () {
    $result = $this->service->calculatePaymentPlan(100000, 'full');

    expect($result['discount'])->toBe(0.05);
    expect($result['final_amount'])->toBe(95000.0);
    expect($result['schedule'])->toHaveCount(1);
    expect($result['schedule'][0]['amount'])->toBe(95000.0);
});

test('calculatePaymentPlan applies discount for semestral payment', function () {
    $result = $this->service->calculatePaymentPlan(100000, 'semestral');

    expect($result['discount'])->toBe(0.03);
    expect($result['final_amount'])->toBe(97000.0);
    expect($result['installments'])->toBe(2);
    expect($result['schedule'][0]['amount'])->toBe(48500.0);
});

test('calculatePaymentPlan defaults to monthly for invalid plan', function () {
    $result = $this->service->calculatePaymentPlan(100000, 'invalid');

    expect($result['plan'])->toBe('monthly');
    expect($result['installments'])->toBe(10);
});

test('getOverdueInvoices returns invoices past due date', function () {
    Invoice::factory()->create([
        'due_date' => now()->subDays(5),
        'status' => InvoiceStatus::SENT,
    ]);
    Invoice::factory()->create([
        'due_date' => now()->subDays(2),
        'status' => InvoiceStatus::PARTIALLY_PAID,
    ]);
    Invoice::factory()->create([
        'due_date' => now()->addDays(5),
        'status' => InvoiceStatus::SENT,
    ]);
    Invoice::factory()->create([
        'due_date' => now()->subDays(10),
        'status' => InvoiceStatus::PAID,
    ]);

    $result = $this->service->getOverdueInvoices();

    expect($result)->toHaveCount(2);
    expect($result->every(fn ($inv) => $inv->due_date < now()))->toBe(true);
    expect($result->every(fn ($inv) => $inv->status !== InvoiceStatus::PAID))->toBe(true);
});

test('getPaymentsByEnrollment returns all payments for enrollment', function () {
    $enrollment = Enrollment::factory()->create();
    $invoice1 = Invoice::factory()->create(['enrollment_id' => $enrollment->id]);
    $invoice2 = Invoice::factory()->create(['enrollment_id' => $enrollment->id]);
    $otherInvoice = Invoice::factory()->create();

    Payment::factory()->count(2)->create(['invoice_id' => $invoice1->id]);
    Payment::factory()->count(3)->create(['invoice_id' => $invoice2->id]);
    Payment::factory()->create(['invoice_id' => $otherInvoice->id]);

    $result = $this->service->getPaymentsByEnrollment($enrollment->id);

    expect($result)->toHaveCount(5);
});

test('getStatistics returns billing statistics', function () {
    // Create invoices with different statuses
    Invoice::factory()->count(3)->create(['status' => InvoiceStatus::PAID, 'total_amount' => 10000]);
    Invoice::factory()->count(2)->create(['status' => InvoiceStatus::SENT, 'total_amount' => 15000]);
    Invoice::factory()->create(['status' => InvoiceStatus::PARTIALLY_PAID, 'total_amount' => 20000, 'paid_amount' => 10000]);

    $result = $this->service->getStatistics();

    expect($result)->toHaveKeys(['total_invoices', 'total_amount', 'total_paid', 'total_pending']);
    expect($result['total_invoices'])->toBe(6);
    expect((float) $result['total_amount'])->toBe(80000.0);
    expect($result['total_paid'])->toBeGreaterThan(0);
    expect($result['total_pending'])->toBeNumeric();
});

test('getStatistics filters by date range', function () {
    Invoice::factory()->create(['created_at' => now()->subDays(10), 'total_amount' => 10000]);
    Invoice::factory()->create(['created_at' => now()->subDays(2), 'total_amount' => 15000]);
    Invoice::factory()->create(['created_at' => now(), 'total_amount' => 20000]);

    $result = $this->service->getStatistics(
        now()->subDays(3)->toDateString(),
        now()->toDateString()
    );

    expect($result['total_invoices'])->toBeGreaterThan(0);
    expect($result['total_amount'])->toBeGreaterThan(0);
});

test('formatInvoiceForDisplay formats invoice with currency', function () {
    $invoice = Invoice::factory()->create([
        'total_amount' => 123456,
        'paid_amount' => 100000,
    ]);

    $result = $this->service->formatInvoiceForDisplay($invoice);

    expect($result)->toHaveKeys(['invoice_number', 'formatted_total', 'formatted_paid', 'formatted_balance']);
    expect($result['formatted_total'])->toBe(CurrencyService::formatCents(12345600));
    expect($result['formatted_paid'])->toBe(CurrencyService::formatCents(10000000));
    expect($result['formatted_balance'])->toBe(CurrencyService::formatCents(2345600));
});

test('generateInvoiceNumber creates unique invoice number', function () {
    // Use reflection to test protected method
    $reflection = new ReflectionClass($this->service);
    $method = $reflection->getMethod('generateInvoiceNumber');
    $method->setAccessible(true);

    $number1 = $method->invoke($this->service);
    $number2 = $method->invoke($this->service);

    expect($number1)->toStartWith('INV-');
    expect($number2)->toStartWith('INV-');
    expect($number1)->not->toBe($number2);
    expect(strlen($number1))->toBe(14); // INV- + 10 digits
});

test('logActivity is called for main operations', function () {
    Log::spy();

    $invoice = Invoice::factory()->create();
    $enrollment = Enrollment::factory()->create();

    $this->service->getPaginatedInvoices();
    $this->service->findWithPayments($invoice->id);
    $this->service->generateInvoice($enrollment);

    Log::shouldHaveReceived('info')->times(3);
});

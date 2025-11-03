<?php

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles and permissions
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('receipt can be created with valid data', function () {
    $payment = Payment::factory()->create();
    $user = User::factory()->create();

    $receipt = Receipt::create([
        'receipt_number' => Receipt::generateReceiptNumber(),
        'payment_id' => $payment->id,
        'invoice_id' => $payment->invoice_id,
        'receipt_date' => now(),
        'amount' => 1000.00,
        'payment_method' => 'cash',
        'received_by' => $user->id,
        'notes' => 'Test receipt',
    ]);

    expect($receipt)->toBeInstanceOf(Receipt::class)
        ->and($receipt->receipt_number)->toBeString()
        ->and($receipt->amount)->toBe('1000.00')
        ->and($receipt->payment_method)->toBe('cash');
});

test('receipt belongs to a payment', function () {
    $receipt = Receipt::factory()->create();

    expect($receipt->payment)->toBeInstanceOf(Payment::class)
        ->and($receipt->payment_id)->toBe($receipt->payment->id);
});

test('receipt belongs to an invoice', function () {
    $receipt = Receipt::factory()->create();

    expect($receipt->invoice)->toBeInstanceOf(Invoice::class)
        ->and($receipt->invoice_id)->toBe($receipt->invoice->id);
});

test('receipt has a receivedBy relationship', function () {
    $user = User::factory()->create();
    $receipt = Receipt::factory()->receivedBy($user)->create();

    expect($receipt->receivedBy)->toBeInstanceOf(User::class)
        ->and($receipt->received_by)->toBe($user->id);
});

test('generateReceiptNumber creates unique sequential numbers', function () {
    $receipt1 = Receipt::generateReceiptNumber();
    $receipt2 = Receipt::generateReceiptNumber();

    expect($receipt1)->toMatch('/^OR-\d{4}-\d{4}$/')
        ->and($receipt2)->toMatch('/^OR-\d{4}-\d{4}$/');
});

test('generateReceiptNumber includes current year', function () {
    $year = now()->year;
    $receiptNumber = Receipt::generateReceiptNumber();

    expect($receiptNumber)->toContain("OR-{$year}-");
});

test('generateReceiptNumber increments sequence correctly', function () {
    // Create receipts to establish a sequence
    Receipt::factory()->create(['receipt_number' => 'OR-2025-0001']);
    Receipt::factory()->create(['receipt_number' => 'OR-2025-0002']);

    $nextNumber = Receipt::generateReceiptNumber();

    expect($nextNumber)->toBe('OR-2025-0003');
});

test('formatted receipt number attribute returns receipt number', function () {
    $receipt = Receipt::factory()->create(['receipt_number' => 'OR-2025-0123']);

    expect($receipt->formatted_receipt_number)->toBe('OR-2025-0123');
});

test('formatted amount attribute returns peso-formatted amount', function () {
    $receipt = Receipt::factory()->amount(1234.56)->create();

    expect($receipt->formatted_amount)->toBe('₱1,234.56');
});

test('receipt number must be unique', function () {
    Receipt::factory()->create(['receipt_number' => 'OR-2025-0001']);

    expect(fn () => Receipt::factory()->create(['receipt_number' => 'OR-2025-0001']))
        ->toThrow(Exception::class);
});

test('receipt date is cast to date', function () {
    $receipt = Receipt::factory()->create(['receipt_date' => '2025-10-25']);

    expect($receipt->receipt_date)->toBeInstanceOf(Illuminate\Support\Carbon::class);
});

test('amount is cast to decimal with 2 places', function () {
    $receipt = Receipt::factory()->amount(100.5)->create();

    expect($receipt->amount)->toBe('100.50');
});

<?php

use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\SchoolYear;
use App\Models\Student;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('can create an invoice item with all attributes', function () {
    $student = Student::factory()->create();
    $schoolYear = SchoolYear::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
    ]);
    $invoice = Invoice::factory()->create([
        'enrollment_id' => $enrollment->id,
    ]);

    $invoiceItem = InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'description' => 'Tuition Fee',
        'quantity' => 1,
        'unit_price' => 15000.00,
        'amount' => 15000.00,
    ]);

    expect($invoiceItem)->toBeInstanceOf(InvoiceItem::class)
        ->and($invoiceItem->description)->toBe('Tuition Fee')
        ->and($invoiceItem->quantity)->toBe(1)
        ->and($invoiceItem->unit_price)->toBe('15000.00')
        ->and($invoiceItem->amount)->toBe('15000.00');
});

it('belongs to an invoice', function () {
    $student = Student::factory()->create();
    $schoolYear = SchoolYear::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
    ]);
    $invoice = Invoice::factory()->create([
        'enrollment_id' => $enrollment->id,
    ]);

    $invoiceItem = InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'description' => 'Miscellaneous Fee',
        'quantity' => 1,
        'unit_price' => 2500.00,
        'amount' => 2500.00,
    ]);

    expect($invoiceItem->invoice)->toBeInstanceOf(Invoice::class)
        ->and($invoiceItem->invoice->id)->toBe($invoice->id);
});

it('casts quantity as integer', function () {
    $student = Student::factory()->create();
    $schoolYear = SchoolYear::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
    ]);
    $invoice = Invoice::factory()->create([
        'enrollment_id' => $enrollment->id,
    ]);

    $invoiceItem = InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'description' => 'Books',
        'quantity' => '5',
        'unit_price' => 500.00,
        'amount' => 2500.00,
    ]);

    expect($invoiceItem->quantity)->toBeInt()
        ->and($invoiceItem->quantity)->toBe(5);
});

it('casts unit_price and amount as decimal with 2 places', function () {
    $student = Student::factory()->create();
    $schoolYear = SchoolYear::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
    ]);
    $invoice = Invoice::factory()->create([
        'enrollment_id' => $enrollment->id,
    ]);

    $invoiceItem = InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'description' => 'Lab Fee',
        'quantity' => 1,
        'unit_price' => 1234.567,
        'amount' => 1234.567,
    ]);

    expect($invoiceItem->unit_price)->toBe('1234.57')
        ->and($invoiceItem->amount)->toBe('1234.57');
});

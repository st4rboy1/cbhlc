<?php

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    // Create school year
    $this->sy2025 = \App\Models\SchoolYear::firstOrCreate([
        'name' => '2025-2026',
        'start_year' => 2025,
        'end_year' => 2026,
        'start_date' => '2025-06-01',
        'end_date' => '2026-05-31',
        'status' => 'active',
    ]);
});

test('enrollment model stores money values in cents and returns float', function () {
    $enrollment = new Enrollment;

    // Test tuition fee attribute
    $enrollment->tuition_fee = 15000.50;
    expect($enrollment->tuition_fee_cents)->toBe(1500050);
    expect($enrollment->tuition_fee)->toBe(15000.50);

    // Test miscellaneous fee attribute
    $enrollment->miscellaneous_fee = 1234.25;
    expect($enrollment->miscellaneous_fee_cents)->toBe(123425);
    expect($enrollment->miscellaneous_fee)->toBe(1234.25);

    // Test total amount attribute (PHP converts to int when value is whole number)
    $enrollment->total_amount = 20000.00;
    expect($enrollment->total_amount_cents)->toBe(2000000);
    expect((float) $enrollment->total_amount)->toBe(20000.0);
});

test('enrollment model calculates amounts correctly', function () {
    $enrollment = new Enrollment([
        'tuition_fee_cents' => 1500000, // 15000.00
        'miscellaneous_fee_cents' => 50000, // 500.00
        'laboratory_fee_cents' => 25000, // 250.00
        'library_fee_cents' => 10000, // 100.00
        'sports_fee_cents' => 15000, // 150.00
        'total_amount_cents' => 1600000, // 16000.00
        'discount_cents' => 100000, // 1000.00
    ]);

    expect($enrollment->calculateTotalAmount())->toBe(16000.00);
    expect($enrollment->calculateNetAmount())->toBe(15000.00);
});

test('enrollment model balance calculation works correctly', function () {
    $enrollment = new Enrollment([
        'net_amount_cents' => 1500000, // 15000.00
        'amount_paid_cents' => 500000, // 5000.00
        'balance_cents' => 1000000, // 10000.00 - stored value
    ]);

    expect($enrollment->calculateBalance())->toBe(10000.00);
    expect((float) $enrollment->balance)->toBe(10000.00);
});

test('enrollment model isFullyPaid works correctly', function () {
    $paidEnrollment = new Enrollment([
        'payment_status' => PaymentStatus::PAID,
        'balance_cents' => 0,
    ]);

    $partialEnrollment = new Enrollment([
        'payment_status' => PaymentStatus::PARTIAL,
        'balance_cents' => 500000,
    ]);

    $zeroBbEnrollment = new Enrollment([
        'payment_status' => PaymentStatus::PARTIAL,
        'balance_cents' => 0,
    ]);

    expect($paidEnrollment->isFullyPaid())->toBeTrue();
    expect($partialEnrollment->isFullyPaid())->toBeFalse();
    expect($zeroBbEnrollment->isFullyPaid())->toBeTrue();
});

test('enrollment model isApproved works correctly', function () {
    $pendingEnrollment = new Enrollment(['status' => EnrollmentStatus::PENDING]);
    $approvedEnrollment = new Enrollment(['status' => EnrollmentStatus::APPROVED]);
    $enrolledEnrollment = new Enrollment(['status' => EnrollmentStatus::ENROLLED]);
    $rejectedEnrollment = new Enrollment(['status' => EnrollmentStatus::REJECTED]);

    expect($pendingEnrollment->isApproved())->toBeFalse();
    expect($approvedEnrollment->isApproved())->toBeTrue();
    expect($enrolledEnrollment->isApproved())->toBeTrue();
    expect($rejectedEnrollment->isApproved())->toBeFalse();
});

test('enrollment model guardian relationship works', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');

    $guardian = \App\Models\Guardian::create([
        'user_id' => $user->id,
        'first_name' => 'Test',
        'last_name' => 'Guardian',
        'contact_number' => '09123456789',
        'address' => '123 Test St',
    ]);

    $student = Student::factory()->create();

    $enrollment = Enrollment::create([
        'enrollment_id' => 'ENR-2025-001',
        'student_id' => $student->id,
        'guardian_id' => $guardian->id,
        'school_year_id' => $this->sy2025->id,
        'quarter' => 'First',
        'grade_level' => 'Grade 1',
        'status' => EnrollmentStatus::PENDING,
        'tuition_fee_cents' => 1500000,
        'total_amount_cents' => 1500000,
        'net_amount_cents' => 1500000,
        'payment_status' => PaymentStatus::PENDING,
        'amount_paid_cents' => 0,
        'balance_cents' => 1500000,
    ]);

    expect($enrollment->guardian)->not->toBeNull();
    expect($enrollment->guardian->id)->toBe($guardian->id);
    expect($enrollment->student->id)->toBe($student->id);
});

test('enrollment model enum casting works correctly', function () {
    $enrollment = new Enrollment([
        'status' => 'pending',
        'payment_status' => 'partial',
    ]);

    expect($enrollment->status)->toBeInstanceOf(EnrollmentStatus::class);
    expect($enrollment->status)->toBe(EnrollmentStatus::PENDING);
    expect($enrollment->payment_status)->toBeInstanceOf(PaymentStatus::class);
    expect($enrollment->payment_status)->toBe(PaymentStatus::PARTIAL);
});

test('enrollment model handles zero amounts correctly', function () {
    $enrollment = new Enrollment;

    $enrollment->discount = 0.00;
    expect($enrollment->discount_cents)->toBe(0);
    expect((float) $enrollment->discount)->toBe(0.0);

    $enrollment->amount_paid = 0.00;
    expect($enrollment->amount_paid_cents)->toBe(0);
    expect((float) $enrollment->amount_paid)->toBe(0.0);
});

test('enrollment model handles large amounts correctly', function () {
    $enrollment = new Enrollment;

    // Test very large amounts
    $enrollment->tuition_fee = 999999.99;
    expect($enrollment->tuition_fee_cents)->toBe(99999999);
    expect($enrollment->tuition_fee)->toBe(999999.99);

    // Test precision with many decimal places (should round to 2)
    $enrollment->miscellaneous_fee = 1234.12345;
    expect($enrollment->miscellaneous_fee_cents)->toBe(123412);
    expect($enrollment->miscellaneous_fee)->toBe(1234.12);
});

test('enrollment model handles negative amounts correctly', function () {
    $enrollment = new Enrollment;

    // Test negative discount (which shouldn't happen but should handle gracefully)
    $enrollment->discount = -100.50;
    expect($enrollment->discount_cents)->toBe(-10050);
    expect($enrollment->discount)->toBe(-100.50);
});

test('enrollment model Laravel 12 attribute syntax works correctly', function () {
    $enrollment = new Enrollment;

    // Test all money fields use Laravel 12 Attribute syntax
    $moneyFields = [
        'tuition_fee' => 'tuition_fee_cents',
        'miscellaneous_fee' => 'miscellaneous_fee_cents',
        'laboratory_fee' => 'laboratory_fee_cents',
        'library_fee' => 'library_fee_cents',
        'sports_fee' => 'sports_fee_cents',
        'total_amount' => 'total_amount_cents',
        'net_amount' => 'net_amount_cents',
        'discount' => 'discount_cents',
        'amount_paid' => 'amount_paid_cents',
        'balance' => 'balance_cents',
    ];

    foreach ($moneyFields as $floatField => $centsField) {
        $testValue = 123.45;
        $enrollment->{$floatField} = $testValue;

        expect($enrollment->{$centsField})->toBe(12345)
            ->and($enrollment->{$floatField})->toBe($testValue);
    }
});

<?php

use App\Enums\EnrollmentStatus;

test('enrollment status enum has correct values', function () {
    expect(EnrollmentStatus::PENDING->value)->toBe('pending');
    expect(EnrollmentStatus::APPROVED->value)->toBe('approved');
    expect(EnrollmentStatus::REJECTED->value)->toBe('rejected');
    expect(EnrollmentStatus::READY_FOR_PAYMENT->value)->toBe('ready_for_payment');
    expect(EnrollmentStatus::PAID->value)->toBe('paid');
    expect(EnrollmentStatus::ENROLLED->value)->toBe('enrolled');
    expect(EnrollmentStatus::COMPLETED->value)->toBe('completed');
});

test('enrollment status enum labels are correct', function () {
    expect(EnrollmentStatus::PENDING->label())->toBe('Pending Review');
    expect(EnrollmentStatus::APPROVED->label())->toBe('Approved - Awaiting Invoice');
    expect(EnrollmentStatus::REJECTED->label())->toBe('Rejected');
    expect(EnrollmentStatus::READY_FOR_PAYMENT->label())->toBe('Ready for Payment');
    expect(EnrollmentStatus::PAID->label())->toBe('Paid - Awaiting Confirmation');
    expect(EnrollmentStatus::ENROLLED->label())->toBe('Enrolled');
    expect(EnrollmentStatus::COMPLETED->label())->toBe('Completed');
});

test('enrollment status enum colors are correct', function () {
    expect(EnrollmentStatus::PENDING->color())->toBe('warning');
    expect(EnrollmentStatus::APPROVED->color())->toBe('info');
    expect(EnrollmentStatus::REJECTED->color())->toBe('destructive');
    expect(EnrollmentStatus::READY_FOR_PAYMENT->color())->toBe('warning');
    expect(EnrollmentStatus::PAID->color())->toBe('success');
    expect(EnrollmentStatus::ENROLLED->color())->toBe('primary');
    expect(EnrollmentStatus::COMPLETED->color())->toBe('muted');
});

test('enrollment status isModifiable works correctly', function () {
    expect(EnrollmentStatus::PENDING->isModifiable())->toBeTrue();
    expect(EnrollmentStatus::APPROVED->isModifiable())->toBeFalse();
    expect(EnrollmentStatus::REJECTED->isModifiable())->toBeFalse();
    expect(EnrollmentStatus::READY_FOR_PAYMENT->isModifiable())->toBeFalse();
    expect(EnrollmentStatus::PAID->isModifiable())->toBeFalse();
    expect(EnrollmentStatus::ENROLLED->isModifiable())->toBeFalse();
    expect(EnrollmentStatus::COMPLETED->isModifiable())->toBeFalse();
});

test('enrollment status isApproved works correctly', function () {
    expect(EnrollmentStatus::PENDING->isApproved())->toBeFalse();
    expect(EnrollmentStatus::APPROVED->isApproved())->toBeTrue();
    expect(EnrollmentStatus::REJECTED->isApproved())->toBeFalse();
    expect(EnrollmentStatus::READY_FOR_PAYMENT->isApproved())->toBeTrue();
    expect(EnrollmentStatus::PAID->isApproved())->toBeTrue();
    expect(EnrollmentStatus::ENROLLED->isApproved())->toBeTrue();
    expect(EnrollmentStatus::COMPLETED->isApproved())->toBeTrue();
});

test('enrollment status values method returns correct array', function () {
    $values = EnrollmentStatus::values();

    expect($values)->toContain('pending');
    expect($values)->toContain('approved');
    expect($values)->toContain('rejected');
    expect($values)->toContain('ready_for_payment');
    expect($values)->toContain('paid');
    expect($values)->toContain('enrolled');
    expect($values)->toContain('completed');
    expect($values)->toHaveCount(7);
});

test('enrollment status enum can be created from string values', function () {
    expect(EnrollmentStatus::from('pending'))->toBe(EnrollmentStatus::PENDING);
    expect(EnrollmentStatus::from('approved'))->toBe(EnrollmentStatus::APPROVED);
    expect(EnrollmentStatus::from('rejected'))->toBe(EnrollmentStatus::REJECTED);
    expect(EnrollmentStatus::from('ready_for_payment'))->toBe(EnrollmentStatus::READY_FOR_PAYMENT);
    expect(EnrollmentStatus::from('paid'))->toBe(EnrollmentStatus::PAID);
    expect(EnrollmentStatus::from('enrolled'))->toBe(EnrollmentStatus::ENROLLED);
    expect(EnrollmentStatus::from('completed'))->toBe(EnrollmentStatus::COMPLETED);
});

test('enrollment status enum throws exception for invalid values', function () {
    EnrollmentStatus::from('invalid');
})->throws(ValueError::class);

test('enrollment status requiresPayment works correctly', function () {
    expect(EnrollmentStatus::PENDING->requiresPayment())->toBeFalse();
    expect(EnrollmentStatus::APPROVED->requiresPayment())->toBeFalse();
    expect(EnrollmentStatus::REJECTED->requiresPayment())->toBeFalse();
    expect(EnrollmentStatus::READY_FOR_PAYMENT->requiresPayment())->toBeTrue();
    expect(EnrollmentStatus::PAID->requiresPayment())->toBeFalse();
    expect(EnrollmentStatus::ENROLLED->requiresPayment())->toBeFalse();
    expect(EnrollmentStatus::COMPLETED->requiresPayment())->toBeFalse();
});

test('enrollment status isPaid works correctly', function () {
    expect(EnrollmentStatus::PENDING->isPaid())->toBeFalse();
    expect(EnrollmentStatus::APPROVED->isPaid())->toBeFalse();
    expect(EnrollmentStatus::REJECTED->isPaid())->toBeFalse();
    expect(EnrollmentStatus::READY_FOR_PAYMENT->isPaid())->toBeFalse();
    expect(EnrollmentStatus::PAID->isPaid())->toBeTrue();
    expect(EnrollmentStatus::ENROLLED->isPaid())->toBeTrue();
    expect(EnrollmentStatus::COMPLETED->isPaid())->toBeTrue();
});

test('enrollment status nextStatus workflow is correct', function () {
    expect(EnrollmentStatus::PENDING->nextStatus())->toBe(EnrollmentStatus::APPROVED);
    expect(EnrollmentStatus::APPROVED->nextStatus())->toBe(EnrollmentStatus::READY_FOR_PAYMENT);
    expect(EnrollmentStatus::READY_FOR_PAYMENT->nextStatus())->toBe(EnrollmentStatus::PAID);
    expect(EnrollmentStatus::PAID->nextStatus())->toBe(EnrollmentStatus::ENROLLED);
    expect(EnrollmentStatus::ENROLLED->nextStatus())->toBe(EnrollmentStatus::COMPLETED);
    expect(EnrollmentStatus::COMPLETED->nextStatus())->toBeNull();
    expect(EnrollmentStatus::REJECTED->nextStatus())->toBeNull();
});

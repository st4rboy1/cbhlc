<?php

use App\Enums\EnrollmentStatus;

test('enrollment status enum has correct values', function () {
    expect(EnrollmentStatus::PENDING->value)->toBe('pending');
    expect(EnrollmentStatus::APPROVED->value)->toBe('approved');
    expect(EnrollmentStatus::REJECTED->value)->toBe('rejected');
    expect(EnrollmentStatus::ENROLLED->value)->toBe('enrolled');
});

test('enrollment status enum labels are correct', function () {
    expect(EnrollmentStatus::PENDING->label())->toBe('Pending Review');
    expect(EnrollmentStatus::APPROVED->label())->toBe('Approved');
    expect(EnrollmentStatus::REJECTED->label())->toBe('Rejected');
    expect(EnrollmentStatus::ENROLLED->label())->toBe('Enrolled');
});

test('enrollment status enum colors are correct', function () {
    expect(EnrollmentStatus::PENDING->color())->toBe('warning');
    expect(EnrollmentStatus::APPROVED->color())->toBe('success');
    expect(EnrollmentStatus::REJECTED->color())->toBe('destructive');
    expect(EnrollmentStatus::ENROLLED->color())->toBe('primary');
});

test('enrollment status isModifiable works correctly', function () {
    expect(EnrollmentStatus::PENDING->isModifiable())->toBeTrue();
    expect(EnrollmentStatus::APPROVED->isModifiable())->toBeFalse();
    expect(EnrollmentStatus::REJECTED->isModifiable())->toBeFalse();
    expect(EnrollmentStatus::ENROLLED->isModifiable())->toBeFalse();
});

test('enrollment status isApproved works correctly', function () {
    expect(EnrollmentStatus::PENDING->isApproved())->toBeFalse();
    expect(EnrollmentStatus::APPROVED->isApproved())->toBeTrue();
    expect(EnrollmentStatus::REJECTED->isApproved())->toBeFalse();
    expect(EnrollmentStatus::ENROLLED->isApproved())->toBeTrue();
});

test('enrollment status values method returns correct array', function () {
    $values = EnrollmentStatus::values();

    expect($values)->toContain('pending');
    expect($values)->toContain('approved');
    expect($values)->toContain('rejected');
    expect($values)->toContain('enrolled');
    expect($values)->toHaveCount(4);
});

test('enrollment status enum can be created from string values', function () {
    expect(EnrollmentStatus::from('pending'))->toBe(EnrollmentStatus::PENDING);
    expect(EnrollmentStatus::from('approved'))->toBe(EnrollmentStatus::APPROVED);
    expect(EnrollmentStatus::from('rejected'))->toBe(EnrollmentStatus::REJECTED);
    expect(EnrollmentStatus::from('enrolled'))->toBe(EnrollmentStatus::ENROLLED);
});

test('enrollment status enum throws exception for invalid values', function () {
    EnrollmentStatus::from('invalid');
})->throws(ValueError::class);

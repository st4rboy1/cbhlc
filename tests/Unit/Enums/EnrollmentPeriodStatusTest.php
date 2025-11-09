<?php

use App\Enums\EnrollmentPeriodStatus;

test('enrollment period status enum has correct values', function () {
    expect(EnrollmentPeriodStatus::UPCOMING->value)->toBe('upcoming');
    expect(EnrollmentPeriodStatus::ACTIVE->value)->toBe('active');
    expect(EnrollmentPeriodStatus::CLOSED->value)->toBe('closed');
});

test('enrollment period status enum labels are correct', function () {
    expect(EnrollmentPeriodStatus::UPCOMING->label())->toBe('Upcoming');
    expect(EnrollmentPeriodStatus::ACTIVE->label())->toBe('Active');
    expect(EnrollmentPeriodStatus::CLOSED->label())->toBe('Closed');
});

test('enrollment period status enum colors are correct', function () {
    expect(EnrollmentPeriodStatus::UPCOMING->color())->toBe('info');
    expect(EnrollmentPeriodStatus::ACTIVE->color())->toBe('success');
    expect(EnrollmentPeriodStatus::CLOSED->color())->toBe('muted');
});

test('enrollment period status isActive works correctly', function () {
    expect(EnrollmentPeriodStatus::UPCOMING->isActive())->toBeFalse();
    expect(EnrollmentPeriodStatus::ACTIVE->isActive())->toBeTrue();
    expect(EnrollmentPeriodStatus::CLOSED->isActive())->toBeFalse();
});

test('enrollment period status isClosed works correctly', function () {
    expect(EnrollmentPeriodStatus::UPCOMING->isClosed())->toBeFalse();
    expect(EnrollmentPeriodStatus::ACTIVE->isClosed())->toBeFalse();
    expect(EnrollmentPeriodStatus::CLOSED->isClosed())->toBeTrue();
});

test('enrollment period status isUpcoming works correctly', function () {
    expect(EnrollmentPeriodStatus::UPCOMING->isUpcoming())->toBeTrue();
    expect(EnrollmentPeriodStatus::ACTIVE->isUpcoming())->toBeFalse();
    expect(EnrollmentPeriodStatus::CLOSED->isUpcoming())->toBeFalse();
});

test('enrollment period status values method returns correct array', function () {
    $values = EnrollmentPeriodStatus::values();

    expect($values)->toContain('upcoming');
    expect($values)->toContain('active');
    expect($values)->toContain('closed');
    expect($values)->toHaveCount(3);
});

test('enrollment period status enum can be created from string values', function () {
    expect(EnrollmentPeriodStatus::from('upcoming'))->toBe(EnrollmentPeriodStatus::UPCOMING);
    expect(EnrollmentPeriodStatus::from('active'))->toBe(EnrollmentPeriodStatus::ACTIVE);
    expect(EnrollmentPeriodStatus::from('closed'))->toBe(EnrollmentPeriodStatus::CLOSED);
});

test('enrollment period status enum throws exception for invalid values', function () {
    EnrollmentPeriodStatus::from('invalid');
})->throws(ValueError::class);

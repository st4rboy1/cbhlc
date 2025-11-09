<?php

use App\Enums\VerificationStatus;

test('verification status enum has correct values', function () {
    expect(VerificationStatus::PENDING->value)->toBe('pending');
    expect(VerificationStatus::VERIFIED->value)->toBe('verified');
    expect(VerificationStatus::REJECTED->value)->toBe('rejected');
});

test('verification status enum labels are correct', function () {
    expect(VerificationStatus::PENDING->label())->toBe('Pending Verification');
    expect(VerificationStatus::VERIFIED->label())->toBe('Verified');
    expect(VerificationStatus::REJECTED->label())->toBe('Rejected');
});

test('verification status enum colors are correct', function () {
    expect(VerificationStatus::PENDING->color())->toBe('yellow');
    expect(VerificationStatus::VERIFIED->color())->toBe('green');
    expect(VerificationStatus::REJECTED->color())->toBe('red');
});

test('verification status enum can be created from string values', function () {
    expect(VerificationStatus::from('pending'))->toBe(VerificationStatus::PENDING);
    expect(VerificationStatus::from('verified'))->toBe(VerificationStatus::VERIFIED);
    expect(VerificationStatus::from('rejected'))->toBe(VerificationStatus::REJECTED);
});

test('verification status enum throws exception for invalid values', function () {
    VerificationStatus::from('invalid');
})->throws(ValueError::class);

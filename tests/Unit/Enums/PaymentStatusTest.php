<?php

use App\Enums\PaymentStatus;

test('payment status enum has correct values', function () {
    expect(PaymentStatus::PENDING->value)->toBe('pending');
    expect(PaymentStatus::PARTIAL->value)->toBe('partial');
    expect(PaymentStatus::PAID->value)->toBe('paid');
});

test('payment status enum labels are correct', function () {
    expect(PaymentStatus::PENDING->label())->toBe('Pending');
    expect(PaymentStatus::PARTIAL->label())->toBe('Partial Payment');
    expect(PaymentStatus::PAID->label())->toBe('Paid');
});

test('payment status enum colors are correct', function () {
    expect(PaymentStatus::PENDING->color())->toBe('red');
    expect(PaymentStatus::PARTIAL->color())->toBe('yellow');
    expect(PaymentStatus::PAID->color())->toBe('green');
});

test('payment status values method returns correct array', function () {
    $values = PaymentStatus::values();

    expect($values)->toContain('pending');
    expect($values)->toContain('partial');
    expect($values)->toContain('paid');
    expect($values)->toHaveCount(3);
});

test('payment status enum can be created from string values', function () {
    expect(PaymentStatus::from('pending'))->toBe(PaymentStatus::PENDING);
    expect(PaymentStatus::from('partial'))->toBe(PaymentStatus::PARTIAL);
    expect(PaymentStatus::from('paid'))->toBe(PaymentStatus::PAID);
});

test('payment status enum throws exception for invalid values', function () {
    PaymentStatus::from('invalid');
})->throws(ValueError::class);
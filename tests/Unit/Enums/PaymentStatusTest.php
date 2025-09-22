<?php

use App\Enums\PaymentStatus;

test('payment status enum has correct values', function () {
    expect(PaymentStatus::PENDING->value)->toBe('pending');
    expect(PaymentStatus::PARTIAL->value)->toBe('partial');
    expect(PaymentStatus::PAID->value)->toBe('paid');
    expect(PaymentStatus::OVERDUE->value)->toBe('overdue');
});

test('payment status enum labels are correct', function () {
    expect(PaymentStatus::PENDING->label())->toBe('Pending');
    expect(PaymentStatus::PARTIAL->label())->toBe('Partial Payment');
    expect(PaymentStatus::PAID->label())->toBe('Paid');
    expect(PaymentStatus::OVERDUE->label())->toBe('Overdue');
});

test('payment status enum colors are correct', function () {
    expect(PaymentStatus::PENDING->color())->toBe('red');
    expect(PaymentStatus::PARTIAL->color())->toBe('yellow');
    expect(PaymentStatus::PAID->color())->toBe('green');
    expect(PaymentStatus::OVERDUE->color())->toBe('orange');
});

test('payment status values method returns correct array', function () {
    $values = PaymentStatus::values();

    expect($values)->toContain('pending');
    expect($values)->toContain('partial');
    expect($values)->toContain('paid');
    expect($values)->toContain('overdue');
    expect($values)->toHaveCount(4);
});

test('payment status enum can be created from string values', function () {
    expect(PaymentStatus::from('pending'))->toBe(PaymentStatus::PENDING);
    expect(PaymentStatus::from('partial'))->toBe(PaymentStatus::PARTIAL);
    expect(PaymentStatus::from('paid'))->toBe(PaymentStatus::PAID);
    expect(PaymentStatus::from('overdue'))->toBe(PaymentStatus::OVERDUE);
});

test('payment status enum throws exception for invalid values', function () {
    PaymentStatus::from('invalid');
})->throws(ValueError::class);

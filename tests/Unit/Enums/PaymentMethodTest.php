<?php

use App\Enums\PaymentMethod;

test('payment method enum has correct values', function () {
    expect(PaymentMethod::CASH->value)->toBe('cash');
    expect(PaymentMethod::BANK_TRANSFER->value)->toBe('bank_transfer');

    expect(PaymentMethod::GCASH->value)->toBe('gcash');

});

test('payment method enum labels are correct', function () {
    expect(PaymentMethod::CASH->label())->toBe('Cash');
    expect(PaymentMethod::BANK_TRANSFER->label())->toBe('Bank Transfer');

});

test('payment method enum icons are correct', function () {
    expect(PaymentMethod::CASH->icon())->toBe('banknotes');
    expect(PaymentMethod::BANK_TRANSFER->icon())->toBe('building-2');

});

test('payment method values method returns correct array', function () {
    $values = PaymentMethod::values();

    expect($values)->toContain('cash');
    expect($values)->toContain('bank_transfer');

    expect($values)->toHaveCount(3);
});

test('payment method enum can be created from string values', function () {
    expect(PaymentMethod::from('cash'))->toBe(PaymentMethod::CASH);
    expect(PaymentMethod::from('bank_transfer'))->toBe(PaymentMethod::BANK_TRANSFER);

});

test('payment method enum throws exception for invalid values', function () {
    PaymentMethod::from('invalid');
})->throws(ValueError::class);

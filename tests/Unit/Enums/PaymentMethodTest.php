<?php

use App\Enums\PaymentMethod;

test('payment method enum has correct values', function () {
    expect(PaymentMethod::CASH->value)->toBe('cash');
    expect(PaymentMethod::BANK_TRANSFER->value)->toBe('bank_transfer');
    expect(PaymentMethod::CHECK->value)->toBe('check');
    expect(PaymentMethod::CREDIT_CARD->value)->toBe('credit_card');
    expect(PaymentMethod::GCASH->value)->toBe('gcash');
    expect(PaymentMethod::PAYMAYA->value)->toBe('paymaya');
});

test('payment method enum labels are correct', function () {
    expect(PaymentMethod::CASH->label())->toBe('Cash');
    expect(PaymentMethod::BANK_TRANSFER->label())->toBe('Bank Transfer');
    expect(PaymentMethod::CHECK->label())->toBe('Check');
    expect(PaymentMethod::CREDIT_CARD->label())->toBe('Credit Card');
    expect(PaymentMethod::GCASH->label())->toBe('GCash');
    expect(PaymentMethod::PAYMAYA->label())->toBe('PayMaya');
});

test('payment method enum icons are correct', function () {
    expect(PaymentMethod::CASH->icon())->toBe('banknotes');
    expect(PaymentMethod::BANK_TRANSFER->icon())->toBe('building-2');
    expect(PaymentMethod::CHECK->icon())->toBe('clipboard');
    expect(PaymentMethod::CREDIT_CARD->icon())->toBe('credit-card');
    expect(PaymentMethod::GCASH->icon())->toBe('device-mobile');
    expect(PaymentMethod::PAYMAYA->icon())->toBe('device-mobile');
});

test('payment method values method returns correct array', function () {
    $values = PaymentMethod::values();

    expect($values)->toContain('cash');
    expect($values)->toContain('bank_transfer');
    expect($values)->toContain('check');
    expect($values)->toContain('credit_card');
    expect($values)->toContain('gcash');
    expect($values)->toContain('paymaya');
    expect($values)->toHaveCount(6);
});

test('payment method enum can be created from string values', function () {
    expect(PaymentMethod::from('cash'))->toBe(PaymentMethod::CASH);
    expect(PaymentMethod::from('bank_transfer'))->toBe(PaymentMethod::BANK_TRANSFER);
    expect(PaymentMethod::from('check'))->toBe(PaymentMethod::CHECK);
    expect(PaymentMethod::from('credit_card'))->toBe(PaymentMethod::CREDIT_CARD);
    expect(PaymentMethod::from('gcash'))->toBe(PaymentMethod::GCASH);
    expect(PaymentMethod::from('paymaya'))->toBe(PaymentMethod::PAYMAYA);
});

test('payment method enum throws exception for invalid values', function () {
    PaymentMethod::from('invalid');
})->throws(ValueError::class);

<?php

use App\Enums\InvoiceStatus;

test('invoice status enum has correct values', function () {
    expect(InvoiceStatus::DRAFT->value)->toBe('draft');
    expect(InvoiceStatus::SENT->value)->toBe('sent');
    expect(InvoiceStatus::PARTIALLY_PAID->value)->toBe('partially_paid');
    expect(InvoiceStatus::PAID->value)->toBe('paid');
    expect(InvoiceStatus::CANCELLED->value)->toBe('cancelled');
    expect(InvoiceStatus::OVERDUE->value)->toBe('overdue');
});

test('invoice status enum labels are correct', function () {
    expect(InvoiceStatus::DRAFT->label())->toBe('Draft');
    expect(InvoiceStatus::SENT->label())->toBe('Sent');
    expect(InvoiceStatus::PARTIALLY_PAID->label())->toBe('Partially Paid');
    expect(InvoiceStatus::PAID->label())->toBe('Paid');
    expect(InvoiceStatus::CANCELLED->label())->toBe('Cancelled');
    expect(InvoiceStatus::OVERDUE->label())->toBe('Overdue');
});

test('invoice status enum colors are correct', function () {
    expect(InvoiceStatus::DRAFT->color())->toBe('gray');
    expect(InvoiceStatus::SENT->color())->toBe('blue');
    expect(InvoiceStatus::PARTIALLY_PAID->color())->toBe('yellow');
    expect(InvoiceStatus::PAID->color())->toBe('green');
    expect(InvoiceStatus::CANCELLED->color())->toBe('red');
    expect(InvoiceStatus::OVERDUE->color())->toBe('orange');
});

test('invoice status values method returns correct array', function () {
    $values = InvoiceStatus::values();

    expect($values)->toContain('draft');
    expect($values)->toContain('sent');
    expect($values)->toContain('partially_paid');
    expect($values)->toContain('paid');
    expect($values)->toContain('cancelled');
    expect($values)->toContain('overdue');
    expect($values)->toHaveCount(6);
});

test('invoice status enum can be created from string values', function () {
    expect(InvoiceStatus::from('draft'))->toBe(InvoiceStatus::DRAFT);
    expect(InvoiceStatus::from('sent'))->toBe(InvoiceStatus::SENT);
    expect(InvoiceStatus::from('partially_paid'))->toBe(InvoiceStatus::PARTIALLY_PAID);
    expect(InvoiceStatus::from('paid'))->toBe(InvoiceStatus::PAID);
    expect(InvoiceStatus::from('cancelled'))->toBe(InvoiceStatus::CANCELLED);
    expect(InvoiceStatus::from('overdue'))->toBe(InvoiceStatus::OVERDUE);
});

test('invoice status enum throws exception for invalid values', function () {
    InvoiceStatus::from('invalid');
})->throws(ValueError::class);

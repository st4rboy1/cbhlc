<?php

use App\Services\CurrencyService;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('currency service formats amounts correctly', function () {
    config([
        'currency.default' => [
            'symbol' => '₱',
            'symbol_position' => 'before',
            'code' => 'PHP',
        ],
        'currency.number_format' => [
            'decimals' => 2,
            'decimal_separator' => '.',
            'thousands_separator' => ',',
        ],
    ]);

    expect(CurrencyService::format(1234.56))->toBe('₱1,234.56');
    expect(CurrencyService::format(0.00))->toBe('₱0.00');
    expect(CurrencyService::format(999999.99))->toBe('₱999,999.99');
});

test('currency service formats amounts with symbol after', function () {
    config([
        'currency.default' => [
            'symbol' => 'PHP',
            'symbol_position' => 'after',
            'code' => 'PHP',
        ],
        'currency.number_format' => [
            'decimals' => 2,
            'decimal_separator' => '.',
            'thousands_separator' => ',',
        ],
    ]);

    expect(CurrencyService::format(1234.56))->toBe('1,234.56 PHP');
});

test('currency service formats cents correctly', function () {
    config([
        'currency.default' => [
            'symbol' => '₱',
            'symbol_position' => 'before',
            'code' => 'PHP',
        ],
        'currency.number_format' => [
            'decimals' => 2,
            'decimal_separator' => '.',
            'thousands_separator' => ',',
        ],
    ]);

    expect(CurrencyService::formatCents(123456))->toBe('₱1,234.56');
    expect(CurrencyService::formatCents(0))->toBe('₱0.00');
    expect(CurrencyService::formatCents(99999999))->toBe('₱999,999.99');
});

test('currency service returns correct symbol', function () {
    config(['currency.default.symbol' => '₱']);
    expect(CurrencyService::symbol())->toBe('₱');
});

test('currency service returns correct code', function () {
    config(['currency.default.code' => 'PHP']);
    expect(CurrencyService::code())->toBe('PHP');
});

test('currency service converts dollars to cents correctly', function () {
    expect(CurrencyService::toCents(123.45))->toBe(12345);
    expect(CurrencyService::toCents(0.00))->toBe(0);
    expect(CurrencyService::toCents(999.99))->toBe(99999);
    expect(CurrencyService::toCents(100.00))->toBe(10000);
});

test('currency service converts cents to dollars correctly', function () {
    expect(CurrencyService::fromCents(12345))->toBe(123.45);
    expect(CurrencyService::fromCents(0))->toBe(0.00);
    expect(CurrencyService::fromCents(99999))->toBe(999.99);
    expect(CurrencyService::fromCents(10000))->toBe(100.00);
});

test('currency service handles rounding for cents conversion', function () {
    // Test rounding to nearest cent
    expect(CurrencyService::toCents(123.456))->toBe(12346); // rounds up
    expect(CurrencyService::toCents(123.454))->toBe(12345); // rounds down
    expect(CurrencyService::toCents(123.455))->toBe(12346); // rounds up (banker's rounding)
});
<?php

namespace App\Services;

class CurrencyService
{
    /**
     * Format a value as the application's configured currency.
     */
    public static function format(float $amount): string
    {
        $config = config('currency.default');
        $numberFormat = config('currency.number_format');

        $formattedNumber = number_format(
            $amount,
            $numberFormat['decimals'],
            $numberFormat['decimal_separator'],
            $numberFormat['thousands_separator']
        );

        if ($config['symbol_position'] === 'before') {
            return $config['symbol'].$formattedNumber;
        }

        return $formattedNumber.' '.$config['symbol'];
    }

    /**
     * Format a value in cents as the application's configured currency.
     */
    public static function formatCents(int $amountInCents): string
    {
        $divisor = 10 ** config('currency.default.decimal_places', 2);

        return self::format($amountInCents / $divisor);
    }

    /**
     * Get the currency symbol
     */
    public static function symbol(): string
    {
        return config('currency.default.symbol');
    }

    /**
     * Get the currency code
     */
    public static function code(): string
    {
        return config('currency.default.code');
    }

    /**
     * Convert to cents for storage
     */
    public static function toCents(float $amount): int
    {
        $divisor = 10 ** config('currency.default.decimal_places', 2);

        return (int) round($amount * $divisor);
    }

    /**
     * Convert cents to the main currency unit for display
     */
    public static function fromCents(int $amountInCents): float
    {
        $divisor = 10 ** config('currency.default.decimal_places', 2);

        return $amountInCents / $divisor;
    }
}

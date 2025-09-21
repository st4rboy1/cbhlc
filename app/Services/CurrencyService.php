<?php

namespace App\Services;

class CurrencyService
{
    /**
     * Format a value as Philippine Peso currency
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
            return $config['symbol'] . $formattedNumber;
        }

        return $formattedNumber . ' ' . $config['symbol'];
    }

    /**
     * Format a value in cents as Philippine Peso currency
     */
    public static function formatCents(int $amountInCents): string
    {
        return self::format($amountInCents / 100);
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
     * Convert dollars to cents for storage
     */
    public static function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Convert cents to dollars for display
     */
    public static function fromCents(int $amountInCents): float
    {
        return $amountInCents / 100;
    }
}

<?php

namespace App\Enums;

enum PaymentPlan: string
{
    case ANNUAL = 'annual';
    case SEMESTRAL = 'semestral';
    case QUARTERLY = 'quarterly';
    case MONTHLY = 'monthly';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::ANNUAL => 'Annual',
            self::SEMESTRAL => 'Semestral',
            self::QUARTERLY => 'Quarterly',
            self::MONTHLY => 'Monthly',
        };
    }

    /**
     * Get the number of installments for this payment plan
     */
    public function installments(): int
    {
        return match ($this) {
            self::ANNUAL => 1,
            self::SEMESTRAL => 2,
            self::QUARTERLY => 4,
            self::MONTHLY => 10, // Typically 10 months in a school year
        };
    }

    /**
     * Get a description of the payment plan
     */
    public function description(): string
    {
        return match ($this) {
            self::ANNUAL => 'Pay full amount once per year',
            self::SEMESTRAL => 'Pay in 2 installments per year',
            self::QUARTERLY => 'Pay in 4 installments per year',
            self::MONTHLY => 'Pay in 10 monthly installments',
        };
    }
}

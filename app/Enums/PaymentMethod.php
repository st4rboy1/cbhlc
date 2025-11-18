<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case BANK_TRANSFER = 'bank_transfer';
    case GCASH = 'gcash';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Cash',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::GCASH => 'GCash',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CASH => 'banknotes',
            self::BANK_TRANSFER => 'building-2',
            self::GCASH => 'device-mobile',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

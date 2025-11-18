<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case BANK_TRANSFER = 'bank_transfer';
    case CHECK = 'check';
    case CREDIT_CARD = 'credit_card';
    case GCASH = 'gcash';
    case PAYMAYA = 'paymaya';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Cash',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::CHECK => 'Check',
            self::CREDIT_CARD => 'Credit Card',
            self::GCASH => 'GCash',
            self::PAYMAYA => 'PayMaya',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CASH => 'banknotes',
            self::BANK_TRANSFER => 'building-2',
            self::CHECK => 'clipboard',
            self::CREDIT_CARD => 'credit-card',
            self::GCASH => 'device-mobile',
            self::PAYMAYA => 'device-mobile',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

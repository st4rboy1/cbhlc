<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PARTIAL = 'partial';
    case PAID = 'paid';
    case OVERDUE = 'overdue';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PARTIAL => 'Partial Payment',
            self::PAID => 'Paid',
            self::OVERDUE => 'Overdue',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'red',
            self::PARTIAL => 'yellow',
            self::PAID => 'green',
            self::OVERDUE => 'orange',
        };
    }
}

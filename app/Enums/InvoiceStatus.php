<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case PARTIALLY_PAID = 'partially_paid';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case OVERDUE = 'overdue';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SENT => 'Sent',
            self::PARTIALLY_PAID => 'Partially Paid',
            self::PAID => 'Paid',
            self::CANCELLED => 'Cancelled',
            self::OVERDUE => 'Overdue',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SENT => 'blue',
            self::PARTIALLY_PAID => 'yellow',
            self::PAID => 'green',
            self::CANCELLED => 'red',
            self::OVERDUE => 'orange',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

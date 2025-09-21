<?php

namespace App\Enums;

enum Semester: string
{
    case FIRST = 'First';
    case SECOND = 'Second';
    case SUMMER = 'Summer';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::FIRST => 'First Semester',
            self::SECOND => 'Second Semester',
            self::SUMMER => 'Summer',
        };
    }
}

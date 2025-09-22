<?php

namespace App\Enums;

enum Quarter: string
{
    case FIRST = 'First';
    case SECOND = 'Second';
    case THIRD = 'Third';
    case FOURTH = 'Fourth';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::FIRST => '1st Quarter',
            self::SECOND => '2nd Quarter',
            self::THIRD => '3rd Quarter',
            self::FOURTH => '4th Quarter',
        };
    }
}

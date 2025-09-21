<?php

namespace App\Enums;

enum GradeLevel: string
{
    case KINDER = 'Kinder';
    case GRADE_1 = 'Grade 1';
    case GRADE_2 = 'Grade 2';
    case GRADE_3 = 'Grade 3';
    case GRADE_4 = 'Grade 4';
    case GRADE_5 = 'Grade 5';
    case GRADE_6 = 'Grade 6';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return $this->value;
    }

    /**
     * Check if grade level is elementary (all levels are elementary for this school)
     */
    public function isElementary(): bool
    {
        return true; // All grade levels in this school are elementary (K-6)
    }

    /**
     * Check if grade level is kindergarten
     */
    public function isKindergarten(): bool
    {
        return $this === self::KINDER;
    }

    /**
     * Get the numeric grade level (0 for Kinder, 1-6 for grades)
     */
    public function getNumericLevel(): int
    {
        return match ($this) {
            self::KINDER => 0,
            self::GRADE_1 => 1,
            self::GRADE_2 => 2,
            self::GRADE_3 => 3,
            self::GRADE_4 => 4,
            self::GRADE_5 => 5,
            self::GRADE_6 => 6,
        };
    }
}

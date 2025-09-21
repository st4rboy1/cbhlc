<?php

namespace App\Enums;

enum GradeLevel: string
{
    case NURSERY = 'Nursery';
    case KINDER_1 = 'Kinder 1';
    case KINDER_2 = 'Kinder 2';
    case GRADE_1 = 'Grade 1';
    case GRADE_2 = 'Grade 2';
    case GRADE_3 = 'Grade 3';
    case GRADE_4 = 'Grade 4';
    case GRADE_5 = 'Grade 5';
    case GRADE_6 = 'Grade 6';
    case GRADE_7 = 'Grade 7';
    case GRADE_8 = 'Grade 8';
    case GRADE_9 = 'Grade 9';
    case GRADE_10 = 'Grade 10';
    case GRADE_11 = 'Grade 11';
    case GRADE_12 = 'Grade 12';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return $this->value;
    }

    public function isElementary(): bool
    {
        return in_array($this, [
            self::NURSERY,
            self::KINDER_1,
            self::KINDER_2,
            self::GRADE_1,
            self::GRADE_2,
            self::GRADE_3,
            self::GRADE_4,
            self::GRADE_5,
            self::GRADE_6,
        ]);
    }

    public function isHighSchool(): bool
    {
        return in_array($this, [
            self::GRADE_7,
            self::GRADE_8,
            self::GRADE_9,
            self::GRADE_10,
            self::GRADE_11,
            self::GRADE_12,
        ]);
    }
}
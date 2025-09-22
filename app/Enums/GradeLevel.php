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
}

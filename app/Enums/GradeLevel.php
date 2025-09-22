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
     * Get the order/level number for grade progression logic
     */
    public function order(): int
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

    /**
     * Get the next grade level
     */
    public function nextGrade(): ?self
    {
        return match ($this) {
            self::KINDER => self::GRADE_1,
            self::GRADE_1 => self::GRADE_2,
            self::GRADE_2 => self::GRADE_3,
            self::GRADE_3 => self::GRADE_4,
            self::GRADE_4 => self::GRADE_5,
            self::GRADE_5 => self::GRADE_6,
            self::GRADE_6 => null, // No next grade after Grade 6
        };
    }

    /**
     * Check if this grade level is higher than another
     */
    public function isHigherThan(self $other): bool
    {
        return $this->order() > $other->order();
    }

    /**
     * Check if this grade level is lower than another
     */
    public function isLowerThan(self $other): bool
    {
        return $this->order() < $other->order();
    }

    /**
     * Get available grade levels for enrollment based on current grade
     */
    public static function getAvailableGradesFor(?self $currentGrade): array
    {
        if ($currentGrade === null) {
            // New student - can start at any grade
            return self::cases();
        }

        $grades = [];
        foreach (self::cases() as $grade) {
            // Can enroll in current grade or higher (no downgrades)
            if ($grade->order() >= $currentGrade->order()) {
                $grades[] = $grade;
            }
        }

        return $grades;
    }
}

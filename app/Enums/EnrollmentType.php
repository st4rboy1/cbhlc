<?php

namespace App\Enums;

enum EnrollmentType: string
{
    case NEW = 'new';
    case CONTINUING = 'continuing';
    case RETURNEE = 'returnee';
    case TRANSFEREE = 'transferee';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'New Student',
            self::CONTINUING => 'Continuing Student',
            self::RETURNEE => 'Returnee',
            self::TRANSFEREE => 'Transferee',
        };
    }

    /**
     * Get a description of the enrollment type
     */
    public function description(): string
    {
        return match ($this) {
            self::NEW => 'First time enrolling in the school',
            self::CONTINUING => 'Continuing from previous school year',
            self::RETURNEE => 'Returning after absence',
            self::TRANSFEREE => 'Transferring from another school',
        };
    }

    /**
     * Check if this enrollment type requires previous school information
     */
    public function requiresPreviousSchool(): bool
    {
        return $this === self::TRANSFEREE;
    }
}

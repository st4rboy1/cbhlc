<?php

namespace App\Enums;

enum EnrollmentPeriodStatus: string
{
    case UPCOMING = 'upcoming';
    case ACTIVE = 'active';
    case CLOSED = 'closed';

    /**
     * Get the display label for the status
     */
    public function label(): string
    {
        return match ($this) {
            self::UPCOMING => 'Upcoming',
            self::ACTIVE => 'Active',
            self::CLOSED => 'Closed',
        };
    }

    /**
     * Get the CSS color class for the status
     */
    public function color(): string
    {
        return match ($this) {
            self::UPCOMING => 'info',
            self::ACTIVE => 'success',
            self::CLOSED => 'muted',
        };
    }

    /**
     * Check if the status is active
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Check if the status is closed
     */
    public function isClosed(): bool
    {
        return $this === self::CLOSED;
    }

    /**
     * Check if the status is upcoming
     */
    public function isUpcoming(): bool
    {
        return $this === self::UPCOMING;
    }

    /**
     * Get all values as array for validation
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

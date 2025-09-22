<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case ENROLLED = 'enrolled';
    case COMPLETED = 'completed';

    /**
     * Get the display label for the status
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending Review',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::ENROLLED => 'Enrolled',
            self::COMPLETED => 'Completed',
        };
    }

    /**
     * Get the CSS color class for the status
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'destructive',
            self::ENROLLED => 'primary',
            self::COMPLETED => 'muted',
        };
    }

    /**
     * Check if the status allows modifications
     */
    public function isModifiable(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Check if the status is approved (either approved, enrolled, or completed)
     */
    public function isApproved(): bool
    {
        return in_array($this, [self::APPROVED, self::ENROLLED, self::COMPLETED]);
    }

    /**
     * Check if the enrollment is completed (student passed the year)
     */
    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Get all values as array for validation
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

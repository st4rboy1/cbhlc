<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case READY_FOR_PAYMENT = 'ready_for_payment';
    case PAID = 'paid';
    case ENROLLED = 'enrolled';
    case COMPLETED = 'completed';

    /**
     * Get the display label for the status
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending Review',
            self::APPROVED => 'Approved - Awaiting Invoice',
            self::REJECTED => 'Rejected',
            self::READY_FOR_PAYMENT => 'Ready for Payment',
            self::PAID => 'Paid - Awaiting Confirmation',
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
            self::APPROVED => 'info',
            self::REJECTED => 'destructive',
            self::READY_FOR_PAYMENT => 'warning',
            self::PAID => 'success',
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
     * Check if the status is approved (from approved onwards, except rejected)
     */
    public function isApproved(): bool
    {
        return in_array($this, [
            self::APPROVED,
            self::READY_FOR_PAYMENT,
            self::PAID,
            self::ENROLLED,
            self::COMPLETED
        ]);
    }

    /**
     * Check if the enrollment is completed (student passed the year)
     */
    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Check if the enrollment requires payment
     */
    public function requiresPayment(): bool
    {
        return $this === self::READY_FOR_PAYMENT;
    }

    /**
     * Check if the enrollment has been paid
     */
    public function isPaid(): bool
    {
        return in_array($this, [self::PAID, self::ENROLLED, self::COMPLETED]);
    }

    /**
     * Get the next status in the workflow
     */
    public function nextStatus(): ?self
    {
        return match ($this) {
            self::PENDING => self::APPROVED,
            self::APPROVED => self::READY_FOR_PAYMENT,
            self::READY_FOR_PAYMENT => self::PAID,
            self::PAID => self::ENROLLED,
            self::ENROLLED => self::COMPLETED,
            default => null,
        };
    }

    /**
     * Get all values as array for validation
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

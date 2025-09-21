<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\Quarter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'student_id',
        'user_id',
        'school_year',
        'semester',
        'status',
        'tuition_fee_cents',
        'miscellaneous_fee_cents',
        'laboratory_fee_cents',
        'library_fee_cents',
        'sports_fee_cents',
        'total_amount_cents',
        'discount_cents',
        'net_amount_cents',
        'payment_status',
        'amount_paid_cents',
        'balance_cents',
        'payment_due_date',
        'remarks',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'payment_due_date' => 'date',
        'quarter' => Quarter::class,
        'payment_status' => PaymentStatus::class,
    ];

    /**
     * Get the student associated with the enrollment
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user (parent) who created the enrollment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who approved the enrollment
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Money accessors (convert cents to dollars)
    public function getTuitionFeeAttribute(): float
    {
        return $this->tuition_fee_cents / 100;
    }

    public function getMiscellaneousFeeAttribute(): float
    {
        return $this->miscellaneous_fee_cents / 100;
    }

    public function getLaboratoryFeeAttribute(): float
    {
        return $this->laboratory_fee_cents / 100;
    }

    public function getLibraryFeeAttribute(): float
    {
        return $this->library_fee_cents / 100;
    }

    public function getSportsFeeAttribute(): float
    {
        return $this->sports_fee_cents / 100;
    }

    public function getTotalAmountAttribute(): float
    {
        return $this->total_amount_cents / 100;
    }

    public function getDiscountAttribute(): float
    {
        return $this->discount_cents / 100;
    }

    public function getNetAmountAttribute(): float
    {
        return $this->net_amount_cents / 100;
    }

    public function getAmountPaidAttribute(): float
    {
        return $this->amount_paid_cents / 100;
    }

    public function getBalanceAttribute(): float
    {
        return $this->balance_cents / 100;
    }

    // Money mutators (convert dollars to cents)
    public function setTuitionFeeAttribute(float $value): void
    {
        $this->tuition_fee_cents = (int) ($value * 100);
    }

    public function setMiscellaneousFeeAttribute(float $value): void
    {
        $this->miscellaneous_fee_cents = (int) ($value * 100);
    }

    public function setLaboratoryFeeAttribute(float $value): void
    {
        $this->laboratory_fee_cents = (int) ($value * 100);
    }

    public function setLibraryFeeAttribute(float $value): void
    {
        $this->library_fee_cents = (int) ($value * 100);
    }

    public function setSportsFeeAttribute(float $value): void
    {
        $this->sports_fee_cents = (int) ($value * 100);
    }

    public function setTotalAmountAttribute(float $value): void
    {
        $this->total_amount_cents = (int) ($value * 100);
    }

    public function setDiscountAttribute(float $value): void
    {
        $this->discount_cents = (int) ($value * 100);
    }

    public function setNetAmountAttribute(float $value): void
    {
        $this->net_amount_cents = (int) ($value * 100);
    }

    public function setAmountPaidAttribute(float $value): void
    {
        $this->amount_paid_cents = (int) ($value * 100);
    }

    public function setBalanceAttribute(float $value): void
    {
        $this->balance_cents = (int) ($value * 100);
    }

    /**
     * Calculate the total amount before discount
     */
    public function calculateTotalAmount(): float
    {
        return ($this->tuition_fee_cents + $this->miscellaneous_fee_cents +
                $this->laboratory_fee_cents + $this->library_fee_cents +
                $this->sports_fee_cents) / 100;
    }

    /**
     * Calculate the net amount after discount
     */
    public function calculateNetAmount(): float
    {
        return ($this->total_amount_cents - $this->discount_cents) / 100;
    }

    /**
     * Calculate the balance
     */
    public function calculateBalance(): float
    {
        return ($this->net_amount_cents - $this->amount_paid_cents) / 100;
    }

    /**
     * Check if enrollment is fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->payment_status === PaymentStatus::PAID || $this->balance_cents <= 0;
    }

    /**
     * Check if enrollment is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved' || $this->status === 'enrolled';
    }
}

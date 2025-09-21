<?php

namespace App\Models;

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
        'tuition_fee',
        'miscellaneous_fee',
        'laboratory_fee',
        'library_fee',
        'sports_fee',
        'total_amount',
        'discount',
        'net_amount',
        'payment_status',
        'amount_paid',
        'balance',
        'payment_due_date',
        'remarks',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'payment_due_date' => 'date',
        'tuition_fee' => 'decimal:2',
        'miscellaneous_fee' => 'decimal:2',
        'laboratory_fee' => 'decimal:2',
        'library_fee' => 'decimal:2',
        'sports_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
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

    /**
     * Calculate the total amount before discount
     */
    public function calculateTotalAmount(): float
    {
        return $this->tuition_fee
            + $this->miscellaneous_fee
            + $this->laboratory_fee
            + $this->library_fee
            + $this->sports_fee;
    }

    /**
     * Calculate the net amount after discount
     */
    public function calculateNetAmount(): float
    {
        return $this->calculateTotalAmount() - $this->discount;
    }

    /**
     * Calculate the balance
     */
    public function calculateBalance(): float
    {
        return $this->net_amount - $this->amount_paid;
    }

    /**
     * Check if enrollment is fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->payment_status === 'paid' || $this->balance <= 0;
    }

    /**
     * Check if enrollment is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved' || $this->status === 'enrolled';
    }
}

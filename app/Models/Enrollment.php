<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\Quarter;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property float $tuition_fee
 * @property float $miscellaneous_fee
 * @property float $laboratory_fee
 * @property float $library_fee
 * @property float $sports_fee
 * @property float $total_amount
 * @property float $discount
 * @property float $net_amount
 * @property float $amount_paid
 * @property float $balance
 */
class Enrollment extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($enrollment) {
            if (empty($enrollment->enrollment_id)) {
                $count = static::count() + 1;
                $enrollment->enrollment_id = 'ENR-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    protected $fillable = [
        'enrollment_id',
        'student_id',
        'guardian_id',
        'school_year',
        'quarter',
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
        'status' => EnrollmentStatus::class,
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
     * Get the guardian who created the enrollment
     */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guardian_id');
    }

    /**
     * Get the user who approved the enrollment
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Money attributes using Laravel 12 Attribute syntax
     */
    protected function tuitionFee(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->tuition_fee_cents / 100,
            set: fn (float $value) => ['tuition_fee_cents' => (int) ($value * 100)]
        );
    }

    protected function miscellaneousFee(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->miscellaneous_fee_cents / 100,
            set: fn (float $value) => ['miscellaneous_fee_cents' => (int) ($value * 100)]
        );
    }

    protected function laboratoryFee(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->laboratory_fee_cents / 100,
            set: fn (float $value) => ['laboratory_fee_cents' => (int) ($value * 100)]
        );
    }

    protected function libraryFee(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->library_fee_cents / 100,
            set: fn (float $value) => ['library_fee_cents' => (int) ($value * 100)]
        );
    }

    protected function sportsFee(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->sports_fee_cents / 100,
            set: fn (float $value) => ['sports_fee_cents' => (int) ($value * 100)]
        );
    }

    protected function totalAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->total_amount_cents / 100,
            set: fn (float $value) => ['total_amount_cents' => (int) ($value * 100)]
        );
    }

    protected function discount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->discount_cents / 100,
            set: fn (float $value) => ['discount_cents' => (int) ($value * 100)]
        );
    }

    protected function netAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->net_amount_cents / 100,
            set: fn (float $value) => ['net_amount_cents' => (int) ($value * 100)]
        );
    }

    protected function amountPaid(): Attribute
    {
        return Attribute::make(
            get: fn () => (float) ($this->amount_paid_cents / 100),
            set: fn (float $value) => ['amount_paid_cents' => (int) ($value * 100)]
        );
    }

    protected function balance(): Attribute
    {
        return Attribute::make(
            get: fn () => (float) ($this->balance_cents / 100),
            set: fn (float $value) => ['balance_cents' => (int) ($value * 100)]
        );
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
        return $this->status->isApproved();
    }
}

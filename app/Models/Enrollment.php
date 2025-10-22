<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\Quarter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

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
    use LogsActivity;

    protected $fillable = [
        'enrollment_id',
        'student_id',
        'guardian_id',
        'school_year',
        'enrollment_period_id',
        'quarter',
        'grade_level',
        'section',
        'adviser',
        'status',
        'type',
        'previous_school',
        'payment_plan',
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
        'rejected_at',
        'approved_by',
        'invoice_id',
        'payment_reference',
        'ready_for_payment_at',
        'paid_at',
        'info_requested',
        'info_request_message',
        'info_request_date',
        'info_requested_by',
        'info_response_message',
        'info_response_date',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'ready_for_payment_at' => 'datetime',
        'paid_at' => 'datetime',
        'payment_due_date' => 'date',
        'info_request_date' => 'datetime',
        'info_response_date' => 'datetime',
        'info_requested' => 'boolean',
        'quarter' => Quarter::class,
        'grade_level' => \App\Enums\GradeLevel::class,
        'status' => EnrollmentStatus::class,
        'payment_status' => PaymentStatus::class,
        // Money casts - convert cents to dollars
        'tuition_fee' => MoneyCast::class,
        'miscellaneous_fee' => MoneyCast::class,
        'laboratory_fee' => MoneyCast::class,
        'library_fee' => MoneyCast::class,
        'sports_fee' => MoneyCast::class,
        'total_amount' => MoneyCast::class,
        'discount' => MoneyCast::class,
        'net_amount' => MoneyCast::class,
        'amount_paid' => MoneyCast::class,
        'balance' => MoneyCast::class,
    ];

    /**
     * Get the activity log options for this model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => 'Enrollment application created',
                'updated' => 'Enrollment application updated',
                'deleted' => 'Enrollment application deleted',
                default => "Enrollment {$eventName}",
            });
    }

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
        return $this->belongsTo(Guardian::class);
    }

    /**
     * Get the user who approved the enrollment
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who requested more information
     */
    public function infoRequester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'info_requested_by');
    }

    /**
     * Get the enrollment period associated with the enrollment
     */
    public function enrollmentPeriod(): BelongsTo
    {
        return $this->belongsTo(EnrollmentPeriod::class);
    }

    /**
     * Get the invoice associated with the enrollment
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
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
     * Get the grade level fee
     */
    public function gradeLevelFee(): BelongsTo
    {
        return $this->belongsTo(GradeLevelFee::class, 'grade_level', 'grade_level');
    }

    /**
     * Get the invoices for the enrollment
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the payments for the enrollment through invoices
     */
    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Invoice::class);
    }

    /**
     * Check if enrollment is approved
     */
    public function isApproved(): bool
    {
        return $this->status->isApproved();
    }

    /**
     * Check if a student can enroll for a given period
     *
     * @return array<string> Array of error messages (empty if can enroll)
     */
    public static function canEnrollForPeriod(EnrollmentPeriod $period, Student $student): array
    {
        $errors = [];

        if (! $period->isOpen()) {
            $errors[] = 'Enrollment period is not currently open.';
        }

        $isNewStudent = $student->isNewStudent();

        if ($isNewStudent && ! $period->allow_new_students) {
            $errors[] = 'This enrollment period does not accept new students.';
        }

        if (! $isNewStudent && ! $period->allow_returning_students) {
            $errors[] = 'This enrollment period does not accept returning students.';
        }

        return $errors;
    }
}

<?php

namespace App\Models;

use App\Casts\FormattedMoneyCast;
use App\Casts\MoneyCast;
use App\Enums\GradeLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property float $tuition_fee
 * @property float $miscellaneous_fee
 * @property float $other_fees
 * @property float $total_amount
 */
class GradeLevelFee extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'grade_level',
        'enrollment_period_id',
        'tuition_fee',
        'tuition_fee_cents',
        'registration_fee',
        'registration_fee_cents',
        'miscellaneous_fee',
        'miscellaneous_fee_cents',
        'laboratory_fee',
        'laboratory_fee_cents',
        'library_fee',
        'library_fee_cents',
        'sports_fee',
        'sports_fee_cents',
        'other_fees',
        'other_fees_cents',
        'down_payment_cents',
        'payment_terms',
        'is_active',
    ];

    protected $appends = [
        'tuition_fee',
        'miscellaneous_fee',
        'other_fees',
        'down_payment',
        'total_amount',
    ];

    protected $casts = [
        'grade_level' => GradeLevel::class,
        'is_active' => 'boolean',
        // Money casts - convert cents to dollars
        'tuition_fee' => MoneyCast::class,
        'registration_fee' => MoneyCast::class,
        'miscellaneous_fee' => MoneyCast::class,
        'laboratory_fee' => MoneyCast::class,
        'library_fee' => MoneyCast::class,
        'sports_fee' => MoneyCast::class,
        'other_fees' => MoneyCast::class,
        'down_payment' => MoneyCast::class,
        // Formatted money casts - display formatted currency
        'formatted_tuition_fee' => FormattedMoneyCast::class,
        'formatted_registration_fee' => FormattedMoneyCast::class,
        'formatted_miscellaneous_fee' => FormattedMoneyCast::class,
        'formatted_laboratory_fee' => FormattedMoneyCast::class,
        'formatted_library_fee' => FormattedMoneyCast::class,
        'formatted_sports_fee' => FormattedMoneyCast::class,
        'formatted_other_fees' => FormattedMoneyCast::class,
        'formatted_total_fee' => FormattedMoneyCast::class,
    ];

    /**
     * Get the enrollment period that this fee belongs to.
     */
    public function enrollmentPeriod()
    {
        return $this->belongsTo(EnrollmentPeriod::class);
    }

    /**
     * Get the school year through the enrollment period.
     */
    public function schoolYear()
    {
        return $this->hasOneThrough(
            SchoolYear::class,
            EnrollmentPeriod::class,
            'id',
            'id',
            'enrollment_period_id',
            'school_year_id'
        );
    }

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
                'created' => 'Grade level fee created',
                'updated' => 'Grade level fee updated',
                'deleted' => 'Grade level fee deleted',
                default => "Grade level fee {$eventName}",
            });
    }

    /**
     * Get total fee in dollars (computed attribute)
     */
    public function getTotalFeeAttribute(): float
    {
        return $this->tuition_fee + $this->registration_fee +
               $this->miscellaneous_fee + $this->laboratory_fee +
               $this->library_fee + $this->sports_fee + $this->other_fees;
    }

    /**
     * Accessor for total_amount (alias for total_fee)
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->total_fee;
    }

    /**
     * Scope to get active fees
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include fees for the active enrollment period.
     */
    public function scopeCurrentEnrollmentPeriod($query)
    {
        $activeEnrollmentPeriod = EnrollmentPeriod::where('status', 'active')->first();

        if (! $activeEnrollmentPeriod) {
            return $query->whereRaw('1 = 0'); // Return empty result
        }

        return $query->where('enrollment_period_id', $activeEnrollmentPeriod->id);
    }

    /**
     * Scope a query to only include fees for the current school year (kept for backward compatibility).
     *
     * @deprecated Use scopeCurrentEnrollmentPeriod instead
     */
    public function scopeCurrentSchoolYear($query)
    {
        return $this->scopeCurrentEnrollmentPeriod($query);
    }

    /**
     * Get fees for a specific grade level and enrollment period
     */
    public static function getFeesForGrade(GradeLevel $gradeLevel, ?int $enrollmentPeriodId = null): ?self
    {
        if (! $enrollmentPeriodId) {
            $activeEnrollmentPeriod = EnrollmentPeriod::where('status', 'active')->first();
            if (! $activeEnrollmentPeriod) {
                return null;
            }
            $enrollmentPeriodId = $activeEnrollmentPeriod->id;
        }

        return self::where('grade_level', $gradeLevel)
            ->where('enrollment_period_id', $enrollmentPeriodId)
            ->where('is_active', true)
            ->first();
    }
}

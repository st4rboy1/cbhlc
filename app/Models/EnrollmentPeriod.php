<?php

namespace App\Models;

use App\Enums\EnrollmentPeriodStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class EnrollmentPeriod extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'school_year_id',
        'start_date',
        'end_date',
        'early_registration_deadline',
        'regular_registration_deadline',
        'late_registration_deadline',
        'status',
        'description',
        'allow_new_students',
        'allow_returning_students',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'early_registration_deadline' => 'date',
        'regular_registration_deadline' => 'date',
        'late_registration_deadline' => 'date',
        'status' => EnrollmentPeriodStatus::class,
        'allow_new_students' => 'boolean',
        'allow_returning_students' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($period) {
            // Validate date ranges
            if ($period->end_date <= $period->start_date) {
                throw new \InvalidArgumentException('End date must be after start date.');
            }

            if ($period->regular_registration_deadline < $period->start_date) {
                throw new \InvalidArgumentException('Registration deadline must be within period dates.');
            }

            // Only one active period at a time
            if ($period->status === EnrollmentPeriodStatus::ACTIVE) {
                static::where('status', EnrollmentPeriodStatus::ACTIVE)
                    ->where('id', '!=', $period->id)
                    ->update(['status' => EnrollmentPeriodStatus::CLOSED]);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', EnrollmentPeriodStatus::ACTIVE);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', EnrollmentPeriodStatus::UPCOMING);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', EnrollmentPeriodStatus::CLOSED);
    }

    public function isActive(): bool
    {
        return $this->status === EnrollmentPeriodStatus::ACTIVE;
    }

    public function isOpen(): bool
    {
        return $this->isActive() && now()->between($this->start_date, $this->end_date);
    }

    public function getDaysRemaining(): int
    {
        if (! $this->isActive()) {
            return 0;
        }

        return max(0, now()->diffInDays($this->regular_registration_deadline, false));
    }

    public function activate(): bool
    {
        if ($this->status === EnrollmentPeriodStatus::ACTIVE) {
            return true; // Already active
        }

        // Close all other active periods
        static::where('status', EnrollmentPeriodStatus::ACTIVE)
            ->where('id', '!=', $this->id)
            ->update(['status' => EnrollmentPeriodStatus::CLOSED]);

        return $this->update(['status' => EnrollmentPeriodStatus::ACTIVE]);
    }

    public function close(): bool
    {
        if ($this->status === EnrollmentPeriodStatus::CLOSED) {
            return true; // Already closed
        }

        return $this->update(['status' => EnrollmentPeriodStatus::CLOSED]);
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function gradeLevelFees(): HasMany
    {
        return $this->hasMany(GradeLevelFee::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['school_year_id', 'start_date', 'end_date', 'status', 'early_registration_deadline', 'regular_registration_deadline', 'late_registration_deadline', 'allow_new_students', 'allow_returning_students'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

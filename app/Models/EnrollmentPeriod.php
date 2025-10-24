<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnrollmentPeriod extends Model
{
    use HasFactory;

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
            if ($period->status === 'active') {
                static::where('status', 'active')
                    ->where('id', '!=', $period->id)
                    ->update(['status' => 'closed']);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
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

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the school year name (accessor for backward compatibility)
     */
    public function getSchoolYearAttribute(): ?string
    {
        // Use getRelationValue to avoid infinite recursion
        return $this->getRelationValue('schoolYear')?->name;
    }
}

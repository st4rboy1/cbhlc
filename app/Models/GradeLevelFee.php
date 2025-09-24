<?php

namespace App\Models;

use App\Casts\FormattedMoneyCast;
use App\Casts\MoneyCast;
use App\Enums\GradeLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeLevelFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_level',
        'tuition_fee_cents',
        'registration_fee_cents',
        'miscellaneous_fee_cents',
        'laboratory_fee_cents',
        'library_fee_cents',
        'sports_fee_cents',
        'school_year',
        'is_active',
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
        // Formatted money casts - display formatted currency
        'formatted_tuition_fee' => FormattedMoneyCast::class,
        'formatted_registration_fee' => FormattedMoneyCast::class,
        'formatted_miscellaneous_fee' => FormattedMoneyCast::class,
        'formatted_laboratory_fee' => FormattedMoneyCast::class,
        'formatted_library_fee' => FormattedMoneyCast::class,
        'formatted_sports_fee' => FormattedMoneyCast::class,
        'formatted_total_fee' => FormattedMoneyCast::class,
    ];


    /**
     * Get total fee in dollars (computed attribute)
     */
    public function getTotalFeeAttribute(): float
    {
        return $this->tuition_fee + $this->registration_fee +
               $this->miscellaneous_fee + $this->laboratory_fee +
               $this->library_fee + $this->sports_fee;
    }


    /**
     * Scope to get active fees
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get fees for current school year
     */
    public function scopeCurrentSchoolYear($query)
    {
        $currentYear = date('Y');
        $nextYear = $currentYear + 1;
        $schoolYear = "{$currentYear}-{$nextYear}";

        return $query->where('school_year', $schoolYear);
    }

    /**
     * Get fees for a specific grade level and school year
     */
    public static function getFeesForGrade(GradeLevel $gradeLevel, ?string $schoolYear = null): ?self
    {
        if (! $schoolYear) {
            $currentYear = date('Y');
            $nextYear = $currentYear + 1;
            $schoolYear = "{$currentYear}-{$nextYear}";
        }

        return self::where('grade_level', $gradeLevel)
            ->where('school_year', $schoolYear)
            ->where('is_active', true)
            ->first();
    }
}

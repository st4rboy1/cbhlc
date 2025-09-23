<?php

namespace App\Models;

use App\Enums\GradeLevel;
use App\Services\CurrencyService;
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
    ];

    /**
     * Get tuition fee in dollars
     */
    public function getTuitionFeeAttribute(): float
    {
        return $this->tuition_fee_cents / 100;
    }

    /**
     * Set tuition fee from dollars
     */
    public function setTuitionFeeAttribute(float $value): void
    {
        $this->tuition_fee_cents = (int) ($value * 100);
    }

    /**
     * Get registration fee in dollars
     */
    public function getRegistrationFeeAttribute(): float
    {
        return $this->registration_fee_cents / 100;
    }

    /**
     * Set registration fee from dollars
     */
    public function setRegistrationFeeAttribute(float $value): void
    {
        $this->registration_fee_cents = (int) ($value * 100);
    }

    /**
     * Get miscellaneous fee in dollars
     */
    public function getMiscellaneousFeeAttribute(): float
    {
        return $this->miscellaneous_fee_cents / 100;
    }

    /**
     * Set miscellaneous fee from dollars
     */
    public function setMiscellaneousFeeAttribute(float $value): void
    {
        $this->miscellaneous_fee_cents = (int) ($value * 100);
    }

    /**
     * Get laboratory fee in dollars
     */
    public function getLaboratoryFeeAttribute(): float
    {
        return $this->laboratory_fee_cents / 100;
    }

    /**
     * Set laboratory fee from dollars
     */
    public function setLaboratoryFeeAttribute(float $value): void
    {
        $this->laboratory_fee_cents = (int) ($value * 100);
    }

    /**
     * Get library fee in dollars
     */
    public function getLibraryFeeAttribute(): float
    {
        return $this->library_fee_cents / 100;
    }

    /**
     * Set library fee from dollars
     */
    public function setLibraryFeeAttribute(float $value): void
    {
        $this->library_fee_cents = (int) ($value * 100);
    }

    /**
     * Get sports fee in dollars
     */
    public function getSportsFeeAttribute(): float
    {
        return $this->sports_fee_cents / 100;
    }

    /**
     * Set sports fee from dollars
     */
    public function setSportsFeeAttribute(float $value): void
    {
        $this->sports_fee_cents = (int) ($value * 100);
    }

    /**
     * Get total fee in dollars
     */
    public function getTotalFeeAttribute(): float
    {
        return ($this->tuition_fee_cents + $this->registration_fee_cents +
                $this->miscellaneous_fee_cents + $this->laboratory_fee_cents +
                $this->library_fee_cents + $this->sports_fee_cents) / 100;
    }

    /**
     * Get formatted tuition fee
     */
    public function getFormattedTuitionFeeAttribute(): string
    {
        return CurrencyService::formatCents($this->tuition_fee_cents);
    }

    /**
     * Get formatted registration fee
     */
    public function getFormattedRegistrationFeeAttribute(): string
    {
        return CurrencyService::formatCents($this->registration_fee_cents);
    }

    /**
     * Get formatted miscellaneous fee
     */
    public function getFormattedMiscellaneousFeeAttribute(): string
    {
        return CurrencyService::formatCents($this->miscellaneous_fee_cents);
    }

    /**
     * Get formatted laboratory fee
     */
    public function getFormattedLaboratoryFeeAttribute(): string
    {
        return CurrencyService::formatCents($this->laboratory_fee_cents);
    }

    /**
     * Get formatted library fee
     */
    public function getFormattedLibraryFeeAttribute(): string
    {
        return CurrencyService::formatCents($this->library_fee_cents);
    }

    /**
     * Get formatted sports fee
     */
    public function getFormattedSportsFeeAttribute(): string
    {
        return CurrencyService::formatCents($this->sports_fee_cents);
    }

    /**
     * Get formatted total fee
     */
    public function getFormattedTotalFeeAttribute(): string
    {
        $totalCents = $this->tuition_fee_cents + $this->registration_fee_cents +
                     $this->miscellaneous_fee_cents + $this->laboratory_fee_cents +
                     $this->library_fee_cents + $this->sports_fee_cents;

        return CurrencyService::formatCents($totalCents);
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

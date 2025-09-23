<?php

namespace App\Models;

use App\Enums\GradeLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'first_name',
        'last_name',
        'middle_name',
        'birthdate',
        'gender',
        'address',
        'contact_number',
        'email',
        'grade_level',
        'section',
        'user_id',
        'guardian_id',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'grade_level' => GradeLevel::class,
    ];

    /**
     * Calculate age from birthdate
     */
    public function getAgeAttribute(): int
    {
        return $this->birthdate->age;
    }

    /**
     * Get the user associated with the student (if any)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the guardian associated with the student
     */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guardian_id');
    }

    /**
     * Get the enrollments for the student
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the guardians associated with this student
     */
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'guardian_students', 'student_id', 'guardian_id')
            ->withPivot(['relationship_type', 'is_primary_contact'])
            ->withTimestamps();
    }

    /**
     * Get the guardian student pivot records
     */
    public function guardianStudents(): HasMany
    {
        return $this->hasMany(GuardianStudent::class);
    }

    /**
     * Generate a unique student ID
     */
    public static function generateStudentId(): string
    {
        do {
            $year = date('Y');
            $number = str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $studentId = $year.'-'.$number;
        } while (self::where('student_id', $studentId)->exists());

        return $studentId;
    }

    /**
     * Check if this is a new student (no previous enrollments)
     */
    public function isNewStudent(): bool
    {
        return $this->enrollments()->count() === 0;
    }

    /**
     * Get the current grade level based on latest approved enrollment
     */
    public function getCurrentGradeLevel(): ?GradeLevel
    {
        $latestEnrollment = $this->enrollments()
            ->where('status', \App\Enums\EnrollmentStatus::APPROVED)
            ->latest('created_at')
            ->first();

        if (! $latestEnrollment) {
            return $this->grade_level; // Use student's base grade level if no enrollments
        }

        // Get the grade level from the enrollment, not the student record
        // The student record grade_level might be outdated
        return $latestEnrollment->grade_level ?? $this->grade_level;
    }

    /**
     * Check if student passed the previous school year
     */
    public function passedPreviousYear(string $schoolYear): bool
    {
        // Get previous school year
        $currentYearStart = (int) substr($schoolYear, 0, 4);
        $previousYear = ($currentYearStart - 1).'-'.$currentYearStart;

        $previousEnrollment = $this->enrollments()
            ->where('school_year', $previousYear)
            ->where('status', \App\Enums\EnrollmentStatus::APPROVED)
            ->first();

        if (! $previousEnrollment) {
            // No previous enrollment means new student or first year
            return true;
        }

        // Check if they completed the year
        // COMPLETED status explicitly means they passed
        // APPROVED/ENROLLED are also considered as passing for backward compatibility
        return in_array($previousEnrollment->status, [
            \App\Enums\EnrollmentStatus::APPROVED,
            \App\Enums\EnrollmentStatus::ENROLLED,
            \App\Enums\EnrollmentStatus::COMPLETED,
        ]);
    }

    /**
     * Get available grade levels for enrollment
     */
    public function getAvailableGradeLevels(string $schoolYear): array
    {
        $currentGrade = $this->getCurrentGradeLevel();

        if ($this->isNewStudent()) {
            // New students can start at any grade
            return GradeLevel::getAvailableGradesFor(null);
        }

        // Existing students can only progress if they passed previous year
        if (! $this->passedPreviousYear($schoolYear)) {
            // Repeat current grade if didn't pass
            return $currentGrade ? [$currentGrade] : [];
        }

        // Can progress to current grade or higher
        return GradeLevel::getAvailableGradesFor($currentGrade);
    }
}

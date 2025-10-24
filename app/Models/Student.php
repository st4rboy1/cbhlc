<?php

namespace App\Models;

use App\Casts\FullNameCast;  // Already present
use App\Enums\EnrollmentStatus;  // Already present
use App\Enums\GradeLevel;  // Added for use in methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property string $full_name
 * @property ?\Illuminate\Support\Carbon $birth_date
 */
class Student extends Model
{
    use HasFactory;
    use LogsActivity;

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
        'birth_place',
        'nationality',
        'religion',
        'user_id',
        'guardian_id',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'grade_level' => GradeLevel::class,
        'full_name' => FullNameCast::class,
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
                'created' => 'Student record created',
                'updated' => 'Student record updated',
                'deleted' => 'Student record deleted',
                default => "Student {$eventName}",
            });
    }

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
        return $this->belongsTo(Guardian::class);
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
        return $this->belongsToMany(Guardian::class, 'guardian_students', 'student_id', 'guardian_id')
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
     * Get the documents for the student
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
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
            ->where('status', EnrollmentStatus::APPROVED)
            ->latest('created_at')
            ->first();

        if (! $latestEnrollment) {
            return $this->grade_level; // Use student's base grade level if no enrollments
        }

        return $latestEnrollment->grade_level ?? $this->grade_level;
    }

    /**
     * Check if student passed the previous school year
     */
    public function passedPreviousYear(int $schoolYearId): bool
    {
        $currentSchoolYear = SchoolYear::find($schoolYearId);
        if (! $currentSchoolYear) {
            return true;
        }

        // Find previous school year
        $previousSchoolYear = SchoolYear::where('start_year', $currentSchoolYear->start_year - 1)->first();
        if (! $previousSchoolYear) {
            return true;
        }

        $previousEnrollment = $this->enrollments()
            ->where('school_year_id', $previousSchoolYear->id)
            ->where('status', EnrollmentStatus::APPROVED)
            ->first();

        if (! $previousEnrollment) {
            return true;
        }

        return in_array($previousEnrollment->status, [
            EnrollmentStatus::APPROVED,
            EnrollmentStatus::ENROLLED,
            EnrollmentStatus::COMPLETED,
        ]);
    }

    /**
     * Get available grade levels for enrollment
     */
    public function getAvailableGradeLevels(string $schoolYear): array
    {
        $currentGrade = $this->getCurrentGradeLevel();

        if ($this->isNewStudent()) {
            return GradeLevel::getAvailableGradesFor(null);
        }

        if (! $this->passedPreviousYear($schoolYear)) {
            return $currentGrade ? [$currentGrade] : [];
        }

        return GradeLevel::getAvailableGradesFor($currentGrade);
    }
}

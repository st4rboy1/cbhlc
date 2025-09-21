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
        'phone',
        'grade_level',
        'user_id',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'grade_level' => GradeLevel::class,
    ];

    /**
     * Get the full name of the student
     */
    public function getFullNameAttribute(): string
    {
        $middle = $this->middle_name ? " {$this->middle_name}" : '';

        return "{$this->first_name}{$middle} {$this->last_name}";
    }

    /**
     * Get the user associated with the student (if any)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the enrollments for the student
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the current enrollment
     */
    public function currentEnrollment()
    {
        return $this->enrollments()->where('status', 'enrolled')->latest()->first();
    }

    /**
     * Get the parents associated with this student
     */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_students', 'student_id', 'parent_id')
            ->withPivot(['relationship_type', 'is_primary_contact'])
            ->withTimestamps();
    }

    /**
     * Get the primary parent contact
     */
    public function primaryParent()
    {
        return $this->parents()->wherePivot('is_primary_contact', true)->first() ??
               $this->parents()->first();
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
}

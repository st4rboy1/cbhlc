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
        'grade_level',
        'user_id',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'grade_level' => GradeLevel::class,
    ];

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
     * Get the guardians associated with this student
     */
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'guardian_students', 'student_id', 'guardian_id')
            ->withPivot(['relationship_type', 'is_primary_contact'])
            ->withTimestamps();
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

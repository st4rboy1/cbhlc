<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'age',
        'address',
        'contact_number',
        'email',
        'guardian_name',
        'guardian_contact',
        'guardian_email',
        'grade_level',
        'section',
        'user_id',
    ];

    protected $casts = [
        'birthdate' => 'date',
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
}

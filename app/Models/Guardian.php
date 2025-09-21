<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Guardian extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'phone',
        'address',
        'occupation',
        'employer',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
    ];

    /**
     * Get the user account associated with this parent
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the students (children) associated with this parent
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'guardian_students', 'guardian_id', 'student_id')
            ->withPivot(['relationship_type', 'is_primary_contact'])
            ->withTimestamps();
    }

    /**
     * Get the full name of the guardian
     */
    public function getFullNameAttribute(): string
    {
        $middle = $this->middle_name ? " {$this->middle_name}" : '';

        return "{$this->first_name}{$middle} {$this->last_name}";
    }

    /**
     * Get enrollments for this guardian's children
     */
    public function childrenEnrollments()
    {
        return Enrollment::whereIn('student_id', $this->children()->pluck('students.id'));
    }

    /**
     * Check if this guardian is the primary contact for any child
     */
    public function isPrimaryContactForAnyChild(): bool
    {
        return $this->children()->wherePivot('is_primary_contact', true)->exists();
    }
}
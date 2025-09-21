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
     * Get the students (children) associated with this guardian
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'guardian_students', 'guardian_id', 'student_id')
            ->withPivot(['relationship_type', 'is_primary_contact'])
            ->withTimestamps();
    }
}

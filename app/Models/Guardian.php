<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * Note: guardian_students table uses user_id (not guardian.id) as guardian_id
     */
    public function children()
    {
        // We use the user's ID since guardian_students.guardian_id references users.id
        return Student::join('guardian_students', 'students.id', '=', 'guardian_students.student_id')
            ->where('guardian_students.guardian_id', $this->user_id)
            ->select('students.*', 'guardian_students.relationship_type', 'guardian_students.is_primary_contact');
    }
}

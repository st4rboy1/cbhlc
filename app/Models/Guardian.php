<?php

namespace App\Models;

use App\Casts\FullNameCast;
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
        'contact_number',
        'address',
        'occupation',
        'employer',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
    ];

    protected $casts = [
        'full_name' => FullNameCast::class,
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
    public function children(): BelongsToMany
    {
        // We use the user's ID since guardian_students.guardian_id references users.id
        return $this->belongsToMany(Student::class, 'guardian_students', 'guardian_id', 'student_id', 'user_id', 'id')
            ->withPivot('relationship_type', 'is_primary_contact')
            ->withTimestamps();
    }
}

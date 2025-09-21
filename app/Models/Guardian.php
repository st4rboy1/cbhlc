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
        // Note: guardian_students table uses user_id as guardian_id, not guardian model id
        // This method delegates to the User model's children() relationship
        return $this->user->children();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuardianStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'guardian_id',
        'student_id',
        'relationship_type',
        'is_primary_contact',
    ];

    protected $casts = [
        'is_primary_contact' => 'boolean',
    ];

    /**
     * Get the guardian (user) associated with this relationship
     * Note: guardian_id references users.id, not guardians.id
     */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'guardian_id', 'user_id');
    }

    /**
     * Get the student associated with this relationship
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}

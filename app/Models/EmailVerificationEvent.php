<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailVerificationEvent extends Model
{
    protected $fillable = [
        'user_id',
        'verified_at',
        'ip_address',
        'user_agent',
        'time_to_verify_minutes',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    /**
     * Get the user that owns the verification event.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

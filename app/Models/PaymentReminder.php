<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReminder extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'enrollment_id',
        'reminder_type',
        'sent_at',
        'email_opened_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'email_opened_at' => 'datetime',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }
}

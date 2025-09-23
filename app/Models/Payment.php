<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_method',
        'payment_date',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_method' => PaymentMethod::class,
        'payment_date' => 'date',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the enrollment through the invoice
     */
    public function enrollment(): ?Enrollment
    {
        return $this->invoice?->enrollment;
    }
}

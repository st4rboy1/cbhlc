<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'enrollment_id',
        'total_amount',
        'paid_amount',
        'status',
        'due_date',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'status' => InvoiceStatus::class,
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    protected $attributes = [
        'paid_amount' => 0,
        'status' => InvoiceStatus::DRAFT,
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function getRemainingBalanceAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== InvoiceStatus::PAID
            && $this->due_date
            && $this->due_date->isPast();
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->status === InvoiceStatus::PAID;
    }

    public function getIsPartiallyPaidAttribute(): bool
    {
        return $this->status === InvoiceStatus::PARTIALLY_PAID;
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => InvoiceStatus::PAID,
            'paid_at' => now(),
        ]);
    }

    public function markAsPartiallyPaid(): void
    {
        $this->update([
            'status' => InvoiceStatus::PARTIALLY_PAID,
        ]);
    }

    public function updatePaidAmount(): void
    {
        $totalPaid = $this->payments()->sum('amount');
        $this->paid_amount = $totalPaid;

        if ($totalPaid >= $this->total_amount) {
            $this->markAsPaid();
        } elseif ($totalPaid > 0) {
            $this->markAsPartiallyPaid();
        }

        $this->save();
    }
}

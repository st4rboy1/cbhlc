<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Receipt extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'receipt_number',
        'payment_id',
        'invoice_id',
        'receipt_date',
        'amount',
        'payment_method',
        'received_by',
        'notes',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the activity log options for this model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => 'Receipt generated',
                'updated' => 'Receipt updated',
                'deleted' => 'Receipt deleted',
                default => "Receipt {$eventName}",
            });
    }

    /**
     * Get the payment associated with this receipt.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the invoice associated with this receipt.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the user who received the payment.
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Generate a unique receipt number.
     * Format: OR-YYYY-NNNN (e.g., OR-2025-0001)
     */
    public static function generateReceiptNumber(): string
    {
        $year = now()->year;
        $prefix = "OR-{$year}-";

        // Get the latest receipt number for this year
        $lastReceipt = static::where('receipt_number', 'like', "{$prefix}%")
            ->orderByDesc('receipt_number')
            ->first();

        if (! $lastReceipt) {
            $sequenceNumber = 1;
        } else {
            // Extract the sequence number from the last receipt
            $lastSequence = (int) substr($lastReceipt->receipt_number, -4);
            $sequenceNumber = $lastSequence + 1;
        }

        return $prefix.str_pad((string) $sequenceNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get formatted receipt number for display.
     */
    public function getFormattedReceiptNumberAttribute(): string
    {
        return $this->receipt_number;
    }

    /**
     * Get formatted amount for display.
     */
    public function getFormattedAmountAttribute(): string
    {
        return '₱'.number_format((float) $this->amount, 2);
    }
}

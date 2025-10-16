<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'notification_type',
        'email_enabled',
        'database_enabled',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'database_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Available notification types with their labels.
     */
    public static function availableTypes(): array
    {
        return [
            'enrollment_approved' => 'Enrollment Application Approved',
            'enrollment_rejected' => 'Enrollment Application Rejected',
            'enrollment_pending' => 'Enrollment Application Pending Review',
            'enrollment_period_changed' => 'Enrollment Period Status Changed',
            'document_verified' => 'Document Verified',
            'document_rejected' => 'Document Rejected',
            'payment_due' => 'Payment Due Reminder',
            'payment_received' => 'Payment Received Confirmation',
            'payment_overdue' => 'Payment Overdue Notice',
            'announcement_published' => 'New Announcement',
            'inquiry_response' => 'Inquiry Response Received',
        ];
    }
}

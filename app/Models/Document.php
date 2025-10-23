<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Document extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'enrollment_id',
        'document_type',
        'original_filename',
        'stored_filename',
        'file_path',
        'file_size',
        'mime_type',
        'upload_date',
        'verification_status',
        'verified_by',
        'verified_at',
        'rejection_reason',
    ];

    protected $casts = [
        'document_type' => DocumentType::class,
        'verification_status' => VerificationStatus::class,
        'upload_date' => 'datetime',
        'verified_at' => 'datetime',
        'file_size' => 'integer',
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
                'created' => 'Document uploaded',
                'updated' => 'Document updated',
                'deleted' => 'Document deleted',
                default => "Document {$eventName}",
            });
    }

    /**
     * Get the student that owns the document.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who verified the document.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the enrollment that owns the document.
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Get the URL for the document.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk('private')->url($this->file_path);
    }

    /**
     * Get the file size in a human-readable format.
     */
    public function getHumanFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    /**
     * Check if document is verified.
     */
    public function isVerified(): bool
    {
        return $this->verification_status === VerificationStatus::VERIFIED;
    }

    /**
     * Check if document is pending.
     */
    public function isPending(): bool
    {
        return $this->verification_status === VerificationStatus::PENDING;
    }

    /**
     * Check if document is rejected.
     */
    public function isRejected(): bool
    {
        return $this->verification_status === VerificationStatus::REJECTED;
    }

    /**
     * Verify the document.
     */
    public function verify(User $user): void
    {
        $this->update([
            'verification_status' => VerificationStatus::VERIFIED,
            'verified_by' => $user->id,
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Reject the document.
     */
    public function reject(User $user, string $reason): void
    {
        $this->update([
            'verification_status' => VerificationStatus::REJECTED,
            'verified_by' => $user->id,
            'verified_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Delete the document file from storage.
     */
    protected static function booted(): void
    {
        static::deleted(function (Document $document) {
            try {
                if ($document->file_path && Storage::disk('private')->exists($document->file_path)) {
                    Storage::disk('private')->delete($document->file_path);
                }
            } catch (\Exception $e) {
                // Log the error but don't fail the deletion
                \Log::warning('Failed to delete document file', [
                    'document_id' => $document->id,
                    'file_path' => $document->file_path,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }
}

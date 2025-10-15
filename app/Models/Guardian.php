<?php

namespace App\Models;

use App\Casts\FullNameCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Guardian extends Model
{
    use HasFactory;
    use LogsActivity;

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
     * Get the activity log options for this model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => 'Guardian record created',
                'updated' => 'Guardian record updated',
                'deleted' => 'Guardian record deleted',
                default => "Guardian {$eventName}",
            });
    }

    /**
     * Get the user account associated with this parent
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the students (children) associated with this guardian
     * Note: guardian_students table uses guardian.id as guardian_id
     */
    public function children(): BelongsToMany
    {
        // guardian_students.guardian_id now references guardians.id after migration
        return $this->belongsToMany(Student::class, 'guardian_students', 'guardian_id', 'student_id')
            ->withPivot('relationship_type', 'is_primary_contact')
            ->withTimestamps();
    }
}

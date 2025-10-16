<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasRoles;
    use LogsActivity;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the activity log options for this model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => 'User account created',
                'updated' => 'User account updated',
                'deleted' => 'User account deleted',
                default => "User {$eventName}",
            });
    }

    /**
     * Get the guardian profile associated with this user
     */
    public function guardian(): HasOne
    {
        return $this->hasOne(Guardian::class);
    }

    /**
     * Get the student profile associated with this user
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Get the dashboard route based on user role
     */
    public function getDashboardRoute(): string
    {
        if ($this->hasRole('super_admin')) {
            return 'super-admin.dashboard';
        } elseif ($this->hasRole('administrator')) {
            return 'admin.dashboard';
        } elseif ($this->hasRole('registrar')) {
            return 'registrar.dashboard';
        } elseif ($this->hasRole('guardian')) {
            return 'guardian.dashboard';
        } elseif ($this->hasRole('student')) {
            return 'student.dashboard';
        }

        // Default to home page if user has no role (shouldn't happen in production)
        return 'home';
    }

    /**
     * Get the notification preferences for this user
     */
    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    /**
     * Get a specific notification preference
     */
    public function getNotificationPreference(string $type): ?NotificationPreference
    {
        /** @var NotificationPreference|null $preference */
        $preference = $this->notificationPreferences()
            ->where('notification_type', $type)
            ->first();

        return $preference;
    }

    /**
     * Check if user should receive a notification on a specific channel
     */
    public function shouldReceiveNotification(string $type, string $channel = 'mail'): bool
    {
        $preference = $this->getNotificationPreference($type);

        if (! $preference) {
            // Default to enabled if no preference set
            return true;
        }

        return match ($channel) {
            'mail' => $preference->email_enabled,
            'database' => $preference->database_enabled,
            default => true,
        };
    }

    /**
     * Set notification preference for a specific type
     */
    public function setNotificationPreference(string $type, bool $emailEnabled, bool $databaseEnabled): void
    {
        $this->notificationPreferences()->updateOrCreate(
            ['notification_type' => $type],
            [
                'email_enabled' => $emailEnabled,
                'database_enabled' => $databaseEnabled,
            ]
        );
    }

    /**
     * Create default notification preferences for the user
     */
    public function createDefaultNotificationPreferences(): void
    {
        foreach (NotificationPreference::availableTypes() as $type => $label) {
            $this->notificationPreferences()->create([
                'notification_type' => $type,
                'email_enabled' => true,
                'database_enabled' => true,
            ]);
        }
    }

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();

        static::created(function ($user) {
            $user->createDefaultNotificationPreferences();
        });
    }
}

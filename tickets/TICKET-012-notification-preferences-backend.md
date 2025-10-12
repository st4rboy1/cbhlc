# Ticket #012: Notification Preferences Backend

**Epic:** [EPIC-006 Notification System Enhancement](./EPIC-006-notification-system-enhancement.md)

**Type:** Story
**Priority:** High
**Estimated Effort:** 1 day
**Assignee:** TBD

## Description

Implement notification preferences system allowing users to control which notifications they receive and through which channels (email, database).

## Acceptance Criteria

- [ ] NotificationPreference model and migration created
- [ ] Users can get/set notification preferences
- [ ] Default preferences applied to new users
- [ ] Notification classes check preferences before sending
- [ ] Backend API for preferences management
- [ ] Preferences validation and business logic

## Implementation Details

### Migration

`database/migrations/create_notification_preferences_table.php`

```php
Schema::create('notification_preferences', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('notification_type'); // e.g., 'enrollment_approved'
    $table->boolean('email_enabled')->default(true);
    $table->boolean('database_enabled')->default(true);
    $table->timestamps();

    $table->unique(['user_id', 'notification_type']);
    $table->index('user_id');
});
```

### Model

`app/Models/NotificationPreference.php`

```php
class NotificationPreference extends Model
{
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Available notification types
    public static function availableTypes(): array
    {
        return [
            'enrollment_approved' => 'Enrollment Application Approved',
            'enrollment_rejected' => 'Enrollment Application Rejected',
            'enrollment_pending' => 'Enrollment Application Pending Review',
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
```

### Update User Model

```php
class User extends Authenticatable
{
    // ... existing code

    public function notificationPreferences()
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function getNotificationPreference(string $type): ?NotificationPreference
    {
        return $this->notificationPreferences()
            ->where('notification_type', $type)
            ->first();
    }

    public function shouldReceiveNotification(string $type, string $channel = 'mail'): bool
    {
        $preference = $this->getNotificationPreference($type);

        if (!$preference) {
            // Default to enabled if no preference set
            return true;
        }

        return match($channel) {
            'mail' => $preference->email_enabled,
            'database' => $preference->database_enabled,
            default => true,
        };
    }

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

    // Create default preferences for new users
    public static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            $user->createDefaultNotificationPreferences();
        });
    }

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
}
```

### Update Notification Classes

Modify existing notification classes to check preferences:

```php
class EnrollmentApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Enrollment $enrollment
    ) {}

    public function via($notifiable): array
    {
        $channels = [];

        if ($notifiable->shouldReceiveNotification('enrollment_approved', 'mail')) {
            $channels[] = 'mail';
        }

        if ($notifiable->shouldReceiveNotification('enrollment_approved', 'database')) {
            $channels[] = 'database';
        }

        return $channels;
    }

    // ... rest of notification class
}
```

### Controller

`app/Http/Controllers/Settings/NotificationPreferenceController.php`

```php
class NotificationPreferenceController extends Controller
{
    public function index()
    {
        $preferences = auth()->user()->notificationPreferences()
            ->get()
            ->keyBy('notification_type');

        $availableTypes = NotificationPreference::availableTypes();

        return Inertia::render('Settings/Notifications', [
            'preferences' => $preferences,
            'availableTypes' => $availableTypes,
        ]);
    }

    public function update(UpdateNotificationPreferencesRequest $request)
    {
        $user = auth()->user();

        foreach ($request->preferences as $type => $settings) {
            $user->setNotificationPreference(
                $type,
                $settings['email_enabled'] ?? false,
                $settings['database_enabled'] ?? false
            );
        }

        activity()
            ->causedBy($user)
            ->log('Notification preferences updated');

        return back()->with('success', 'Notification preferences updated successfully.');
    }

    public function reset()
    {
        $user = auth()->user();

        // Delete all preferences (will use defaults)
        $user->notificationPreferences()->delete();

        // Recreate defaults
        $user->createDefaultNotificationPreferences();

        return back()->with('success', 'Notification preferences reset to default.');
    }
}
```

### Routes

```php
Route::middleware('auth')->prefix('settings/notifications')->name('settings.notifications.')->group(function () {
    Route::get('/', [NotificationPreferenceController::class, 'index'])->name('index');
    Route::put('/', [NotificationPreferenceController::class, 'update'])->name('update');
    Route::post('/reset', [NotificationPreferenceController::class, 'reset'])->name('reset');
});
```

### Validation Request

```php
class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function rules()
    {
        $availableTypes = array_keys(NotificationPreference::availableTypes());

        return [
            'preferences' => 'required|array',
            'preferences.*' => 'array',
            'preferences.*.email_enabled' => 'boolean',
            'preferences.*.database_enabled' => 'boolean',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $availableTypes = array_keys(NotificationPreference::availableTypes());

            foreach (array_keys($this->preferences ?? []) as $type) {
                if (!in_array($type, $availableTypes)) {
                    $validator->errors()->add('preferences', "Invalid notification type: {$type}");
                }
            }
        });
    }
}
```

## Testing Requirements

- [ ] Unit test: shouldReceiveNotification() method
- [ ] Unit test: setNotificationPreference() method
- [ ] Feature test: get preferences
- [ ] Feature test: update preferences
- [ ] Feature test: reset preferences
- [ ] Feature test: default preferences created for new users
- [ ] Feature test: notification respects preferences
- [ ] Integration test: preferences affect email sending

## Dependencies

- Laravel Notifications system
- Activity log package

## Notes

- Consider adding SMS channel in future
- Add preference for notification frequency (immediate, daily digest, weekly)
- Consider global "mute all" toggle
- Log preference changes for audit trail

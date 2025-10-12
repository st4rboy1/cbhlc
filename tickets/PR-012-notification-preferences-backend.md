# PR #012: Notification Preferences Backend

## Related Ticket

[TICKET-012: Notification Preferences Backend](./TICKET-012-notification-preferences-backend.md)

## Epic

[EPIC-006: Notification System Enhancement](./EPIC-006-notification-system-enhancement.md)

## Description

This PR implements a comprehensive notification preferences system allowing users to control which notifications they receive through email and database channels, with default preferences for new users.

## Changes Made

### Database

- ✅ Created `create_notification_preferences_table.php` migration
- ✅ Unique constraint on `user_id` + `notification_type`
- ✅ Indexes for performance

### Models

- ✅ Created `app/Models/NotificationPreference.php`
- ✅ Updated `User` model with preference methods
- ✅ Implemented `shouldReceiveNotification()` method
- ✅ Implemented `setNotificationPreference()` method
- ✅ Auto-create defaults for new users

### Controllers

- ✅ Created `Settings/NotificationPreferenceController.php`
- ✅ Implemented `index()` - get preferences
- ✅ Implemented `update()` - save preferences
- ✅ Implemented `reset()` - restore defaults

### Validation

- ✅ Created `UpdateNotificationPreferencesRequest`
- ✅ Validate notification types
- ✅ Validate channel toggles

### Notification Updates

- ✅ Updated all notification classes to check preferences
- ✅ Dynamic channel selection based on user preferences

## Type of Change

- [x] New feature (backend)
- [ ] Bug fix
- [ ] Breaking change
- [ ] Documentation update

## Testing Checklist

### Model Tests

- [ ] NotificationPreference model can be created
- [ ] Relationships work correctly
- [ ] `shouldReceiveNotification()` returns correct value
- [ ] `setNotificationPreference()` creates/updates preference
- [ ] Default preferences created for new users
- [ ] Available types list is complete

### Feature Tests

- [ ] Can get user preferences
- [ ] Can update preferences
- [ ] Can reset to defaults
- [ ] Invalid notification type rejected
- [ ] Preferences persist correctly
- [ ] Activity logged for preference changes

### Integration Tests

- [ ] Notification respects email preference
- [ ] Notification respects database preference
- [ ] User with disabled email doesn't receive email
- [ ] User with disabled database doesn't get DB notification
- [ ] Defaults applied when no preference exists

## Verification Steps

```bash
# Run migration
./vendor/bin/sail artisan migrate

# Test in tinker
./vendor/bin/sail artisan tinker
>>> $user = User::first();
>>> $user->notificationPreferences; // Should auto-create if new user
>>> $user->shouldReceiveNotification('enrollment_approved', 'mail'); // true (default)
>>> $user->setNotificationPreference('enrollment_approved', false, true);
>>> $user->shouldReceiveNotification('enrollment_approved', 'mail'); // false
>>> $user->shouldReceiveNotification('enrollment_approved', 'database'); // true

# Test notification sending
>>> $enrollment = Enrollment::first();
>>> $user->notify(new EnrollmentApprovedNotification($enrollment));
// Check that email is NOT sent (preference disabled)
// Check that database notification IS created

# Run tests
./vendor/bin/sail pest tests/Unit/Models/NotificationPreferenceTest.php
./vendor/bin/sail pest tests/Feature/Settings/NotificationPreferencesTest.php
```

## Database Schema

```sql
CREATE TABLE notification_preferences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    notification_type VARCHAR(255) NOT NULL,
    email_enabled BOOLEAN DEFAULT TRUE,
    database_enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY unique_user_type (user_id, notification_type),
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## NotificationPreference Model

### Available Types

```php
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
```

## User Model Updates

### New Methods

```php
public function notificationPreferences()
{
    return $this->hasMany(NotificationPreference::class);
}

public function shouldReceiveNotification(string $type, string $channel = 'mail'): bool
{
    $preference = $this->notificationPreferences()
        ->where('notification_type', $type)
        ->first();

    if (!$preference) {
        return true; // Default to enabled
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
```

### Boot Method

```php
public static function boot()
{
    parent::boot();

    static::created(function ($user) {
        $user->createDefaultNotificationPreferences();
    });
}
```

## Updated Notification Classes

### Example: EnrollmentApprovedNotification

```php
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
```

## Controller Methods

### Index

```php
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
```

### Update

```php
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
```

### Reset

```php
public function reset()
{
    $user = auth()->user();

    $user->notificationPreferences()->delete();
    $user->createDefaultNotificationPreferences();

    return back()->with('success', 'Notification preferences reset to default.');
}
```

## Routes

```php
Route::middleware('auth')->prefix('settings/notifications')->name('settings.notifications.')->group(function () {
    Route::get('/', [NotificationPreferenceController::class, 'index'])->name('index');
    Route::put('/', [NotificationPreferenceController::class, 'update'])->name('update');
    Route::post('/reset', [NotificationPreferenceController::class, 'reset'])->name('reset');
});
```

## Validation Request

```php
class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function rules()
    {
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

## API Response Examples

### Get Preferences

```json
{
    "preferences": {
        "enrollment_approved": {
            "notification_type": "enrollment_approved",
            "email_enabled": true,
            "database_enabled": true
        },
        "enrollment_rejected": {
            "notification_type": "enrollment_rejected",
            "email_enabled": false,
            "database_enabled": true
        }
    },
    "availableTypes": {
        "enrollment_approved": "Enrollment Application Approved",
        "enrollment_rejected": "Enrollment Application Rejected"
    }
}
```

### Update Request

```json
{
    "preferences": {
        "enrollment_approved": {
            "email_enabled": true,
            "database_enabled": true
        },
        "enrollment_rejected": {
            "email_enabled": false,
            "database_enabled": true
        }
    }
}
```

## Dependencies

- Laravel Notifications system
- Activity log package

## Breaking Changes

None (New feature)

## Data Migration

For existing users without preferences:

```bash
php artisan tinker
User::chunk(100, function ($users) {
    foreach ($users as $user) {
        if ($user->notificationPreferences()->count() === 0) {
            $user->createDefaultNotificationPreferences();
        }
    }
});
```

## Deployment Notes

- Run migration: `php artisan migrate`
- Run data migration for existing users
- Clear cache: `php artisan cache:clear`

## Post-Merge Checklist

- [ ] Migration run successfully
- [ ] Existing users have default preferences
- [ ] New users get preferences auto-created
- [ ] Notification channels respect preferences
- [ ] API endpoints work correctly
- [ ] Activity logged for changes
- [ ] Tests pass
- [ ] Next ticket (TICKET-013) can begin

## Reviewer Notes

Please verify:

1. Default preferences created for all scenarios
2. Notification channel selection is dynamic
3. Preferences validation is comprehensive
4. Activity logging captures changes
5. Model relationships are correct
6. Boot method doesn't cause N+1 issues
7. Tests cover all edge cases
8. Migration is reversible

---

**Ticket:** #012
**Estimated Effort:** 1 day
**Actual Effort:** _[To be filled after completion]_

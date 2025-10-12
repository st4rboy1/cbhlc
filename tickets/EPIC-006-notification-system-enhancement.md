# Ticket #006: Notification System Enhancement

## Priority: High (Should Have)

## Related SRS Requirements

- **Section 8.2.2:** NOTIFICATION entity (Supporting Entities in ERD)
- **FR-4.3:** System shall send status update notifications to parents
- **FR-8.4:** System shall support announcement broadcasting
- **NFR-2.4:** Comprehensive audit logging

## Current Status

⚠️ **PARTIALLY IMPLEMENTED**

Current implementation:

- Laravel's built-in notification system exists
- User model may have notifications relationship
- Email notifications for enrollment status exist
- Missing comprehensive notification management UI
- No notification preferences
- No in-app notification center

## Required Implementation

### 1. Database Layer

Verify/Create migrations for notifications:

**Laravel's built-in table** (if not exists):

```bash
php artisan notifications:table
php artisan migrate
```

**Notification Preferences Table:**

```php
Schema::create('notification_preferences', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('notification_type'); // e.g., 'enrollment_approved'
    $table->boolean('email_enabled')->default(true);
    $table->boolean('database_enabled')->default(true);
    $table->boolean('sms_enabled')->default(false);
    $table->json('schedule')->nullable(); // Custom delivery schedule
    $table->timestamps();

    $table->unique(['user_id', 'notification_type']);
});
```

### 2. Notification Types

**Create Notification Classes:**

```php
// app/Notifications/Enrollment/EnrollmentApprovedNotification.php
// app/Notifications/Enrollment/EnrollmentRejectedNotification.php
// app/Notifications/Enrollment/EnrollmentPendingReviewNotification.php
// app/Notifications/Enrollment/DocumentVerificationRequiredNotification.php
// app/Notifications/Billing/PaymentDueNotification.php
// app/Notifications/Billing/PaymentReceivedNotification.php
// app/Notifications/Billing/PaymentOverdueNotification.php
// app/Notifications/System/AnnouncementPublishedNotification.php
// app/Notifications/System/AccountCreatedNotification.php
// app/Notifications/Communication/InquiryResponseNotification.php
```

**Example Notification:**

```php
class EnrollmentApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Enrollment $enrollment
    ) {}

    public function via($notifiable): array
    {
        $preferences = $notifiable->notificationPreferences()
            ->where('notification_type', 'enrollment_approved')
            ->first();

        $channels = [];

        if ($preferences?->email_enabled ?? true) {
            $channels[] = 'mail';
        }

        if ($preferences?->database_enabled ?? true) {
            $channels[] = 'database';
        }

        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Enrollment Application Approved')
            ->greeting("Hello {$notifiable->name}!")
            ->line("We're pleased to inform you that the enrollment application for {$this->enrollment->student->full_name} has been approved.")
            ->line("Grade Level: {$this->enrollment->grade_level->label()}")
            ->line("School Year: {$this->enrollment->school_year}")
            ->action('View Enrollment', route('guardian.enrollments.show', $this->enrollment))
            ->line('Thank you for choosing our school!');
    }

    public function toArray($notifiable): array
    {
        return [
            'enrollment_id' => $this->enrollment->id,
            'student_name' => $this->enrollment->student->full_name,
            'grade_level' => $this->enrollment->grade_level->value,
            'school_year' => $this->enrollment->school_year,
            'message' => "Enrollment application for {$this->enrollment->student->full_name} has been approved.",
        ];
    }
}
```

### 3. Model Enhancement

**User Model:**

```php
// Add to User model
public function notificationPreferences()
{
    return $this->hasMany(NotificationPreference::class);
}

public function unreadNotifications()
{
    return $this->notifications()->whereNull('read_at');
}

public function markAllNotificationsAsRead()
{
    $this->unreadNotifications()->update(['read_at' => now()]);
}

public function getNotificationPreference(string $type): bool
{
    return $this->notificationPreferences()
        ->where('notification_type', $type)
        ->first()?->email_enabled ?? true;
}
```

**Create NotificationPreference Model:**

```php
class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'notification_type',
        'email_enabled',
        'database_enabled',
        'sms_enabled',
        'schedule',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'database_enabled' => 'boolean',
        'sms_enabled' => 'boolean',
        'schedule' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### 4. Backend Layer

**Controllers:**

#### Settings/NotificationController

- View notification preferences
- Update notification preferences
- Bulk update preferences

#### Api/NotificationController

- Fetch notifications (paginated)
- Mark as read
- Mark all as read
- Delete notification

**Routes:**

```php
// Settings routes
Route::prefix('settings/notifications')->name('settings.notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::put('/preferences', 'updatePreferences')->name('update-preferences');
    Route::post('/test', 'sendTest')->name('test');
});

// API routes
Route::prefix('api/notifications')->name('api.notifications.')->group(function () {
    Route::get('/', [ApiNotificationController::class, 'index'])->name('index');
    Route::post('/{id}/read', 'markAsRead')->name('mark-read');
    Route::post('/read-all', 'markAllAsRead')->name('mark-all-read');
    Route::delete('/{id}', 'destroy')->name('destroy');
    Route::delete('/clear-all', 'clearAll')->name('clear-all');
});
```

### 5. Frontend Layer

**Notification Center Component:**

```tsx
// resources/js/components/notification-center.tsx
// - Dropdown bell icon with badge
// - List of recent notifications
// - Mark as read functionality
// - Link to full notification page
```

**Pages:**

- `/resources/js/pages/settings/notifications.tsx` - Preferences page
- `/resources/js/pages/notifications/index.tsx` - All notifications page

**Components:**

- `NotificationBell` - Header notification icon with badge
- `NotificationDropdown` - Dropdown list of notifications
- `NotificationCard` - Individual notification display
- `NotificationPreferences` - Preference settings form
- `NotificationList` - Paginated notification list

**Features:**

- Real-time notification badge count
- Dropdown with recent 5 notifications
- Mark as read on click
- Mark all as read button
- Delete notification
- Clear all notifications
- Notification preferences UI
- Test notification feature
- Notification grouping
- Notification filtering (read/unread)

### 6. Real-time Notifications (Optional)

**Using Laravel Echo + Pusher:**

```php
// Broadcasting notification
public function toBroadcast($notifiable)
{
    return new BroadcastMessage([
        'data' => $this->toArray($notifiable),
        'message' => 'New notification received',
    ]);
}

// Route
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
```

**Frontend:**

```tsx
// Listen for notifications
Echo.private(`App.Models.User.${userId}`).notification((notification) => {
    // Update notification badge
    // Show toast
    // Update notification list
});
```

### 7. Notification Templates

**Email Templates:**

- Create Blade templates for all notification types
- Use consistent branding and styling
- Include school logo and contact info
- Add unsubscribe link
- Mobile-responsive design

**Template Location:**

```
resources/views/emails/enrollment/approved.blade.php
resources/views/emails/enrollment/rejected.blade.php
resources/views/emails/billing/payment-due.blade.php
```

### 8. Notification Dispatch

**Update Observers:**

```php
// EnrollmentObserver
public function updated(Enrollment $enrollment)
{
    if ($enrollment->wasChanged('status')) {
        match($enrollment->status) {
            EnrollmentStatus::APPROVED =>
                $enrollment->guardian->notify(new EnrollmentApprovedNotification($enrollment)),
            EnrollmentStatus::REJECTED =>
                $enrollment->guardian->notify(new EnrollmentRejectedNotification($enrollment)),
            default => null,
        };
    }
}
```

### 9. Scheduled Notifications

**Create Commands:**

```php
// app/Console/Commands/SendPaymentReminders.php
class SendPaymentReminders extends Command
{
    public function handle()
    {
        $enrollments = Enrollment::whereHas('invoices', function($query) {
            $query->where('status', 'unpaid')
                  ->where('due_date', '<=', now()->addDays(3));
        })->get();

        foreach ($enrollments as $enrollment) {
            $enrollment->guardian->notify(
                new PaymentDueNotification($enrollment)
            );
        }
    }
}
```

**Schedule in Kernel:**

```php
$schedule->command('notifications:payment-reminders')->daily();
```

## Acceptance Criteria

✅ Users can view notifications in a notification center
✅ Notification badge shows unread count
✅ Users can mark notifications as read
✅ Users can mark all notifications as read
✅ Users can delete individual notifications
✅ Users can configure notification preferences
✅ Email notifications respect user preferences
✅ Enrollment status changes trigger notifications
✅ Payment reminders are sent automatically
✅ Announcement notifications work correctly
✅ Notification templates are branded and professional
✅ Real-time notifications work (if implemented)

## Testing Requirements

- Unit tests for notification classes
- Feature tests for notification dispatching
- Integration tests with enrollment workflow
- UI tests for notification center
- Email rendering tests
- Preference management tests
- Scheduled notification tests

## Estimated Effort

**High Priority:** 3-4 days

## Dependencies

- Requires queue system (Redis recommended)
- Requires email configuration
- Optional: Pusher/Laravel Echo for real-time
- Requires proper notification templates

## Implementation Phases

**Phase 1: Core System (1-2 days)**

- Notification preferences table
- Notification classes
- Basic notification dispatch

**Phase 2: Frontend (1-2 days)**

- Notification center UI
- Preferences page
- Badge and dropdown

**Phase 3: Integration (1 day)**

- Integrate with enrollment workflow
- Scheduled notifications
- Email templates

**Phase 4: Enhancements (optional)**

- Real-time notifications
- Advanced preferences
- Notification history

## Notes

- Consider SMS notifications for critical updates
- Add notification digest (daily/weekly summary email)
- Implement notification read receipts
- Add notification export functionality
- Consider notification webhooks for integrations
- Ensure GDPR/DPA compliance for notification data
- Add notification analytics

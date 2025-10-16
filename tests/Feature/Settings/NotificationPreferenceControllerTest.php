<?php

use App\Models\NotificationPreference;
use App\Models\User;
use App\Notifications\EnrollmentPeriodStatusChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles
    Role::create(['name' => 'super_admin']);
    Role::create(['name' => 'guardian']);

    // Create user
    $this->user = User::factory()->create();
    $this->user->assignRole('guardian');
});

// ========================================
// MODEL RELATIONSHIP TESTS
// ========================================

test('user has notification preferences relationship', function () {
    expect($this->user->notificationPreferences())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('notification preference belongs to user', function () {
    // Use an existing preference that was created by default
    $preference = $this->user->getNotificationPreference('enrollment_approved');

    expect($preference->user)->toBeInstanceOf(User::class);
    expect($preference->user->id)->toBe($this->user->id);
});

// ========================================
// DEFAULT PREFERENCES TESTS
// ========================================

test('default preferences are created for new users', function () {
    $newUser = User::factory()->create();

    $availableTypes = NotificationPreference::availableTypes();
    expect($newUser->notificationPreferences()->count())->toBe(count($availableTypes));

    foreach (array_keys($availableTypes) as $type) {
        $preference = $newUser->getNotificationPreference($type);
        expect($preference)->not->toBeNull();
        expect($preference->email_enabled)->toBeTrue();
        expect($preference->database_enabled)->toBeTrue();
    }
});

test('available types returns correct notification types', function () {
    $types = NotificationPreference::availableTypes();

    expect($types)->toBeArray();
    expect($types)->toHaveKey('enrollment_approved');
    expect($types)->toHaveKey('enrollment_rejected');
    expect($types)->toHaveKey('document_verified');
    expect($types)->toHaveKey('document_rejected');
    expect($types)->toHaveKey('payment_due');
    expect($types['enrollment_approved'])->toBe('Enrollment Application Approved');
});

// ========================================
// USER PREFERENCE METHODS TESTS
// ========================================

test('shouldReceiveNotification returns true when no preference set', function () {
    // Delete all preferences
    $this->user->notificationPreferences()->delete();

    expect($this->user->shouldReceiveNotification('enrollment_approved', 'mail'))->toBeTrue();
    expect($this->user->shouldReceiveNotification('enrollment_approved', 'database'))->toBeTrue();
});

test('shouldReceiveNotification respects email preference', function () {
    $this->user->setNotificationPreference('enrollment_approved', false, true);

    expect($this->user->shouldReceiveNotification('enrollment_approved', 'mail'))->toBeFalse();
    expect($this->user->shouldReceiveNotification('enrollment_approved', 'database'))->toBeTrue();
});

test('shouldReceiveNotification respects database preference', function () {
    $this->user->setNotificationPreference('enrollment_approved', true, false);

    expect($this->user->shouldReceiveNotification('enrollment_approved', 'mail'))->toBeTrue();
    expect($this->user->shouldReceiveNotification('enrollment_approved', 'database'))->toBeFalse();
});

test('setNotificationPreference creates or updates preference', function () {
    // First call should create
    $this->user->setNotificationPreference('enrollment_approved', false, false);

    $preference = $this->user->getNotificationPreference('enrollment_approved');
    expect($preference->email_enabled)->toBeFalse();
    expect($preference->database_enabled)->toBeFalse();

    // Second call should update
    $this->user->setNotificationPreference('enrollment_approved', true, true);

    $preference->refresh();
    expect($preference->email_enabled)->toBeTrue();
    expect($preference->database_enabled)->toBeTrue();
});

test('getNotificationPreference returns correct preference', function () {
    $preference = $this->user->getNotificationPreference('enrollment_approved');

    expect($preference)->not->toBeNull();
    expect($preference->notification_type)->toBe('enrollment_approved');
    expect($preference->user_id)->toBe($this->user->id);
});

test('getNotificationPreference returns null for non-existent type', function () {
    $this->user->notificationPreferences()->delete();

    $preference = $this->user->getNotificationPreference('non_existent_type');

    expect($preference)->toBeNull();
});

// ========================================
// CONTROLLER TESTS - INDEX
// ========================================

test('user can view notification preferences page', function () {
    $response = actingAs($this->user)
        ->get(route('settings.notifications.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('settings/notifications', false)
        ->has('preferences')
        ->has('availableTypes')
    );
});

test('preferences are keyed by notification type', function () {
    $response = actingAs($this->user)
        ->get(route('settings.notifications.index'));

    $response->assertOk();

    $preferences = $response->viewData('page')['props']['preferences'];
    expect($preferences)->toHaveKey('enrollment_approved');
    expect($preferences)->toHaveKey('document_verified');
});

test('guest cannot view notification preferences', function () {
    $response = $this->get(route('settings.notifications.index'));

    $response->assertRedirect(route('login'));
});

// ========================================
// CONTROLLER TESTS - UPDATE
// ========================================

test('user can update notification preferences', function () {
    $response = actingAs($this->user)
        ->put(route('settings.notifications.update'), [
            'preferences' => [
                'enrollment_approved' => [
                    'email_enabled' => false,
                    'database_enabled' => true,
                ],
                'document_verified' => [
                    'email_enabled' => true,
                    'database_enabled' => false,
                ],
            ],
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $enrollmentPref = $this->user->getNotificationPreference('enrollment_approved');
    expect($enrollmentPref->email_enabled)->toBeFalse();
    expect($enrollmentPref->database_enabled)->toBeTrue();

    $documentPref = $this->user->getNotificationPreference('document_verified');
    expect($documentPref->email_enabled)->toBeTrue();
    expect($documentPref->database_enabled)->toBeFalse();
});

test('updating preferences logs activity', function () {
    actingAs($this->user)
        ->put(route('settings.notifications.update'), [
            'preferences' => [
                'enrollment_approved' => [
                    'email_enabled' => false,
                    'database_enabled' => true,
                ],
            ],
        ]);

    assertDatabaseHas('activity_log', [
        'description' => 'Notification preferences updated',
        'causer_id' => $this->user->id,
        'causer_type' => User::class,
    ]);
});

test('update validates required preferences field', function () {
    $response = actingAs($this->user)
        ->put(route('settings.notifications.update'), []);

    $response->assertSessionHasErrors('preferences');
});

test('update validates preferences must be array', function () {
    $response = actingAs($this->user)
        ->put(route('settings.notifications.update'), [
            'preferences' => 'not-an-array',
        ]);

    $response->assertSessionHasErrors('preferences');
});

test('update validates email_enabled must be boolean', function () {
    $response = actingAs($this->user)
        ->put(route('settings.notifications.update'), [
            'preferences' => [
                'enrollment_approved' => [
                    'email_enabled' => 'not-boolean',
                    'database_enabled' => true,
                ],
            ],
        ]);

    $response->assertSessionHasErrors('preferences.enrollment_approved.email_enabled');
});

test('update validates database_enabled must be boolean', function () {
    $response = actingAs($this->user)
        ->put(route('settings.notifications.update'), [
            'preferences' => [
                'enrollment_approved' => [
                    'email_enabled' => true,
                    'database_enabled' => 'not-boolean',
                ],
            ],
        ]);

    $response->assertSessionHasErrors('preferences.enrollment_approved.database_enabled');
});

test('update rejects invalid notification types', function () {
    $response = actingAs($this->user)
        ->put(route('settings.notifications.update'), [
            'preferences' => [
                'invalid_notification_type' => [
                    'email_enabled' => true,
                    'database_enabled' => true,
                ],
            ],
        ]);

    $response->assertSessionHasErrors('preferences');
});

test('guest cannot update notification preferences', function () {
    $response = $this->put(route('settings.notifications.update'), [
        'preferences' => [
            'enrollment_approved' => [
                'email_enabled' => false,
                'database_enabled' => true,
            ],
        ],
    ]);

    $response->assertRedirect(route('login'));
});

// ========================================
// CONTROLLER TESTS - RESET
// ========================================

test('user can reset preferences to default', function () {
    // Disable all preferences
    foreach (array_keys(NotificationPreference::availableTypes()) as $type) {
        $this->user->setNotificationPreference($type, false, false);
    }

    // Verify they're disabled
    $preference = $this->user->getNotificationPreference('enrollment_approved');
    expect($preference->email_enabled)->toBeFalse();
    expect($preference->database_enabled)->toBeFalse();

    // Reset
    $response = actingAs($this->user)
        ->post(route('settings.notifications.reset'));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Verify they're back to default (enabled)
    $preference = $this->user->getNotificationPreference('enrollment_approved')->fresh();
    expect($preference->email_enabled)->toBeTrue();
    expect($preference->database_enabled)->toBeTrue();
});

test('resetting preferences logs activity', function () {
    actingAs($this->user)
        ->post(route('settings.notifications.reset'));

    assertDatabaseHas('activity_log', [
        'description' => 'Notification preferences reset to default',
        'causer_id' => $this->user->id,
        'causer_type' => User::class,
    ]);
});

test('guest cannot reset notification preferences', function () {
    $response = $this->post(route('settings.notifications.reset'));

    $response->assertRedirect(route('login'));
});

// ========================================
// NOTIFICATION INTEGRATION TESTS
// ========================================

test('notification respects email preference when disabled', function () {
    Notification::fake();

    $this->user->setNotificationPreference('enrollment_period_changed', false, true);

    $this->user->notify(new EnrollmentPeriodStatusChangedNotification([
        'activated' => 1,
        'closed' => 0,
    ]));

    Notification::assertSentTo(
        $this->user,
        EnrollmentPeriodStatusChangedNotification::class,
        function ($notification, $channels) {
            return ! in_array('mail', $channels) && in_array('database', $channels);
        }
    );
});

test('notification respects database preference when disabled', function () {
    Notification::fake();

    $this->user->setNotificationPreference('enrollment_period_changed', true, false);

    $this->user->notify(new EnrollmentPeriodStatusChangedNotification([
        'activated' => 1,
        'closed' => 0,
    ]));

    Notification::assertSentTo(
        $this->user,
        EnrollmentPeriodStatusChangedNotification::class,
        function ($notification, $channels) {
            return in_array('mail', $channels) && ! in_array('database', $channels);
        }
    );
});

test('notification respects both preferences when both disabled', function () {
    Notification::fake();

    $this->user->setNotificationPreference('enrollment_period_changed', false, false);

    $this->user->notify(new EnrollmentPeriodStatusChangedNotification([
        'activated' => 1,
        'closed' => 0,
    ]));

    // When both channels are disabled, notification is not sent
    Notification::assertNothingSentTo($this->user);
});

test('notification uses both channels when both enabled', function () {
    Notification::fake();

    $this->user->setNotificationPreference('enrollment_period_changed', true, true);

    $this->user->notify(new EnrollmentPeriodStatusChangedNotification([
        'activated' => 1,
        'closed' => 0,
    ]));

    Notification::assertSentTo(
        $this->user,
        EnrollmentPeriodStatusChangedNotification::class,
        function ($notification, $channels) {
            return in_array('mail', $channels) && in_array('database', $channels);
        }
    );
});

test('notification uses default when no preference exists', function () {
    Notification::fake();

    // Delete preference
    $this->user->notificationPreferences()
        ->where('notification_type', 'enrollment_period_changed')
        ->delete();

    $this->user->notify(new EnrollmentPeriodStatusChangedNotification([
        'activated' => 1,
        'closed' => 0,
    ]));

    Notification::assertSentTo(
        $this->user,
        EnrollmentPeriodStatusChangedNotification::class,
        function ($notification, $channels) {
            // Default is both channels enabled
            return in_array('mail', $channels) && in_array('database', $channels);
        }
    );
});

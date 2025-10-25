<?php

use App\Events\UserEmailVerified;
use App\Models\EmailVerificationEvent;
use App\Models\User;
use App\Notifications\UserEmailVerifiedNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('user email verification creates verification event record', function () {
    $user = User::factory()->unverified()->create();

    // Get verification URL
    $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $this->actingAs($user)->get($verificationUrl);

    // Check that user is now verified
    $user->refresh();
    expect($user->hasVerifiedEmail())->toBeTrue();

    // Check that verification event was logged
    $this->assertDatabaseHas('email_verification_events', [
        'user_id' => $user->id,
    ]);
});

test('UserEmailVerified event sends notifications to super admins', function () {
    Notification::fake();

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    $user = User::factory()->create();

    event(new UserEmailVerified($user, '127.0.0.1', 'Test Agent'));

    Notification::assertSentTo(
        $superAdmin,
        UserEmailVerifiedNotification::class
    );
});

test('UserEmailVerified event sends notifications to admins', function () {
    Notification::fake();

    $admin = User::factory()->create();
    $admin->assignRole('administrator');

    $user = User::factory()->create();

    event(new UserEmailVerified($user, '127.0.0.1', 'Test Agent'));

    Notification::assertSentTo(
        $admin,
        UserEmailVerifiedNotification::class
    );
});

test('UserEmailVerified event does not send notifications to guardians', function () {
    Notification::fake();

    $guardian = User::factory()->create();
    $guardian->assignRole('guardian');

    $user = User::factory()->create();

    event(new UserEmailVerified($user, '127.0.0.1', 'Test Agent'));

    Notification::assertNotSentTo(
        $guardian,
        UserEmailVerifiedNotification::class
    );
});

test('email verification event is logged to database', function () {
    $user = User::factory()->create();

    event(new UserEmailVerified($user, '192.168.1.1', 'Mozilla/5.0'));

    $this->assertDatabaseHas('email_verification_events', [
        'user_id' => $user->id,
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0',
    ]);
});

test('email verification event calculates time to verify', function () {
    $user = User::factory()->create([
        'created_at' => now()->subHours(2),
    ]);

    event(new UserEmailVerified($user, '127.0.0.1', 'Test Agent'));

    $event = EmailVerificationEvent::where('user_id', $user->id)->first();

    expect($event->time_to_verify_minutes)->toBeGreaterThanOrEqual(119)
        ->and($event->time_to_verify_minutes)->toBeLessThanOrEqual(121);
});

test('notification contains correct user information', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $event = new UserEmailVerified($user, '127.0.0.1', 'Test Agent');
    $notification = new UserEmailVerifiedNotification(
        $user,
        $event->getTimeToVerify(),
        $event->verifiedAt
    );

    $mailData = $notification->toMail($user);

    expect($mailData->subject)->toContain('John Doe')
        ->and($mailData->introLines)->toContain($user->name.' has successfully verified their email address.');
});

test('notification includes time to verify metric', function () {
    $user = User::factory()->create([
        'created_at' => now()->subHours(3),
    ]);

    $event = new UserEmailVerified($user, '127.0.0.1', 'Test Agent');
    $timeToVerify = $event->getTimeToVerify();

    expect($timeToVerify)->toContain('hour');
});

test('notification warns if verification took longer than 48 hours', function () {
    $user = User::factory()->create([
        'created_at' => now()->subHours(50),
    ]);

    $event = new UserEmailVerified($user, '127.0.0.1', 'Test Agent');
    $notification = new UserEmailVerifiedNotification(
        $user,
        $event->getTimeToVerify(),
        $event->verifiedAt
    );

    $mailData = $notification->toMail($user);

    $allLines = array_merge($mailData->introLines, $mailData->outroLines);
    $hasWarning = collect($allLines)->contains(fn ($line) => str_contains($line, '⚠️'));

    expect($hasWarning)->toBeTrue();
});

test('dashboard shows verification metrics', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    // Create some verified users
    User::factory()->count(3)->create(['email_verified_at' => now()]);

    // Create some unverified users
    User::factory()->count(2)->unverified()->create();

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.dashboard'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('stats.verified_users')
        ->has('stats.unverified_users')
        ->has('stats.verification_rate')
    );
});

test('recent verification events are displayed on dashboard', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    $user = User::factory()->create();
    event(new UserEmailVerified($user, '127.0.0.1', 'Test Agent'));

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.dashboard'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('stats.recent_verification_events')
    );
});

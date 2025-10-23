<?php

use App\Models\User;
use App\Notifications\CustomVerifyEmailNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Notification;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Seed roles and permissions for each test
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('sends verification notification', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect(route('home'));

    Notification::assertSentTo($user, CustomVerifyEmailNotification::class);
});

test('does not send verification notification if email is verified', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $user->assignRole('guardian'); // Assign a role so getDashboardRoute() works

    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect(route($user->getDashboardRoute(), absolute: false));

    Notification::assertNothingSent();
});

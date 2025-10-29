<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Seed roles for tests
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

test('registration page can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

test('new users can register and are assigned guardian role', function () {
    $response = $this->post(route('register.store'), [
        'first_name' => 'Test',
        'last_name' => 'Parent',
        'email' => 'guardian@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'contact_number' => '+63912345678',
        'address' => '123 Test Street, Manila',
        'occupation' => 'Teacher',
        'employer' => 'Test School',
    ]);

    // Check if user was created and has guardian role
    $user = \App\Models\User::where('email', 'guardian@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('guardian'))->toBeTrue();

    // Check if Guardian record was created with contact information
    $guardian = \App\Models\Guardian::where('user_id', $user->id)->first();
    expect($guardian)->not->toBeNull();
    expect($guardian->first_name)->toBe('Test');
    expect($guardian->last_name)->toBe('Parent');
    expect($guardian->contact_number)->toBe('+63912345678');
    expect($guardian->address)->toBe('123 Test Street, Manila');
    expect($guardian->occupation)->toBe('Teacher');
    expect($guardian->employer)->toBe('Test School');

    // Check that email is not verified yet
    expect($user->hasVerifiedEmail())->toBeFalse();

    $this->assertAuthenticated();
    // All registered users get redirected to email verification notice
    $response->assertRedirect(route('verification.notice', absolute: false));
});

test('registration no longer accepts role parameter', function () {
    // Even if role is provided, it should be ignored and guardian role assigned
    $response = $this->post(route('register.store'), [
        'first_name' => 'Test',
        'last_name' => 'User',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'contact_number' => '+63912345678',
        'address' => '123 Test Street, Manila',
        'occupation' => 'Engineer',
        'employer' => 'Tech Company',
        'role' => 'student', // This should be ignored
    ]);

    // Check if user was created with guardian role regardless of parameter
    $user = \App\Models\User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('guardian'))->toBeTrue();
    expect($user->hasRole('student'))->toBeFalse();

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice', absolute: false));
});

test('registration validates required fields', function () {
    $response = $this->post(route('register.store'), []);

    $response->assertSessionHasErrors(['first_name', 'last_name', 'email', 'password', 'contact_number', 'address']);
});

test('registration validates email format', function () {
    $response = $this->post(route('register.store'), [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'not-an-email',
        'password' => 'password',
        'password_confirmation' => 'password',
        'contact_number' => '+63912345678',
        'address' => '123 Test Street, Manila',
        'occupation' => 'Engineer',
    ]);

    $response->assertSessionHasErrors('email');
});

test('registration requires both first and last name', function () {
    $response = $this->post(route('register.store'), [
        'first_name' => 'Madonna',
        'last_name' => 'Ciccone',
        'email' => 'madonna@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'contact_number' => '+63912345678',
        'address' => '123 Test Street, Manila',
        'occupation' => 'Singer',
        'employer' => 'Self-employed',
    ]);

    $user = \App\Models\User::where('email', 'madonna@example.com')->first();
    $guardian = \App\Models\Guardian::where('user_id', $user->id)->first();

    expect($guardian)->not->toBeNull();
    expect($guardian->first_name)->toBe('Madonna');
    expect($guardian->last_name)->toBe('Ciccone');
    expect($guardian->contact_number)->toBe('+63912345678');
    expect($guardian->address)->toBe('123 Test Street, Manila');
    expect($guardian->occupation)->toBe('Singer');
    expect($guardian->employer)->toBe('Self-employed');
});

test('employer field is optional', function () {
    $response = $this->post(route('register.store'), [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'optional@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'contact_number' => '+63912345678',
        'address' => '123 Test Street, Manila',
        'occupation' => 'Freelancer',
        // employer intentionally omitted
    ]);

    $user = \App\Models\User::where('email', 'optional@example.com')->first();
    $guardian = \App\Models\Guardian::where('user_id', $user->id)->first();

    expect($guardian)->not->toBeNull();
    expect($guardian->employer)->toBeNull();

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice', absolute: false));
});

test('registration sends email verification notification', function () {
    \Illuminate\Support\Facades\Notification::fake();

    $response = $this->post(route('register.store'), [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'verify@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'contact_number' => '+63912345678',
        'address' => '123 Test Street, Manila',
        'occupation' => 'Engineer',
    ]);

    $user = \App\Models\User::where('email', 'verify@example.com')->first();
    expect($user)->not->toBeNull();

    // Verify that email verification notification was sent
    \Illuminate\Support\Facades\Notification::assertSentTo(
        $user,
        \App\Notifications\CustomVerifyEmailNotification::class
    );

    $response->assertRedirect(route('verification.notice', absolute: false));
});

test('unverified users cannot access guardian dashboard', function () {
    $user = \App\Models\User::factory()->create([
        'email_verified_at' => null,
    ]);
    $user->assignRole('guardian');

    $response = $this->actingAs($user)->get(route('guardian.dashboard'));

    // Should be redirected to verification notice
    $response->assertRedirect(route('verification.notice'));
});

test('verified users can access guardian dashboard', function () {
    $user = \App\Models\User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $user->assignRole('guardian');

    // Create guardian profile
    \App\Models\Guardian::create([
        'user_id' => $user->id,
        'first_name' => 'Test',
        'last_name' => 'Guardian',
        'contact_number' => '+63912345678',
        'address' => '123 Test Street',
        'occupation' => 'Teacher',
    ]);

    $response = $this->actingAs($user)->get(route('guardian.dashboard'));

    // Should be able to access dashboard
    $response->assertStatus(200);
});

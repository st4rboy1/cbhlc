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
        'name' => 'Test Parent',
        'email' => 'guardian@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    // Check if user was created and has guardian role
    $user = \App\Models\User::where('email', 'guardian@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('guardian'))->toBeTrue();

    // Check if Guardian record was created
    $guardian = \App\Models\Guardian::where('user_id', $user->id)->first();
    expect($guardian)->not->toBeNull();
    expect($guardian->first_name)->toBe('Test');
    expect($guardian->last_name)->toBe('Parent');

    $this->assertAuthenticated();
    // All registered users get redirected to guardian dashboard
    $response->assertRedirect(route('guardian.dashboard', absolute: false));
});

test('registration no longer accepts role parameter', function () {
    // Even if role is provided, it should be ignored and guardian role assigned
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'student', // This should be ignored
    ]);

    // Check if user was created with guardian role regardless of parameter
    $user = \App\Models\User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('guardian'))->toBeTrue();
    expect($user->hasRole('student'))->toBeFalse();

    $this->assertAuthenticated();
    $response->assertRedirect(route('guardian.dashboard', absolute: false));
});

test('registration validates required fields', function () {
    $response = $this->post(route('register.store'), []);

    $response->assertSessionHasErrors(['name', 'email', 'password']);
});

test('registration validates email format', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'not-an-email',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
});

test('registration handles single-word names correctly', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Madonna',
        'email' => 'madonna@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = \App\Models\User::where('email', 'madonna@example.com')->first();
    $guardian = \App\Models\Guardian::where('user_id', $user->id)->first();

    expect($guardian)->not->toBeNull();
    expect($guardian->first_name)->toBe('Madonna');
    expect($guardian->last_name)->toBe('');
});

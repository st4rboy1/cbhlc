<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Seed roles for tests
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

test('registration route redirects to home', function () {
    $response = $this->get(route('register'));

    $response->assertRedirect('/');
});

test('new guardian users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test Parent',
        'email' => 'guardian@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'guardian',
    ]);

    // Check if user was created and has role first
    $user = \App\Models\User::where('email', 'guardian@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('guardian'))->toBeTrue();

    $this->assertAuthenticated();
    // Parent users get redirected to guardian dashboard
    $response->assertRedirect(route('guardian.dashboard', absolute: false));
});

test('new student users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test Student',
        'email' => 'student@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'student',
    ]);

    // Check if user was created and has role first
    $user = \App\Models\User::where('email', 'student@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('student'))->toBeTrue();

    $this->assertAuthenticated();
    // Student users get redirected to student dashboard
    $response->assertRedirect(route('student.dashboard', absolute: false));
});

test('registration requires role selection', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        // Missing role field
    ]);

    $response->assertSessionHasErrors('role');
});

test('registration only accepts valid roles', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'admin', // Invalid role - only guardian and student allowed
    ]);

    $response->assertSessionHasErrors('role');
});

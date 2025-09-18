<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Seed roles for tests
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

test('new parent users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test Parent',
        'email' => 'parent@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'parent',
    ]);

    // Check if user was created and has role first
    $user = \App\Models\User::where('email', 'parent@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('parent'))->toBeTrue();

    $this->assertAuthenticated();
    // Parent users get redirected to parent dashboard
    $response->assertRedirect(route('parent.dashboard', absolute: false));
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
        'role' => 'admin', // Invalid role - only parent and student allowed
    ]);

    $response->assertSessionHasErrors('role');
});

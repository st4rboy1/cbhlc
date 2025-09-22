<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('registration page redirects to home', function () {
    $response = $this->get('/register');

    $response->assertStatus(302);
    $response->assertRedirect('/');
});

test('registration requires role field', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['role']);
    $this->assertGuest();
});

test('new users can register with guardian role', function () {
    $response = $this->post('/register', [
        'name' => 'Test Guardian',
        'email' => 'guardian@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'guardian',
    ]);

    $user = User::where('email', 'guardian@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Test Guardian')
        ->and($user->hasRole('guardian'))->toBeTrue();

    $this->assertAuthenticated();
    $response->assertRedirect('/guardian/dashboard');
});

test('new users can register with student role', function () {
    $response = $this->post('/register', [
        'name' => 'Test Student',
        'email' => 'student@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'student',
    ]);

    $user = User::where('email', 'student@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Test Student')
        ->and($user->hasRole('student'))->toBeTrue();

    $this->assertAuthenticated();
    $response->assertRedirect('/student/dashboard');
});

test('registration validates role must be guardian or student', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'invalid_role',
    ]);

    $response->assertSessionHasErrors(['role']);
    $this->assertGuest();
});

test('registration requires unique email', function () {
    // Create existing user
    User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'existing@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'guardian',
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();
});

test('registration requires password confirmation', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'different_password',
        'role' => 'guardian',
    ]);

    $response->assertSessionHasErrors(['password']);
    $this->assertGuest();
});

test('user is redirected to correct dashboard after registration', function () {
    // Test guardian registration
    $response = $this->post('/register', [
        'name' => 'Guardian User',
        'email' => 'guardian@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'guardian',
    ]);

    $response->assertRedirect('/guardian/dashboard');

    // Logout and test student registration
    $this->post('/logout');

    $response = $this->post('/register', [
        'name' => 'Student User',
        'email' => 'student@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'student',
    ]);

    $response->assertRedirect('/student/dashboard');
});

test('registration validates all required fields', function () {
    $response = $this->post('/register', []);

    $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    $this->assertGuest();
});

test('registration requires lowercase email', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'TEST@EXAMPLE.COM',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'guardian',
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();
});

test('registration accepts lowercase email', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'guardian',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();

    $user = auth()->user();
    expect($user)->not->toBeNull()
        ->and($user->email)->toBe('test@example.com');
});

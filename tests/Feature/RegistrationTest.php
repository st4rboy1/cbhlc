<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('registration page can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('registration does not require role field', function () {
    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'contact_number' => '+63912345678',
        'address' => '123 Test Street, Manila',
        'occupation' => 'Engineer',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();

    $user = User::where('email', 'test@example.com')->first();
    expect($user->hasRole('guardian'))->toBeTrue();
});

test('new users are automatically assigned guardian role', function () {
    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'Guardian',
        'email' => 'guardian@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'contact_number' => '+63912345678',
        'address' => '123 Test Street, Manila',
        'occupation' => 'Teacher',
    ]);

    $user = User::where('email', 'guardian@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Test Guardian')
        ->and($user->hasRole('guardian'))->toBeTrue();

    $this->assertAuthenticated();
    $response->assertRedirect('/verify-email');
});

test('role parameter is ignored if provided', function () {
    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'Student',
        'email' => 'student@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'contact_number' => '+63912345678',
        'address' => '123 Test Street, Manila',
        'occupation' => 'Student',
        'role' => 'student', // This should be ignored
    ]);

    $user = User::where('email', 'student@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Test Student')
        ->and($user->hasRole('guardian'))->toBeTrue()
        ->and($user->hasRole('student'))->toBeFalse();

    $this->assertAuthenticated();
    $response->assertRedirect('/verify-email');
});

test('invalid role parameter is ignored', function () {
    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'contact_number' => '+63912345678',
        'address' => '123 Test Street, Manila',
        'occupation' => 'Engineer',
        'role' => 'invalid_role', // This should be ignored
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();

    $user = User::where('email', 'test@example.com')->first();
    expect($user->hasRole('guardian'))->toBeTrue();
});

test('registration requires unique email', function () {
    // Create existing user
    User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'existing@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'contact_number' => '+63912345678',
        'address' => '123 Test Street, Manila',
        'occupation' => 'Engineer',
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();
});

test('registration requires password confirmation', function () {
    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'different_password',
        'contact_number' => '+63912345678',
        'address' => '123 Test Street, Manila',
        'occupation' => 'Engineer',
    ]);

    $response->assertSessionHasErrors(['password']);
    $this->assertGuest();
});

test('all users are redirected to email verification after registration', function () {
    // Test first registration
    $response = $this->post('/register', [
        'first_name' => 'First',
        'last_name' => 'User',
        'email' => 'first@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'contact_number' => '+63912345678',
        'address' => '123 Test Street, Manila',
        'occupation' => 'Engineer',
    ]);

    $response->assertRedirect('/verify-email');

    // Logout and test second registration
    $this->post('/logout');

    $response = $this->post('/register', [
        'first_name' => 'Second',
        'last_name' => 'User',
        'email' => 'second@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'contact_number' => '+63912345679',
        'address' => '456 Test Street, Manila',
        'occupation' => 'Teacher',
    ]);

    $response->assertRedirect('/verify-email');
});

test('registration validates all required fields', function () {
    $response = $this->post('/register', []);

    $response->assertSessionHasErrors(['first_name', 'last_name', 'email', 'password', 'contact_number', 'address']);
    $this->assertGuest();
});

test('registration requires lowercase email', function () {
    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'TEST@EXAMPLE.COM',
        'password' => 'password',
        'password_confirmation' => 'password',
        'contact_number' => '+63912345678',
        'address' => '123 Test Street, Manila',
        'occupation' => 'Engineer',
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();
});

test('registration accepts lowercase email', function () {
    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'contact_number' => '+63912345678',
        'address' => '123 Test Street, Manila',
        'occupation' => 'Engineer',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();

    $user = auth()->user();
    expect($user)->not->toBeNull()
        ->and($user->email)->toBe('test@example.com');
});

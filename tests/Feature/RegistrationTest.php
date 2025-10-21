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
        'name' => 'Test User',
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
        'name' => 'Test Guardian',
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
    $response->assertRedirect('/guardian/dashboard');
});

test('role parameter is ignored if provided', function () {
    $response = $this->post('/register', [
        'name' => 'Test Student',
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
    $response->assertRedirect('/guardian/dashboard');
});

test('invalid role parameter is ignored', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
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
        'name' => 'Test User',
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
        'name' => 'Test User',
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

test('all users are redirected to guardian dashboard after registration', function () {
    // Test first registration
    $response = $this->post('/register', [
        'name' => 'First User',
        'email' => 'first@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'contact_number' => '+63912345678',
        'address' => '123 Test Street, Manila',
        'occupation' => 'Engineer',
    ]);

    $response->assertRedirect('/guardian/dashboard');

    // Logout and test second registration
    $this->post('/logout');

    $response = $this->post('/register', [
        'name' => 'Second User',
        'email' => 'second@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'contact_number' => '+63912345679',
        'address' => '456 Test Street, Manila',
        'occupation' => 'Teacher',
    ]);

    $response->assertRedirect('/guardian/dashboard');
});

test('registration validates all required fields', function () {
    $response = $this->post('/register', []);

    $response->assertSessionHasErrors(['name', 'email', 'password', 'contact_number', 'address', 'occupation']);
    $this->assertGuest();
});

test('registration requires lowercase email', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
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
        'name' => 'Test User',
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

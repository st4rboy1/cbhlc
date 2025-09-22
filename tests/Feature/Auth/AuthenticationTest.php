<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Seed roles and permissions for each test
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    // User without role should redirect to home
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    // Users without roles should redirect to home
    $response->assertRedirect(route('home'));
});

test('super admin redirects to admin dashboard after login', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('admin.dashboard'));
});

test('administrator redirects to admin dashboard after login', function () {
    $admin = User::factory()->administrator()->create();

    $response = $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('admin.dashboard'));
});

test('registrar redirects to registrar dashboard after login', function () {
    $registrar = User::factory()->registrar()->create();

    $response = $this->post(route('login.store'), [
        'email' => $registrar->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('registrar.dashboard'));
});

test('guardian redirects to guardian dashboard after login', function () {
    $guardian = User::factory()->guardian()->create();

    $response = $this->post(route('login.store'), [
        'email' => $guardian->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('guardian.dashboard'));
});

test('student redirects to student dashboard after login', function () {
    $student = User::factory()->student()->create();

    $response = $this->post(route('login.store'), [
        'email' => $student->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('student.dashboard'));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('login validates required fields', function () {
    $response = $this->post(route('login.store'), []);

    $response->assertSessionHasErrors(['email', 'password']);
});

test('login validates email format', function () {
    $response = $this->post(route('login.store'), [
        'email' => 'not-an-email',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect(route('home'));
});

test('users are rate limited', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(302)->assertSessionHasErrors([
            'email' => 'These credentials do not match our records.',
        ]);
    }

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors('email');

    $errors = session('errors');

    $this->assertStringContainsString('Too many login attempts', $errors->first('email'));
});

test('authentication completes within 2 seconds as per NFR-1.1', function () {
    $user = User::factory()->create();

    $startTime = microtime(true);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;

    $this->assertAuthenticated();
    $this->assertLessThan(2.0, $executionTime, 'Authentication should complete within 2 seconds');
});

test('invalid credentials show error within 1 second as per acceptance criteria', function () {
    $user = User::factory()->create();

    $startTime = microtime(true);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
    $this->assertLessThan(1.0, $executionTime, 'Invalid credential response should be within 1 second');
});

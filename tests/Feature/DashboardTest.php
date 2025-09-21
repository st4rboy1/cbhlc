<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Seed roles and permissions for each test
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('guests are redirected to the login page for admin dashboard', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

test('guests are redirected to the login page for registrar dashboard', function () {
    $this->get(route('registrar.dashboard'))->assertRedirect(route('login'));
});

test('guests are redirected to the login page for guardian dashboard', function () {
    $this->get(route('guardian.dashboard'))->assertRedirect(route('login'));
});

test('guests are redirected to the login page for student dashboard', function () {
    $this->get(route('student.dashboard'))->assertRedirect(route('login'));
});

test('super admin can visit admin dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();
});

test('administrator can visit admin dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');

    $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();
});

test('registrar can visit registrar dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('registrar');

    $this->actingAs($user)->get(route('registrar.dashboard'))->assertOk();
});

test('guardian can visit guardian dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');

    $this->actingAs($user)->get(route('guardian.dashboard'))->assertOk();
});

test('student can visit student dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('student');

    $this->actingAs($user)->get(route('student.dashboard'))->assertOk();
});

test('super admin is redirected to admin dashboard', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');
    expect($superAdmin->getDashboardRoute())->toBe('admin.dashboard');
});

test('administrator is redirected to admin dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrator');
    expect($admin->getDashboardRoute())->toBe('admin.dashboard');
});

test('registrar is redirected to registrar dashboard', function () {
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');
    expect($registrar->getDashboardRoute())->toBe('registrar.dashboard');
});

test('guardian is redirected to guardian dashboard', function () {
    $guardian = User::factory()->create();
    $guardian->assignRole('guardian');
    expect($guardian->getDashboardRoute())->toBe('guardian.dashboard');
});

test('student is redirected to student dashboard', function () {
    $student = User::factory()->create();
    $student->syncRoles('student');
    expect($student->getDashboardRoute())->toBe('student.dashboard');
});

test('user without role is redirected to home', function () {
    $userWithoutRole = User::factory()->create();
    expect($userWithoutRole->getDashboardRoute())->toBe('home');
});

test('users cannot access dashboards they do not have permission for', function () {
    $guardian = User::factory()->create();
    $guardian->assignRole('guardian');

    // Guardian should not be able to access admin dashboard
    $this->actingAs($guardian)->get(route('admin.dashboard'))->assertForbidden();

    // Guardian should not be able to access registrar dashboard
    $this->actingAs($guardian)->get(route('registrar.dashboard'))->assertForbidden();

    // Guardian should not be able to access student dashboard
    $this->actingAs($guardian)->get(route('student.dashboard'))->assertForbidden();
});

test('registrar cannot access admin dashboard', function () {
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    $this->actingAs($registrar)->get(route('admin.dashboard'))->assertForbidden();
});

test('student cannot access other dashboards', function () {
    $student = User::factory()->create();
    $student->syncRoles('student');

    $this->actingAs($student)->get(route('admin.dashboard'))->assertForbidden();
    $this->actingAs($student)->get(route('registrar.dashboard'))->assertForbidden();
    $this->actingAs($student)->get(route('guardian.dashboard'))->assertForbidden();
});

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

test('guests are redirected to the login page for parent dashboard', function () {
    $this->get(route('parent.dashboard'))->assertRedirect(route('login'));
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

test('parent can visit parent dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('parent');

    $this->actingAs($user)->get(route('parent.dashboard'))->assertOk();
});

test('student can visit student dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('student');

    $this->actingAs($user)->get(route('student.dashboard'))->assertOk();
});

test('user is redirected to correct dashboard based on role', function () {
    // Test super admin
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');
    expect($superAdmin->getDashboardRoute())->toBe('admin.dashboard');

    // Test administrator
    $admin = User::factory()->create();
    $admin->assignRole('administrator');
    expect($admin->getDashboardRoute())->toBe('admin.dashboard');

    // Test registrar
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');
    expect($registrar->getDashboardRoute())->toBe('registrar.dashboard');

    // Test parent
    $parent = User::factory()->create();
    $parent->assignRole('parent');
    expect($parent->getDashboardRoute())->toBe('parent.dashboard');

    // Test student
    $student = User::factory()->create();
    $student->assignRole('student');
    expect($student->getDashboardRoute())->toBe('student.dashboard');

    // Test user without role (fallback to home)
    $userWithoutRole = User::factory()->create();
    expect($userWithoutRole->getDashboardRoute())->toBe('home');
});

test('users cannot access dashboards they do not have permission for', function () {
    $parent = User::factory()->create();
    $parent->assignRole('parent');

    // Parent should not be able to access admin dashboard
    $this->actingAs($parent)->get(route('admin.dashboard'))->assertForbidden();

    // Parent should not be able to access registrar dashboard
    $this->actingAs($parent)->get(route('registrar.dashboard'))->assertForbidden();

    // Parent should not be able to access student dashboard
    $this->actingAs($parent)->get(route('student.dashboard'))->assertForbidden();
});

test('registrar cannot access admin dashboard', function () {
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    $this->actingAs($registrar)->get(route('admin.dashboard'))->assertForbidden();
});

test('student cannot access other dashboards', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $this->actingAs($student)->get(route('admin.dashboard'))->assertForbidden();
    $this->actingAs($student)->get(route('registrar.dashboard'))->assertForbidden();
    $this->actingAs($student)->get(route('parent.dashboard'))->assertForbidden();
});

<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Seed roles and permissions
    $this->seed(RolesAndPermissionsSeeder::class);

    // Clear permission cache to ensure roles have fresh permissions
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
});

test('registrar can access grade level fees index', function () {
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    $response = $this->actingAs($registrar)->get('/registrar/grade-level-fees');

    $response->assertStatus(200);
});

test('registrar can access grade level fees create page', function () {
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    $response = $this->actingAs($registrar)->get('/registrar/grade-level-fees/create');

    $response->assertStatus(200);
});

test('guardian cannot access grade level fees', function () {
    $guardian = User::factory()->create();
    $guardian->assignRole('guardian');

    $response = $this->actingAs($guardian)->get('/registrar/grade-level-fees');

    $response->assertStatus(403);
});

test('student cannot access grade level fees', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $response = $this->actingAs($student)->get('/registrar/grade-level-fees');

    $response->assertStatus(403);
});

test('unauthenticated user cannot access grade level fees', function () {
    $response = $this->get('/registrar/grade-level-fees');

    $response->assertRedirect('/login');
});

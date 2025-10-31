<?php

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

test('registrar can update enrollment status', function () {
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    $enrollment = Enrollment::factory()->create([
        'status' => EnrollmentStatus::PENDING,
    ]);

    $response = $this->actingAs($registrar)
        ->put(route('registrar.enrollments.update-status', $enrollment), [
            'status' => EnrollmentStatus::APPROVED->value,
            'remarks' => 'Test approval',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $enrollment->refresh();
    expect($enrollment->status)->toBe(EnrollmentStatus::APPROVED);
});

test('administrator can update enrollment status', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrator');

    $enrollment = Enrollment::factory()->create([
        'status' => EnrollmentStatus::PENDING,
    ]);

    $response = $this->actingAs($admin)
        ->put(route('registrar.enrollments.update-status', $enrollment), [
            'status' => EnrollmentStatus::REJECTED->value,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $enrollment->refresh();
    expect($enrollment->status)->toBe(EnrollmentStatus::REJECTED);
});

test('super admin can update enrollment status', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    $enrollment = Enrollment::factory()->create([
        'status' => EnrollmentStatus::APPROVED,
    ]);

    $response = $this->actingAs($superAdmin)
        ->put(route('registrar.enrollments.update-status', $enrollment), [
            'status' => EnrollmentStatus::ENROLLED->value,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $enrollment->refresh();
    expect($enrollment->status)->toBe(EnrollmentStatus::ENROLLED);
});

test('guardian cannot update enrollment status', function () {
    $guardian = User::factory()->create();
    $guardian->assignRole('guardian');

    $enrollment = Enrollment::factory()->create([
        'status' => EnrollmentStatus::PENDING,
    ]);

    $response = $this->actingAs($guardian)
        ->put(route('registrar.enrollments.update-status', $enrollment), [
            'status' => EnrollmentStatus::APPROVED->value,
        ]);

    $response->assertForbidden();
});

test('update enrollment status requires valid status', function () {
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    $enrollment = Enrollment::factory()->create([
        'status' => EnrollmentStatus::PENDING,
    ]);

    $response = $this->actingAs($registrar)
        ->put(route('registrar.enrollments.update-status', $enrollment), [
            'status' => 'invalid_status',
        ]);

    $response->assertSessionHasErrors(['status']);
});

test('update enrollment status requires status field', function () {
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    $enrollment = Enrollment::factory()->create([
        'status' => EnrollmentStatus::PENDING,
    ]);

    $response = $this->actingAs($registrar)
        ->put(route('registrar.enrollments.update-status', $enrollment), [
            'remarks' => 'No status provided',
        ]);

    $response->assertSessionHasErrors(['status']);
});

test('update enrollment status accepts optional remarks', function () {
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    $enrollment = Enrollment::factory()->create([
        'status' => EnrollmentStatus::PENDING,
    ]);

    $response = $this->actingAs($registrar)
        ->put(route('registrar.enrollments.update-status', $enrollment), [
            'status' => EnrollmentStatus::APPROVED->value,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $enrollment->refresh();
    expect($enrollment->status)->toBe(EnrollmentStatus::APPROVED);
});

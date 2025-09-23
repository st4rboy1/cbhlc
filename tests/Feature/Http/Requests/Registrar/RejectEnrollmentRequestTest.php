<?php

use App\Enums\EnrollmentStatus;
use App\Http\Requests\Registrar\RejectEnrollmentRequest;
use App\Models\Enrollment;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->registrar = User::factory()->create();
    $this->registrar->assignRole('registrar');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('administrator');

    $this->guardian = User::factory()->create();
    $this->guardian->assignRole('guardian');

    $this->enrollment = Enrollment::factory()->create([
        'status' => EnrollmentStatus::PENDING,
    ]);
});

test('registrar is authorized to reject enrollment', function () {
    $response = $this->actingAs($this->registrar)
        ->post(route('registrar.enrollments.reject', $this->enrollment), [
            'reason' => 'Missing required documents',
        ]);

    $response->assertSessionDoesntHaveErrors();
    $response->assertRedirect();
});

test('guardian is not authorized to reject enrollment', function () {
    $response = $this->actingAs($this->guardian)
        ->post(route('registrar.enrollments.reject', $this->enrollment), [
            'reason' => 'Missing required documents',
        ]);

    $response->assertStatus(403);
});

test('reason is required', function () {
    $response = $this->actingAs($this->registrar)
        ->post(route('registrar.enrollments.reject', $this->enrollment), [
            'reason' => '',
        ]);

    $response->assertSessionHasErrors(['reason']);
    $errors = session('errors')->get('reason');
    expect($errors)->toContain('A rejection reason is required.');
});

test('reason must be at least 10 characters', function () {
    $response = $this->actingAs($this->registrar)
        ->post(route('registrar.enrollments.reject', $this->enrollment), [
            'reason' => 'Too short',
        ]);

    $response->assertSessionHasErrors(['reason']);
    $errors = session('errors')->get('reason');
    expect($errors)->toContain('Rejection reason must be at least 10 characters.');
});

test('reason cannot exceed 500 characters', function () {
    $response = $this->actingAs($this->registrar)
        ->post(route('registrar.enrollments.reject', $this->enrollment), [
            'reason' => str_repeat('a', 501),
        ]);

    $response->assertSessionHasErrors(['reason']);
    $errors = session('errors')->get('reason');
    expect($errors)->toContain('Rejection reason must not exceed 500 characters.');
});

test('valid reason passes validation', function () {
    $reason = 'The submitted documents are incomplete. Missing Form 138 and Good Moral Certificate.';

    $response = $this->actingAs($this->registrar)
        ->post(route('registrar.enrollments.reject', $this->enrollment), [
            'reason' => $reason,
        ]);

    $response->assertSessionDoesntHaveErrors();
    $response->assertRedirect();
});

test('reason is trimmed before validation', function () {
    $reason = '   The submitted documents are incomplete.   ';

    $response = $this->actingAs($this->registrar)
        ->post(route('registrar.enrollments.reject', $this->enrollment), [
            'reason' => $reason,
        ]);

    $response->assertSessionDoesntHaveErrors();

    // Check that the trimmed value was used
    $this->enrollment->refresh();
    expect($this->enrollment->remarks)->toBe('The submitted documents are incomplete.');
});

test('form request validation rules work correctly', function () {
    $request = new RejectEnrollmentRequest;

    $rules = $request->rules();

    expect($rules)->toHaveKey('reason');
    expect($rules['reason'])->toContain('required');
    expect($rules['reason'])->toContain('string');
    expect($rules['reason'])->toContain('min:10');
    expect($rules['reason'])->toContain('max:500');
});

test('form request messages are customized', function () {
    $request = new RejectEnrollmentRequest;

    $messages = $request->messages();

    expect($messages)->toHaveKey('reason.required');
    expect($messages['reason.required'])->toBe('A rejection reason is required.');

    expect($messages)->toHaveKey('reason.min');
    expect($messages['reason.min'])->toBe('Rejection reason must be at least 10 characters.');

    expect($messages)->toHaveKey('reason.max');
    expect($messages['reason.max'])->toBe('Rejection reason must not exceed 500 characters.');
});

test('unauthenticated user cannot reject enrollment', function () {
    $response = $this->post(route('registrar.enrollments.reject', $this->enrollment), [
        'reason' => 'Missing required documents',
    ]);

    $response->assertRedirect('/login');
});

test('administrator is authorized to reject enrollment', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('registrar.enrollments.reject', $this->enrollment), [
            'reason' => 'Missing required documents for enrollment',
        ]);

    $response->assertSessionDoesntHaveErrors();
    $response->assertRedirect();
});

test('super admin is authorized to reject enrollment', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    $response = $this->actingAs($superAdmin)
        ->post(route('registrar.enrollments.reject', $this->enrollment), [
            'reason' => 'Missing required documents for enrollment',
        ]);

    $response->assertSessionDoesntHaveErrors();
    $response->assertRedirect();
});

<?php

use App\Enums\EnrollmentStatus;
use App\Http\Requests\Registrar\BulkApproveEnrollmentsRequest;
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
});

test('registrar is authorized to bulk approve enrollments', function () {
    $enrollments = Enrollment::factory()->count(3)->create([
        'status' => EnrollmentStatus::PENDING,
    ]);

    $response = $this->actingAs($this->registrar)
        ->post(route('registrar.enrollments.bulk-approve'), [
            'enrollment_ids' => $enrollments->pluck('id')->toArray(),
        ]);

    $response->assertSessionDoesntHaveErrors();
    $response->assertRedirect();
});

test('guardian is not authorized to bulk approve enrollments', function () {
    $enrollments = Enrollment::factory()->count(3)->create([
        'status' => EnrollmentStatus::PENDING,
    ]);

    $response = $this->actingAs($this->guardian)
        ->post(route('registrar.enrollments.bulk-approve'), [
            'enrollment_ids' => $enrollments->pluck('id')->toArray(),
        ]);

    $response->assertStatus(403);
});

test('enrollment ids are required', function () {
    $response = $this->actingAs($this->registrar)
        ->post(route('registrar.enrollments.bulk-approve'), []);

    $response->assertSessionHasErrors(['enrollment_ids']);
    $errors = session('errors')->get('enrollment_ids');
    expect($errors)->toContain('Please select at least one enrollment.');
});

test('enrollment ids must be an array', function () {
    $response = $this->actingAs($this->registrar)
        ->post(route('registrar.enrollments.bulk-approve'), [
            'enrollment_ids' => 'not-an-array',
        ]);

    $response->assertSessionHasErrors(['enrollment_ids']);
    $errors = session('errors')->get('enrollment_ids');
    expect($errors)->toContain('Invalid enrollment selection.');
});

test('enrollment ids array must have at least one item', function () {
    $response = $this->actingAs($this->registrar)
        ->post(route('registrar.enrollments.bulk-approve'), [
            'enrollment_ids' => [],
        ]);

    $response->assertSessionHasErrors(['enrollment_ids']);
    $errors = session('errors')->get('enrollment_ids');
    expect($errors)->toContain('Please select at least one enrollment.');
});

test('each enrollment id must exist', function () {
    $response = $this->actingAs($this->registrar)
        ->post(route('registrar.enrollments.bulk-approve'), [
            'enrollment_ids' => [999999, 888888],
        ]);

    $response->assertSessionHasErrors(['enrollment_ids.0', 'enrollment_ids.1']);
    $errors = session('errors')->get('enrollment_ids.0');
    expect($errors)->toContain('One or more selected enrollments do not exist.');
});

test('cannot approve non pending enrollments', function () {
    $pendingEnrollment = Enrollment::factory()->create([
        'status' => EnrollmentStatus::PENDING,
    ]);

    $approvedEnrollment = Enrollment::factory()->create([
        'status' => EnrollmentStatus::ENROLLED,
    ]);

    $response = $this->actingAs($this->registrar)
        ->post(route('registrar.enrollments.bulk-approve'), [
            'enrollment_ids' => [
                $pendingEnrollment->id,
                $approvedEnrollment->id,
            ],
        ]);

    $response->assertSessionHasErrors(['enrollment_ids.1']);
    $errors = session('errors')->get('enrollment_ids.1');
    expect($errors[0])->toContain("Enrollment ID {$approvedEnrollment->id} is not in pending status");
});

test('valid pending enrollment ids pass validation', function () {
    $enrollments = Enrollment::factory()->count(5)->create([
        'status' => EnrollmentStatus::PENDING,
    ]);

    $response = $this->actingAs($this->registrar)
        ->post(route('registrar.enrollments.bulk-approve'), [
            'enrollment_ids' => $enrollments->pluck('id')->toArray(),
        ]);

    $response->assertSessionDoesntHaveErrors();
    $response->assertRedirect();
});

test('enrollment ids are converted to integers', function () {
    $enrollments = Enrollment::factory()->count(3)->create([
        'status' => EnrollmentStatus::PENDING,
    ]);

    // Send string IDs
    $response = $this->actingAs($this->registrar)
        ->post(route('registrar.enrollments.bulk-approve'), [
            'enrollment_ids' => $enrollments->pluck('id')->map(function ($id) {
                return (string) $id;
            })->toArray(),
        ]);

    $response->assertSessionDoesntHaveErrors();
    $response->assertRedirect();

    // Verify enrollments were actually approved
    foreach ($enrollments as $enrollment) {
        $enrollment->refresh();
        expect($enrollment->status)->toBe(EnrollmentStatus::ENROLLED);
    }
});

test('form request validation rules work correctly', function () {
    $request = new BulkApproveEnrollmentsRequest;

    $rules = $request->rules();

    expect($rules)->toHaveKey('enrollment_ids');
    expect($rules['enrollment_ids'])->toContain('required');
    expect($rules['enrollment_ids'])->toContain('array');
    expect($rules['enrollment_ids'])->toContain('min:1');

    expect($rules)->toHaveKey('enrollment_ids.*');
    $enrollmentIdRules = $rules['enrollment_ids.*'];
    expect($enrollmentIdRules)->toContain('required');
    expect($enrollmentIdRules)->toContain('integer');
    expect($enrollmentIdRules)->toContain('exists:enrollments,id');
});

test('form request messages are customized', function () {
    $request = new BulkApproveEnrollmentsRequest;

    $messages = $request->messages();

    expect($messages)->toHaveKey('enrollment_ids.required');
    expect($messages['enrollment_ids.required'])->toBe('Please select at least one enrollment.');

    expect($messages)->toHaveKey('enrollment_ids.array');
    expect($messages['enrollment_ids.array'])->toBe('Invalid enrollment selection.');

    expect($messages)->toHaveKey('enrollment_ids.min');
    expect($messages['enrollment_ids.min'])->toBe('Please select at least one enrollment.');

    expect($messages)->toHaveKey('enrollment_ids.*.exists');
    expect($messages['enrollment_ids.*.exists'])->toBe('One or more selected enrollments do not exist.');
});

test('rejected enrollments cannot be bulk approved', function () {
    $pendingEnrollment = Enrollment::factory()->create([
        'status' => EnrollmentStatus::PENDING,
    ]);

    $rejectedEnrollment = Enrollment::factory()->create([
        'status' => EnrollmentStatus::REJECTED,
    ]);

    $response = $this->actingAs($this->registrar)
        ->post(route('registrar.enrollments.bulk-approve'), [
            'enrollment_ids' => [
                $pendingEnrollment->id,
                $rejectedEnrollment->id,
            ],
        ]);

    $response->assertSessionHasErrors(['enrollment_ids.1']);
    $errors = session('errors')->get('enrollment_ids.1');
    expect($errors[0])->toContain("Enrollment ID {$rejectedEnrollment->id} is not in pending status");
});

test('unauthenticated user cannot bulk approve enrollments', function () {
    $enrollments = Enrollment::factory()->count(3)->create([
        'status' => EnrollmentStatus::PENDING,
    ]);

    $response = $this->post(route('registrar.enrollments.bulk-approve'), [
        'enrollment_ids' => $enrollments->pluck('id')->toArray(),
    ]);

    $response->assertRedirect('/login');
});

test('mixed valid and invalid enrollment ids fail validation', function () {
    $validEnrollment = Enrollment::factory()->create([
        'status' => EnrollmentStatus::PENDING,
    ]);

    $response = $this->actingAs($this->registrar)
        ->post(route('registrar.enrollments.bulk-approve'), [
            'enrollment_ids' => [$validEnrollment->id, 999999],
        ]);

    $response->assertSessionHasErrors(['enrollment_ids.1']);
});

test('administrator is authorized to bulk approve enrollments', function () {
    $enrollments = Enrollment::factory()->count(3)->create([
        'status' => EnrollmentStatus::PENDING,
    ]);

    $response = $this->actingAs($this->admin)
        ->post(route('registrar.enrollments.bulk-approve'), [
            'enrollment_ids' => $enrollments->pluck('id')->toArray(),
        ]);

    $response->assertSessionDoesntHaveErrors();
    $response->assertRedirect();
});

test('super admin is authorized to bulk approve enrollments', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    $enrollments = Enrollment::factory()->count(3)->create([
        'status' => EnrollmentStatus::PENDING,
    ]);

    $response = $this->actingAs($superAdmin)
        ->post(route('registrar.enrollments.bulk-approve'), [
            'enrollment_ids' => $enrollments->pluck('id')->toArray(),
        ]);

    $response->assertSessionDoesntHaveErrors();
    $response->assertRedirect();
});

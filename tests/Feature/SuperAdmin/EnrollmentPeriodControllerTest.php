<?php

use App\Models\Enrollment;
use App\Models\EnrollmentPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles
    Role::create(['name' => 'super_admin']);
    Role::create(['name' => 'administrator']);
    Role::create(['name' => 'guardian']);

    // Create school year
    $this->sy2024 = \App\Models\SchoolYear::create([
        'name' => '2024-2025',
        'start_year' => 2024,
        'end_year' => 2025,
        'start_date' => '2024-06-01',
        'end_date' => '2025-05-31',
        'status' => 'active',
    ]);

    // Create super admin user
    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super_admin');

    // Create non-admin user for authorization tests
    $this->guardian = User::factory()->create();
    $this->guardian->assignRole('guardian');
});

// ========================================
// INDEX TESTS
// ========================================

test('super admin can view enrollment periods index', function () {
    EnrollmentPeriod::factory()->schoolYear('2025-2026')->create([
        'status' => 'active',
        'start_date' => now(),
        'end_date' => now()->addMonths(10),
        'regular_registration_deadline' => now()->addMonths(2),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $response = actingAs($this->superAdmin)
        ->get(route('super-admin.enrollment-periods.index'));

    $response->assertOk();
    // Note: Inertia component check skipped - UI implementation is TICKET-009
    $response->assertInertia(fn ($page) => $page
        ->component('super-admin/enrollment-periods/index', false)
        ->has('periods')
        ->has('activePeriod')
    );
});

// ========================================
// CREATE TESTS
// ========================================

test('super admin can create enrollment period', function () {
    $schoolYear = \App\Models\SchoolYear::factory()->create(['name' => '2025-2026']);

    $data = [
        'school_year_id' => $schoolYear->id,
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addMonths(10)->format('Y-m-d'),
        'early_registration_deadline' => now()->addWeek()->format('Y-m-d'),
        'regular_registration_deadline' => now()->addMonths(2)->format('Y-m-d'),
        'late_registration_deadline' => now()->addMonths(9)->format('Y-m-d'),
        'description' => 'Test enrollment period',
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ];

    $response = actingAs($this->superAdmin)
        ->post(route('super-admin.enrollment-periods.store'), $data);

    $response->assertRedirect(route('super-admin.enrollment-periods.index'));
    $response->assertSessionHas('success');

    assertDatabaseHas('enrollment_periods', [
        'school_year_id' => \App\Models\SchoolYear::where('name', '2025-2026')->first()->id,
        'description' => 'Test enrollment period',
    ]);
});

test('creating enrollment period logs activity', function () {
    $schoolYear = \App\Models\SchoolYear::factory()->create(['name' => '2025-2026']);

    $data = [
        'school_year_id' => $schoolYear->id,
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addMonths(10)->format('Y-m-d'),
        'regular_registration_deadline' => now()->addMonths(2)->format('Y-m-d'),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ];

    actingAs($this->superAdmin)
        ->post(route('super-admin.enrollment-periods.store'), $data);

    assertDatabaseHas('activity_log', [
        'subject_type' => EnrollmentPeriod::class,
        'description' => 'Enrollment period created',
    ]);
});

test('school year must be valid', function () {
    $data = [
        'school_year_id' => 99999, // Non-existent ID
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addMonths(10)->format('Y-m-d'),
        'regular_registration_deadline' => now()->addMonths(2)->format('Y-m-d'),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ];

    $response = actingAs($this->superAdmin)
        ->post(route('super-admin.enrollment-periods.store'), $data);

    $response->assertSessionHasErrors('school_year_id');
});

test('school year must be unique', function () {
    $period = EnrollmentPeriod::factory()->schoolYear('2025-2026')->create([
        'status' => 'active',
        'start_date' => now(),
        'end_date' => now()->addMonths(10),
        'regular_registration_deadline' => now()->addMonths(2),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $data = [
        'school_year_id' => $period->school_year_id, // Duplicate
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addMonths(10)->format('Y-m-d'),
        'regular_registration_deadline' => now()->addMonths(2)->format('Y-m-d'),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ];

    $response = actingAs($this->superAdmin)
        ->post(route('super-admin.enrollment-periods.store'), $data);

    $response->assertSessionHasErrors('school_year_id');
});

test('end date must be after start date', function () {
    $schoolYear = \App\Models\SchoolYear::factory()->create(['name' => '2025-2026']);

    $data = [
        'school_year_id' => $schoolYear->id,
        'start_date' => now()->addMonths(10)->format('Y-m-d'),
        'end_date' => now()->addDay()->format('Y-m-d'), // Before start date
        'regular_registration_deadline' => now()->addMonths(2)->format('Y-m-d'),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ];

    $response = actingAs($this->superAdmin)
        ->post(route('super-admin.enrollment-periods.store'), $data);

    $response->assertSessionHasErrors('end_date');
});

test('registration deadlines must be within period dates', function () {
    $schoolYear = \App\Models\SchoolYear::factory()->create(['name' => '2025-2026']);

    $data = [
        'school_year_id' => $schoolYear->id,
        'start_date' => now()->addMonth()->format('Y-m-d'),
        'end_date' => now()->addMonths(10)->format('Y-m-d'),
        'regular_registration_deadline' => now()->format('Y-m-d'), // Before start date
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ];

    $response = actingAs($this->superAdmin)
        ->post(route('super-admin.enrollment-periods.store'), $data);

    $response->assertSessionHasErrors('regular_registration_deadline');
});

test('late registration deadline must be after regular deadline', function () {
    $schoolYear = \App\Models\SchoolYear::factory()->create(['name' => '2025-2026']);

    $data = [
        'school_year_id' => $schoolYear->id,
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addMonths(10)->format('Y-m-d'),
        'regular_registration_deadline' => now()->addMonths(5)->format('Y-m-d'),
        'late_registration_deadline' => now()->addMonths(3)->format('Y-m-d'), // Before regular
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ];

    $response = actingAs($this->superAdmin)
        ->post(route('super-admin.enrollment-periods.store'), $data);

    $response->assertSessionHasErrors('late_registration_deadline');
});

// ========================================
// UPDATE TESTS
// ========================================

test('super admin can update enrollment period', function () {
    $period = EnrollmentPeriod::factory()->schoolYear('2025-2026')->create([
        'status' => 'upcoming',
        'start_date' => now()->addMonth(),
        'end_date' => now()->addMonths(10),
        'regular_registration_deadline' => now()->addMonths(2),
        'description' => 'Original description',
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $data = [
        'school_year_id' => $period->school_year_id,
        'start_date' => now()->addMonth()->format('Y-m-d'),
        'end_date' => now()->addMonths(11)->format('Y-m-d'),
        'regular_registration_deadline' => now()->addMonths(3)->format('Y-m-d'),
        'description' => 'Updated description',
        'allow_new_students' => false,
        'allow_returning_students' => true,
    ];

    $response = actingAs($this->superAdmin)
        ->put(route('super-admin.enrollment-periods.update', $period), $data);

    $response->assertRedirect(route('super-admin.enrollment-periods.show', $period));
    $response->assertSessionHas('success');

    assertDatabaseHas('enrollment_periods', [
        'id' => $period->id,
        'description' => 'Updated description',
        'allow_new_students' => false,
    ]);
});

test('updating enrollment period logs activity', function () {
    $period = EnrollmentPeriod::factory()->schoolYear('2025-2026')->create([
        'status' => 'upcoming',
        'start_date' => now()->addMonth(),
        'end_date' => now()->addMonths(10),
        'regular_registration_deadline' => now()->addMonths(2),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $data = [
        'school_year_id' => $period->school_year_id,
        'start_date' => now()->addMonth()->format('Y-m-d'),
        'end_date' => now()->addMonths(11)->format('Y-m-d'),
        'regular_registration_deadline' => now()->addMonths(3)->format('Y-m-d'),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ];

    actingAs($this->superAdmin)
        ->put(route('super-admin.enrollment-periods.update', $period), $data);

    assertDatabaseHas('activity_log', [
        'subject_type' => EnrollmentPeriod::class,
        'subject_id' => $period->id,
        'description' => 'Enrollment period updated',
    ]);
});

// ========================================
// DELETE TESTS
// ========================================

test('super admin can delete enrollment period without enrollments', function () {
    $period = EnrollmentPeriod::factory()->schoolYear('2025-2026')->create([
        'status' => 'upcoming',
        'start_date' => now()->addMonth(),
        'end_date' => now()->addMonths(10),
        'regular_registration_deadline' => now()->addMonths(2),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $response = actingAs($this->superAdmin)
        ->delete(route('super-admin.enrollment-periods.destroy', $period));

    $response->assertRedirect(route('super-admin.enrollment-periods.index'));
    $response->assertSessionHas('success');

    assertDatabaseMissing('enrollment_periods', [
        'id' => $period->id,
    ]);
});

test('cannot delete active enrollment period', function () {
    $period = EnrollmentPeriod::factory()->schoolYear('2025-2026')->create([
        'status' => 'active',
        'start_date' => now()->subMonth(),
        'end_date' => now()->addMonths(10),
        'regular_registration_deadline' => now()->addMonths(2),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $response = actingAs($this->superAdmin)
        ->delete(route('super-admin.enrollment-periods.destroy', $period));

    $response->assertSessionHasErrors('period');

    assertDatabaseHas('enrollment_periods', [
        'id' => $period->id,
    ]);
});

test('cannot delete period with existing enrollments', function () {
    $period = EnrollmentPeriod::factory()->schoolYear('2025-2026')->create([
        'status' => 'closed',
        'start_date' => now()->subYear(),
        'end_date' => now()->subMonths(2),
        'regular_registration_deadline' => now()->subMonths(10),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    // Create an enrollment for this period
    $sy2025 = \App\Models\SchoolYear::create(['name' => '2025-2026', 'start_year' => 2025, 'end_year' => 2026, 'start_date' => '2025-06-01', 'end_date' => '2026-05-31', 'status' => 'upcoming']);
    Enrollment::factory()->create([
        'school_year_id' => $sy2025->id,
    ]);

    $response = actingAs($this->superAdmin)
        ->delete(route('super-admin.enrollment-periods.destroy', $period));

    $response->assertSessionHasErrors('period');

    assertDatabaseHas('enrollment_periods', [
        'id' => $period->id,
    ]);
});

// ========================================
// ACTIVATE TESTS
// ========================================

test('super admin can activate enrollment period', function () {
    $period = EnrollmentPeriod::factory()->schoolYear('2025-2026')->create([
        'status' => 'upcoming',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonths(10),
        'regular_registration_deadline' => now()->addMonths(2),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $response = actingAs($this->superAdmin)
        ->post(route('super-admin.enrollment-periods.activate', $period));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $period->refresh();
    expect($period->status)->toBe('active');
});

test('activating period closes other active periods', function () {
    $oldPeriod = EnrollmentPeriod::factory()->schoolYear('2024-2025')->create([
        'status' => 'active',
        'start_date' => now()->subYear(),
        'end_date' => now()->addMonth(),
        'regular_registration_deadline' => now()->subMonths(10),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $newPeriod = EnrollmentPeriod::factory()->schoolYear('2025-2026')->create([
        'status' => 'upcoming',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonths(10),
        'regular_registration_deadline' => now()->addMonths(2),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    actingAs($this->superAdmin)
        ->post(route('super-admin.enrollment-periods.activate', $newPeriod));

    $oldPeriod->refresh();
    $newPeriod->refresh();

    expect($oldPeriod->status)->toBe('closed');
    expect($newPeriod->status)->toBe('active');
});

test('activating period logs activity', function () {
    $period = EnrollmentPeriod::factory()->schoolYear('2025-2026')->create([
        'status' => 'upcoming',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonths(10),
        'regular_registration_deadline' => now()->addMonths(2),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    actingAs($this->superAdmin)
        ->post(route('super-admin.enrollment-periods.activate', $period));

    assertDatabaseHas('activity_log', [
        'subject_type' => EnrollmentPeriod::class,
        'subject_id' => $period->id,
        'description' => 'Enrollment period activated',
    ]);
});

// ========================================
// CLOSE TESTS
// ========================================

test('super admin can close active enrollment period', function () {
    $period = EnrollmentPeriod::factory()->schoolYear('2025-2026')->create([
        'status' => 'active',
        'start_date' => now()->subMonth(),
        'end_date' => now()->addMonths(10),
        'regular_registration_deadline' => now()->addMonths(2),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $response = actingAs($this->superAdmin)
        ->post(route('super-admin.enrollment-periods.close', $period));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $period->refresh();
    expect($period->status)->toBe('closed');
});

test('cannot close non-active enrollment period', function () {
    $period = EnrollmentPeriod::factory()->schoolYear('2025-2026')->create([
        'status' => 'upcoming',
        'start_date' => now()->addMonth(),
        'end_date' => now()->addMonths(10),
        'regular_registration_deadline' => now()->addMonths(2),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $response = actingAs($this->superAdmin)
        ->post(route('super-admin.enrollment-periods.close', $period));

    $response->assertSessionHasErrors('period');

    $period->refresh();
    expect($period->status)->toBe('upcoming');
});

test('closing period logs activity', function () {
    $period = EnrollmentPeriod::factory()->schoolYear('2025-2026')->create([
        'status' => 'active',
        'start_date' => now()->subMonth(),
        'end_date' => now()->addMonths(10),
        'regular_registration_deadline' => now()->addMonths(2),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    actingAs($this->superAdmin)
        ->post(route('super-admin.enrollment-periods.close', $period));

    assertDatabaseHas('activity_log', [
        'subject_type' => EnrollmentPeriod::class,
        'subject_id' => $period->id,
        'description' => 'Enrollment period closed',
    ]);
});

// ========================================
// AUTHORIZATION TESTS
// ========================================

test('non super admin cannot access enrollment periods index', function () {
    $response = actingAs($this->guardian)
        ->get(route('super-admin.enrollment-periods.index'));

    $response->assertForbidden();
});

test('non super admin cannot create enrollment period', function () {
    $schoolYear = \App\Models\SchoolYear::factory()->create(['name' => '2025-2026']);

    $data = [
        'school_year_id' => $schoolYear->id,
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addMonths(10)->format('Y-m-d'),
        'regular_registration_deadline' => now()->addMonths(2)->format('Y-m-d'),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ];

    $response = actingAs($this->guardian)
        ->post(route('super-admin.enrollment-periods.store'), $data);

    $response->assertForbidden();
});

test('non super admin cannot update enrollment period', function () {
    $period = EnrollmentPeriod::factory()->schoolYear('2025-2026')->create([
        'status' => 'upcoming',
        'start_date' => now()->addMonth(),
        'end_date' => now()->addMonths(10),
        'regular_registration_deadline' => now()->addMonths(2),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $data = [
        'school_year_id' => $period->school_year_id,
        'start_date' => now()->addMonth()->format('Y-m-d'),
        'end_date' => now()->addMonths(11)->format('Y-m-d'),
        'regular_registration_deadline' => now()->addMonths(3)->format('Y-m-d'),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ];

    $response = actingAs($this->guardian)
        ->put(route('super-admin.enrollment-periods.update', $period), $data);

    $response->assertForbidden();
});

test('non super admin cannot delete enrollment period', function () {
    $period = EnrollmentPeriod::factory()->schoolYear('2025-2026')->create([
        'status' => 'upcoming',
        'start_date' => now()->addMonth(),
        'end_date' => now()->addMonths(10),
        'regular_registration_deadline' => now()->addMonths(2),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $response = actingAs($this->guardian)
        ->delete(route('super-admin.enrollment-periods.destroy', $period));

    $response->assertForbidden();
});

test('non super admin cannot activate enrollment period', function () {
    $period = EnrollmentPeriod::factory()->schoolYear('2025-2026')->create([
        'status' => 'upcoming',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonths(10),
        'regular_registration_deadline' => now()->addMonths(2),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $response = actingAs($this->guardian)
        ->post(route('super-admin.enrollment-periods.activate', $period));

    $response->assertForbidden();
});

test('non super admin cannot close enrollment period', function () {
    $period = EnrollmentPeriod::factory()->schoolYear('2025-2026')->create([
        'status' => 'active',
        'start_date' => now()->subMonth(),
        'end_date' => now()->addMonths(10),
        'regular_registration_deadline' => now()->addMonths(2),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $response = actingAs($this->guardian)
        ->post(route('super-admin.enrollment-periods.close', $period));

    $response->assertForbidden();
});

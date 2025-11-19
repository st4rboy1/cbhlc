<?php

use App\Models\EnrollmentPeriod;
use App\Models\SchoolYear;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

describe('Super Admin Enrollment Period Year Range', function () {

    test('super admin can create enrollment period with future year dates beyond 2025', function () {
        // Create super admin user
        $user = User::factory()->create([
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('super_admin');

        // Create a school year for 2026-2027
        $schoolYear = SchoolYear::factory()->create([
            'name' => '2026-2027',
            'status' => 'upcoming',
        ]);

        // Login and navigate to create page
        $this->actingAs($user);

        $response = $this->get(route('super-admin.enrollment-periods.create'));
        $response->assertStatus(200);

        // Create enrollment period with dates in 2026
        $response = $this->post(route('super-admin.enrollment-periods.store'), [
            'school_year_id' => $schoolYear->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-08-31',
            'regular_registration_deadline' => '2026-07-15',
            'allow_new_students' => true,
            'allow_returning_students' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Verify enrollment period was created with 2026 dates
        $period = EnrollmentPeriod::where('school_year_id', $schoolYear->id)->first();
        expect($period)->not()->toBeNull();
        expect($period->start_date->format('Y-m-d'))->toBe('2026-06-01');
        expect($period->end_date->format('Y-m-d'))->toBe('2026-08-31');
        expect($period->regular_registration_deadline->format('Y-m-d'))->toBe('2026-07-15');
    })->group('super-admin', 'enrollment-period', 'year-range', 'critical');

    test('super admin can create enrollment period with year 2027 and beyond', function () {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $schoolYear = SchoolYear::factory()->create([
            'name' => '2027-2028',
            'status' => 'upcoming',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('super-admin.enrollment-periods.store'), [
            'school_year_id' => $schoolYear->id,
            'start_date' => '2027-06-01',
            'end_date' => '2027-08-31',
            'early_registration_deadline' => '2027-06-15',
            'regular_registration_deadline' => '2027-07-15',
            'late_registration_deadline' => '2027-08-15',
            'description' => 'Enrollment period for 2027-2028 school year',
            'allow_new_students' => true,
            'allow_returning_students' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $period = EnrollmentPeriod::where('school_year_id', $schoolYear->id)->first();
        expect($period)->not()->toBeNull();
        expect($period->start_date->format('Y-m-d'))->toBe('2027-06-01');
        expect($period->end_date->format('Y-m-d'))->toBe('2027-08-31');
    })->group('super-admin', 'enrollment-period', 'year-range', 'critical');

    test('super admin can edit enrollment period and update to future year dates', function () {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        // Create enrollment period with current year dates
        $schoolYear = SchoolYear::factory()->create([
            'name' => '2025-2026',
            'status' => 'active',
        ]);

        $enrollmentPeriod = EnrollmentPeriod::factory()->create([
            'school_year_id' => $schoolYear->id,
            'start_date' => '2025-06-01',
            'end_date' => '2025-08-31',
            'regular_registration_deadline' => '2025-07-15',
        ]);

        $this->actingAs($user);

        // Update with 2026 dates
        $response = $this->put(route('super-admin.enrollment-periods.update', $enrollmentPeriod), [
            'school_year_id' => $schoolYear->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-08-31',
            'regular_registration_deadline' => '2026-07-15',
            'allow_new_students' => true,
            'allow_returning_students' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Verify dates were updated to 2026
        $enrollmentPeriod->refresh();
        expect($enrollmentPeriod->start_date->format('Y-m-d'))->toBe('2026-06-01');
        expect($enrollmentPeriod->end_date->format('Y-m-d'))->toBe('2026-08-31');
        expect($enrollmentPeriod->regular_registration_deadline->format('Y-m-d'))->toBe('2026-07-15');
    })->group('super-admin', 'enrollment-period', 'year-range', 'critical');

    test('super admin can create enrollment period up to 10 years in the future', function () {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $futureYear = (int) date('Y') + 10;
        $nextYear = $futureYear + 1;

        $schoolYear = SchoolYear::factory()->create([
            'name' => "{$futureYear}-{$nextYear}",
            'status' => 'upcoming',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('super-admin.enrollment-periods.store'), [
            'school_year_id' => $schoolYear->id,
            'start_date' => "{$futureYear}-06-01",
            'end_date' => "{$futureYear}-08-31",
            'regular_registration_deadline' => "{$futureYear}-07-15",
            'allow_new_students' => true,
            'allow_returning_students' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $period = EnrollmentPeriod::where('school_year_id', $schoolYear->id)->first();
        expect($period)->not()->toBeNull();
        expect($period->start_date->format('Y-m-d'))->toBe("{$futureYear}-06-01");
        expect($period->end_date->format('Y-m-d'))->toBe("{$futureYear}-08-31");
    })->group('super-admin', 'enrollment-period', 'year-range');

    test('enrollment period dates are correctly stored with all deadline fields', function () {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $schoolYear = SchoolYear::factory()->create([
            'name' => '2028-2029',
            'status' => 'upcoming',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('super-admin.enrollment-periods.store'), [
            'school_year_id' => $schoolYear->id,
            'start_date' => '2028-06-01',
            'end_date' => '2028-08-31',
            'early_registration_deadline' => '2028-06-10',
            'regular_registration_deadline' => '2028-07-15',
            'late_registration_deadline' => '2028-08-25',
            'description' => 'Test enrollment period with all fields',
            'allow_new_students' => true,
            'allow_returning_students' => false,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Verify all fields are stored correctly
        $period = EnrollmentPeriod::where('school_year_id', $schoolYear->id)->first();
        expect($period)->not()->toBeNull();
        expect($period->start_date->format('Y-m-d'))->toBe('2028-06-01');
        expect($period->end_date->format('Y-m-d'))->toBe('2028-08-31');
        expect($period->early_registration_deadline->format('Y-m-d'))->toBe('2028-06-10');
        expect($period->regular_registration_deadline->format('Y-m-d'))->toBe('2028-07-15');
        expect($period->late_registration_deadline->format('Y-m-d'))->toBe('2028-08-25');
        expect($period->allow_new_students)->toBeTrue();
        expect($period->allow_returning_students)->toBeFalse();
    })->group('super-admin', 'enrollment-period', 'year-range');
});

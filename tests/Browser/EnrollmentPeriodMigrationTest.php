<?php

use App\Enums\EnrollmentPeriodStatus;
use App\Models\EnrollmentPeriod;
use App\Models\SchoolYear;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => RolesAndPermissionsSeeder::class]);
});

describe('Enrollment Period Migration', function () {

    test('enrollment_periods table exists with all required columns', function () {
        expect(Schema::hasTable('enrollment_periods'))->toBeTrue();

        // Verify all required columns exist
        $columns = [
            'id',
            'school_year_id',
            'start_date',
            'end_date',
            'early_registration_deadline',
            'regular_registration_deadline',
            'late_registration_deadline',
            'status',
            'description',
            'allow_new_students',
            'allow_returning_students',
            'created_at',
            'updated_at',
        ];

        foreach ($columns as $column) {
            expect(Schema::hasColumn('enrollment_periods', $column))
                ->toBeTrue("Column {$column} should exist in enrollment_periods table");
        }
    })->group('enrollment-period', 'migration');

    test('can create enrollment period with all required fields', function () {
        $schoolYear = SchoolYear::factory()->create([
            'name' => '2025-2026',
            'start_year' => 2025,
            'end_year' => 2026,
        ]);

        $period = EnrollmentPeriod::create([
            'school_year_id' => $schoolYear->id,
            'start_date' => '2025-06-01',
            'end_date' => '2026-05-31',
            'early_registration_deadline' => '2025-07-15',
            'regular_registration_deadline' => '2025-08-31',
            'late_registration_deadline' => '2025-09-15',
            'status' => EnrollmentPeriodStatus::ACTIVE,
            'description' => 'School Year 2025-2026 Enrollment Period',
            'allow_new_students' => true,
            'allow_returning_students' => true,
        ]);

        expect($period->exists())->toBeTrue();
        expect($period->school_year_id)->toBe($schoolYear->id);
        expect($period->start_date->format('Y-m-d'))->toBe('2025-06-01');
        expect($period->end_date->format('Y-m-d'))->toBe('2026-05-31');
        expect($period->early_registration_deadline->format('Y-m-d'))->toBe('2025-07-15');
        expect($period->regular_registration_deadline->format('Y-m-d'))->toBe('2025-08-31');
        expect($period->late_registration_deadline->format('Y-m-d'))->toBe('2025-09-15');
        expect($period->status)->toBe(EnrollmentPeriodStatus::ACTIVE);
        expect($period->description)->toBe('School Year 2025-2026 Enrollment Period');
        expect($period->allow_new_students)->toBeTrue();
        expect($period->allow_returning_students)->toBeTrue();
    })->group('enrollment-period', 'migration');

    test('enrollment period status enum works correctly', function () {
        $schoolYear = SchoolYear::factory()->create();

        // Test ACTIVE status
        $activePeriod = EnrollmentPeriod::factory()->create([
            'school_year_id' => $schoolYear->id,
            'status' => EnrollmentPeriodStatus::ACTIVE,
        ]);
        expect($activePeriod->status)->toBe(EnrollmentPeriodStatus::ACTIVE);
        expect($activePeriod->isActive())->toBeTrue();

        // Test UPCOMING status
        $upcomingPeriod = EnrollmentPeriod::factory()->create([
            'school_year_id' => $schoolYear->id,
            'status' => EnrollmentPeriodStatus::UPCOMING,
        ]);
        expect($upcomingPeriod->status)->toBe(EnrollmentPeriodStatus::UPCOMING);
        expect($upcomingPeriod->isActive())->toBeFalse();

        // Test CLOSED status
        $closedPeriod = EnrollmentPeriod::factory()->create([
            'school_year_id' => $schoolYear->id,
            'status' => EnrollmentPeriodStatus::CLOSED,
        ]);
        expect($closedPeriod->status)->toBe(EnrollmentPeriodStatus::CLOSED);
        expect($closedPeriod->isActive())->toBeFalse();
    })->group('enrollment-period', 'migration');

    test('enrollment period boolean fields default correctly', function () {
        $schoolYear = SchoolYear::factory()->create();

        $period = EnrollmentPeriod::factory()->create([
            'school_year_id' => $schoolYear->id,
        ]);

        expect($period->allow_new_students)->toBeTrue();
        expect($period->allow_returning_students)->toBeTrue();
    })->group('enrollment-period', 'migration');

    test('enrollment period nullable fields work correctly', function () {
        $schoolYear = SchoolYear::factory()->create();

        $period = EnrollmentPeriod::create([
            'school_year_id' => $schoolYear->id,
            'start_date' => '2025-06-01',
            'end_date' => '2026-05-31',
            'regular_registration_deadline' => '2025-08-31',
            'status' => EnrollmentPeriodStatus::UPCOMING,
            // Omit nullable fields
        ]);

        expect($period->exists())->toBeTrue();
        expect($period->early_registration_deadline)->toBeNull();
        expect($period->late_registration_deadline)->toBeNull();
        expect($period->description)->toBeNull();
    })->group('enrollment-period', 'migration');

    test('enrollment period scope methods work correctly', function () {
        $schoolYear = SchoolYear::factory()->create();

        EnrollmentPeriod::factory()->active()->create(['school_year_id' => $schoolYear->id]);
        EnrollmentPeriod::factory()->upcoming()->create(['school_year_id' => $schoolYear->id]);
        EnrollmentPeriod::factory()->closed()->create(['school_year_id' => $schoolYear->id]);

        expect(EnrollmentPeriod::active()->count())->toBe(1);
        expect(EnrollmentPeriod::upcoming()->count())->toBe(1);
        expect(EnrollmentPeriod::closed()->count())->toBe(1);
    })->group('enrollment-period', 'migration');
});

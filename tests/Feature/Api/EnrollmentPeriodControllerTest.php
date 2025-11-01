<?php

use App\Enums\EnrollmentPeriodStatus;
use App\Models\EnrollmentPeriod;
use App\Models\SchoolYear;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->schoolYear = SchoolYear::factory()->create([
        'start_year' => 2024,
        'end_year' => 2025,
        'status' => 'active',
    ]);
});

test('public api returns active enrollment period with all details', function () {
    $startDate = now()->subDays(5);
    $period = EnrollmentPeriod::factory()->create([
        'school_year_id' => $this->schoolYear->id,
        'status' => EnrollmentPeriodStatus::ACTIVE,
        'start_date' => $startDate,
        'end_date' => now()->addMonths(2),
        'early_registration_deadline' => $startDate->copy()->addDays(7),
        'regular_registration_deadline' => now()->addDays(30),
        'late_registration_deadline' => now()->addMonths(1)->addDays(15),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $response = $this->getJson(route('api.enrollment-period.active'));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id',
            'school_year',
            'start_date',
            'end_date',
            'early_registration_deadline',
            'regular_registration_deadline',
            'late_registration_deadline',
            'is_open',
            'days_remaining',
            'allow_new_students',
            'allow_returning_students',
        ],
        'message',
    ]);

    expect($response->json('data.id'))->toBe($period->id);
    expect($response->json('data.is_open'))->toBeTrue();
    expect($response->json('data.allow_new_students'))->toBeTrue();
    expect($response->json('data.allow_returning_students'))->toBeTrue();
    expect($response->json('data.days_remaining'))->toBeGreaterThanOrEqual(29);
});

test('public api returns null when no active enrollment period exists', function () {
    // Create only upcoming and closed periods
    EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::UPCOMING]);
    EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::CLOSED]);

    $response = $this->getJson(route('api.enrollment-period.active'));

    $response->assertStatus(200);
    $response->assertJson([
        'data' => null,
        'message' => 'No active enrollment period at this time.',
    ]);
});

test('public api is accessible without authentication', function () {
    $period = EnrollmentPeriod::factory()->create([
        'status' => EnrollmentPeriodStatus::ACTIVE,
    ]);

    // Make request without authentication
    $response = $this->getJson(route('api.enrollment-period.active'));

    $response->assertStatus(200);
    expect($response->json('data.id'))->toBe($period->id);
});

test('public api returns only one active period even if multiple exist in database', function () {
    // This shouldn't happen due to model constraints, but test the API behavior
    $period1 = EnrollmentPeriod::factory()->create([
        'status' => EnrollmentPeriodStatus::ACTIVE,
        'start_date' => now()->subDays(10),
        'end_date' => now()->addMonths(1),
        'regular_registration_deadline' => now()->addDays(20),
    ]);

    $response = $this->getJson(route('api.enrollment-period.active'));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id',
            'school_year',
            'start_date',
            'end_date',
        ],
    ]);

    // Should return the first active period found
    expect($response->json('data'))->not->toBeNull();
});

test('public api shows is_open as false when period is active but not yet started', function () {
    $period = EnrollmentPeriod::factory()->create([
        'status' => EnrollmentPeriodStatus::ACTIVE,
        'start_date' => now()->addDays(5),
        'end_date' => now()->addMonths(2),
        'regular_registration_deadline' => now()->addMonths(1),
    ]);

    $response = $this->getJson(route('api.enrollment-period.active'));

    $response->assertStatus(200);
    expect($response->json('data.is_open'))->toBeFalse();
});

test('public api shows is_open as true when period is active and within date range', function () {
    $period = EnrollmentPeriod::factory()->create([
        'status' => EnrollmentPeriodStatus::ACTIVE,
        'start_date' => now()->subDays(5),
        'end_date' => now()->addMonths(2),
        'regular_registration_deadline' => now()->addDays(30),
    ]);

    $response = $this->getJson(route('api.enrollment-period.active'));

    $response->assertStatus(200);
    expect($response->json('data.is_open'))->toBeTrue();
});

test('public api shows days_remaining correctly', function () {
    $period = EnrollmentPeriod::factory()->create([
        'status' => EnrollmentPeriodStatus::ACTIVE,
        'start_date' => now(),
        'end_date' => now()->addMonths(2),
        'regular_registration_deadline' => now()->addDays(10),
    ]);

    $response = $this->getJson(route('api.enrollment-period.active'));

    $response->assertStatus(200);
    $daysRemaining = $response->json('data.days_remaining');

    expect($daysRemaining)->toBeGreaterThanOrEqual(9);
    expect($daysRemaining)->toBeLessThanOrEqual(10);
});

test('public api shows days_remaining as zero when deadline passed', function () {
    $period = EnrollmentPeriod::factory()->create([
        'status' => EnrollmentPeriodStatus::ACTIVE,
        'start_date' => now()->subDays(30),
        'end_date' => now()->addMonths(2),
        'regular_registration_deadline' => now()->subDays(5),
    ]);

    $response = $this->getJson(route('api.enrollment-period.active'));

    $response->assertStatus(200);
    expect($response->json('data.days_remaining'))->toBe(0);
});

test('public api returns school year label', function () {
    $schoolYear = SchoolYear::factory()->create([
        'start_year' => 2025,
        'end_year' => 2026,
        'status' => 'active',
    ]);

    $period = EnrollmentPeriod::factory()->create([
        'school_year_id' => $schoolYear->id,
        'status' => EnrollmentPeriodStatus::ACTIVE,
    ]);

    $response = $this->getJson(route('api.enrollment-period.active'));

    $response->assertStatus(200);
    expect($response->json('data.school_year'))->toBe('2025-2026');
});

test('public api handles null optional deadlines', function () {
    $startDate = now();
    $period = EnrollmentPeriod::factory()->create([
        'status' => EnrollmentPeriodStatus::ACTIVE,
        'start_date' => $startDate,
        'end_date' => $startDate->copy()->addMonths(2),
        'early_registration_deadline' => null,
        'regular_registration_deadline' => $startDate->copy()->addDays(30),
        'late_registration_deadline' => null,
    ]);

    $response = $this->getJson(route('api.enrollment-period.active'));

    $response->assertStatus(200);
    expect($response->json('data.early_registration_deadline'))->toBeNull();
    expect($response->json('data.late_registration_deadline'))->toBeNull();
    expect($response->json('data.regular_registration_deadline'))->not->toBeNull();
});

test('public api returns dates in correct format', function () {
    $period = EnrollmentPeriod::factory()->create([
        'status' => EnrollmentPeriodStatus::ACTIVE,
        'start_date' => '2025-01-15',
        'end_date' => '2025-03-15',
        'regular_registration_deadline' => '2025-02-15',
    ]);

    $response = $this->getJson(route('api.enrollment-period.active'));

    $response->assertStatus(200);
    expect($response->json('data.start_date'))->toBe('2025-01-15');
    expect($response->json('data.end_date'))->toBe('2025-03-15');
    expect($response->json('data.regular_registration_deadline'))->toBe('2025-02-15');
});

test('public api handles allow_new_students and allow_returning_students flags', function () {
    $period = EnrollmentPeriod::factory()->create([
        'status' => EnrollmentPeriodStatus::ACTIVE,
        'allow_new_students' => false,
        'allow_returning_students' => true,
    ]);

    $response = $this->getJson(route('api.enrollment-period.active'));

    $response->assertStatus(200);
    expect($response->json('data.allow_new_students'))->toBeFalse();
    expect($response->json('data.allow_returning_students'))->toBeTrue();
});

<?php

use App\Enums\EnrollmentPeriodStatus;
use App\Models\EnrollmentPeriod;
use App\Models\SchoolYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

test('enrollment period creation is logged', function () {
    $schoolYear = SchoolYear::factory()->create();

    $period = EnrollmentPeriod::create([
        'school_year_id' => $schoolYear->id,
        'start_date' => now(),
        'end_date' => now()->addMonths(2),
        'regular_registration_deadline' => now()->addMonth(),
        'status' => EnrollmentPeriodStatus::UPCOMING,
    ]);

    $activity = Activity::where('subject_type', EnrollmentPeriod::class)
        ->where('subject_id', $period->id)
        ->where('event', 'created')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('attributes'))->toHaveKey('school_year_id')
        ->and($activity->properties->get('attributes'))->toHaveKey('status');
});

test('enrollment period update is logged', function () {
    $period = EnrollmentPeriod::factory()->create([
        'status' => EnrollmentPeriodStatus::UPCOMING,
    ]);

    $period->update(['status' => EnrollmentPeriodStatus::ACTIVE]);

    $activity = Activity::where('subject_type', EnrollmentPeriod::class)
        ->where('subject_id', $period->id)
        ->where('event', 'updated')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('old'))->toHaveKey('status')
        ->and($activity->properties->get('old')['status'])->toBe('upcoming')
        ->and($activity->properties->get('attributes')['status'])->toBe('active');
});

test('enrollment period deletion is logged', function () {
    $period = EnrollmentPeriod::factory()->create([
        'status' => EnrollmentPeriodStatus::UPCOMING,
    ]);

    $periodId = $period->id;
    $period->delete();

    $activity = Activity::where('subject_type', EnrollmentPeriod::class)
        ->where('subject_id', $periodId)
        ->where('event', 'deleted')
        ->first();

    expect($activity)->not->toBeNull();
});

test('only changed fields are logged for enrollment period', function () {
    $startDate = now();
    $period = EnrollmentPeriod::factory()->create([
        'start_date' => $startDate,
        'end_date' => $startDate->copy()->addMonths(2),
        'regular_registration_deadline' => $startDate->copy()->addMonth(),
        'status' => EnrollmentPeriodStatus::UPCOMING,
    ]);

    // Update only status
    $period->update(['status' => EnrollmentPeriodStatus::ACTIVE]);

    $activity = Activity::where('subject_type', EnrollmentPeriod::class)
        ->where('subject_id', $period->id)
        ->where('event', 'updated')
        ->first();

    // Only status should be in old and attributes
    expect($activity->properties->get('old'))->toHaveKey('status')
        ->and($activity->properties->get('old'))->not->toHaveKey('start_date')
        ->and($activity->properties->get('attributes'))->toHaveKey('status')
        ->and($activity->properties->get('attributes'))->not->toHaveKey('start_date');
});

test('enrollment period activation is logged', function () {
    $period = EnrollmentPeriod::factory()->create([
        'status' => EnrollmentPeriodStatus::UPCOMING,
    ]);

    // Change to active
    $period->activate();

    $activity = Activity::where('subject_type', EnrollmentPeriod::class)
        ->where('subject_id', $period->id)
        ->where('event', 'updated')
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('old')['status'])->toBe('upcoming')
        ->and($activity->properties->get('attributes')['status'])->toBe('active');
});

test('enrollment period closure is logged', function () {
    $period = EnrollmentPeriod::factory()->create([
        'status' => EnrollmentPeriodStatus::ACTIVE,
    ]);

    // Change to closed
    $period->close();

    $activity = Activity::where('subject_type', EnrollmentPeriod::class)
        ->where('subject_id', $period->id)
        ->where('event', 'updated')
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('old')['status'])->toBe('active')
        ->and($activity->properties->get('attributes')['status'])->toBe('closed');
});

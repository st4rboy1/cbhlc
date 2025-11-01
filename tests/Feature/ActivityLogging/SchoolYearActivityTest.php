<?php

use App\Models\SchoolYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

test('school year creation is logged', function () {
    $schoolYear = SchoolYear::create([
        'name' => '2024-2025',
        'start_year' => 2024,
        'end_year' => 2025,
        'start_date' => '2024-06-01',
        'end_date' => '2025-03-31',
        'status' => 'upcoming',
        'is_active' => false,
    ]);

    $activity = Activity::where('subject_type', SchoolYear::class)
        ->where('subject_id', $schoolYear->id)
        ->where('event', 'created')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('attributes'))->toHaveKey('name')
        ->and($activity->properties->get('attributes'))->toHaveKey('start_year')
        ->and($activity->properties->get('attributes'))->toHaveKey('status');
});

test('school year update is logged', function () {
    $schoolYear = SchoolYear::factory()->create([
        'status' => 'upcoming',
        'is_active' => false,
    ]);

    $schoolYear->update([
        'status' => 'active',
        'is_active' => true,
    ]);

    $activity = Activity::where('subject_type', SchoolYear::class)
        ->where('subject_id', $schoolYear->id)
        ->where('event', 'updated')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('old'))->toHaveKey('status')
        ->and($activity->properties->get('old'))->toHaveKey('is_active')
        ->and($activity->properties->get('old')['status'])->toBe('upcoming')
        ->and($activity->properties->get('attributes')['status'])->toBe('active');
});

test('school year deletion is logged', function () {
    $schoolYear = SchoolYear::factory()->create();

    $schoolYearId = $schoolYear->id;
    $schoolYear->delete();

    $activity = Activity::where('subject_type', SchoolYear::class)
        ->where('subject_id', $schoolYearId)
        ->where('event', 'deleted')
        ->first();

    expect($activity)->not->toBeNull();
});

test('only changed fields are logged for school year', function () {
    $schoolYear = SchoolYear::factory()->create([
        'name' => '2024-2025',
        'status' => 'upcoming',
    ]);

    // Update only status
    $schoolYear->update(['status' => 'active']);

    $activity = Activity::where('subject_type', SchoolYear::class)
        ->where('subject_id', $schoolYear->id)
        ->where('event', 'updated')
        ->first();

    // Only status should be in old and attributes
    expect($activity->properties->get('old'))->toHaveKey('status')
        ->and($activity->properties->get('old'))->not->toHaveKey('name')
        ->and($activity->properties->get('attributes'))->toHaveKey('status')
        ->and($activity->properties->get('attributes'))->not->toHaveKey('name');
});

test('school year activation is logged', function () {
    $schoolYear = SchoolYear::factory()->create([
        'is_active' => false,
        'status' => 'upcoming',
    ]);

    $schoolYear->setAsActive();

    $activity = Activity::where('subject_type', SchoolYear::class)
        ->where('subject_id', $schoolYear->id)
        ->where('event', 'updated')
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('old')['is_active'])->toBeFalse()
        ->and($activity->properties->get('attributes')['is_active'])->toBeTrue()
        ->and($activity->properties->get('attributes')['status'])->toBe('active');
});

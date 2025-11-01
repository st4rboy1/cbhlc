<?php

use App\Enums\EnrollmentPeriodStatus;
use App\Models\EnrollmentPeriod;
use App\Models\SchoolYear;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->schoolYear = SchoolYear::factory()->create();
});

test('enrollment period model can be instantiated', function () {
    $period = new EnrollmentPeriod;

    expect($period)->toBeInstanceOf(EnrollmentPeriod::class);
});

test('enrollment period has required fillable fields', function () {
    $startDate = now();
    $endDate = now()->addMonths(2);

    $period = EnrollmentPeriod::factory()->create([
        'school_year_id' => $this->schoolYear->id,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'regular_registration_deadline' => $startDate->copy()->addMonth(),
        'status' => EnrollmentPeriodStatus::ACTIVE,
    ]);

    expect($period->school_year_id)->toBe($this->schoolYear->id)
        ->and($period->start_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($period->end_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($period->status)->toBe(EnrollmentPeriodStatus::ACTIVE);
});

test('enrollment period status enum casts properly', function () {
    $period = EnrollmentPeriod::factory()->create([
        'status' => EnrollmentPeriodStatus::ACTIVE,
    ]);

    expect($period->status)->toBeInstanceOf(EnrollmentPeriodStatus::class)
        ->and($period->status)->toBe(EnrollmentPeriodStatus::ACTIVE);
});

test('enrollment period dates cast to Carbon instances', function () {
    $period = EnrollmentPeriod::factory()->create([
        'start_date' => now(),
        'end_date' => now()->addMonths(2),
        'early_registration_deadline' => now()->addDays(7),
        'regular_registration_deadline' => now()->addMonths(1),
        'late_registration_deadline' => now()->addMonths(1)->addDays(14),
    ]);

    expect($period->start_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($period->end_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($period->early_registration_deadline)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($period->regular_registration_deadline)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($period->late_registration_deadline)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('active scope filters active periods', function () {
    // Create non-active periods first
    EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::UPCOMING]);
    EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::CLOSED]);
    // Create one active period (boot() will ensure only one active at a time)
    EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::ACTIVE]);

    $activePeriods = EnrollmentPeriod::active()->get();

    expect($activePeriods)->toHaveCount(1)
        ->and($activePeriods->every(fn ($p) => $p->status === EnrollmentPeriodStatus::ACTIVE))->toBeTrue();
});

test('upcoming scope filters upcoming periods', function () {
    EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::ACTIVE]);
    EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::UPCOMING]);
    EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::CLOSED]);
    EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::UPCOMING]);

    $upcomingPeriods = EnrollmentPeriod::upcoming()->get();

    expect($upcomingPeriods)->toHaveCount(2)
        ->and($upcomingPeriods->every(fn ($p) => $p->status === EnrollmentPeriodStatus::UPCOMING))->toBeTrue();
});

test('closed scope filters closed periods', function () {
    EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::ACTIVE]);
    EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::UPCOMING]);
    EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::CLOSED]);
    EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::CLOSED]);

    $closedPeriods = EnrollmentPeriod::closed()->get();

    expect($closedPeriods)->toHaveCount(2)
        ->and($closedPeriods->every(fn ($p) => $p->status === EnrollmentPeriodStatus::CLOSED))->toBeTrue();
});

test('isActive method returns true for active periods', function () {
    $activePeriod = EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::ACTIVE]);
    $upcomingPeriod = EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::UPCOMING]);

    expect($activePeriod->isActive())->toBeTrue()
        ->and($upcomingPeriod->isActive())->toBeFalse();
});

test('isOpen method returns true when period is active and within date range', function () {
    $startDate1 = now()->subDays(5);
    $openPeriod = EnrollmentPeriod::factory()->create([
        'status' => EnrollmentPeriodStatus::ACTIVE,
        'start_date' => $startDate1,
        'end_date' => now()->addDays(5),
        'regular_registration_deadline' => $startDate1->copy()->addDays(3),
    ]);

    $startDate2 = now()->addDays(5);
    $futurePeriod = EnrollmentPeriod::factory()->create([
        'status' => EnrollmentPeriodStatus::ACTIVE,
        'start_date' => $startDate2,
        'end_date' => now()->addDays(15),
        'regular_registration_deadline' => $startDate2->copy()->addDays(5),
    ]);

    expect($openPeriod->isOpen())->toBeTrue()
        ->and($futurePeriod->isOpen())->toBeFalse();
});

test('getDaysRemaining returns correct days for active period', function () {
    $startDate = now();
    $period = EnrollmentPeriod::factory()->create([
        'status' => EnrollmentPeriodStatus::ACTIVE,
        'start_date' => $startDate,
        'end_date' => $startDate->copy()->addMonths(2),
        'regular_registration_deadline' => now()->addDays(10),
    ]);

    $daysRemaining = $period->getDaysRemaining();
    expect($daysRemaining)->toBeGreaterThanOrEqual(9)
        ->and($daysRemaining)->toBeLessThanOrEqual(10);
});

test('getDaysRemaining returns zero for non-active periods', function () {
    $startDate = now();

    $period = EnrollmentPeriod::factory()->create([
        'status' => EnrollmentPeriodStatus::UPCOMING,
        'start_date' => $startDate,
        'end_date' => $startDate->copy()->addMonths(2),
        'regular_registration_deadline' => $startDate->copy()->addDays(10),
    ]);

    expect($period->getDaysRemaining())->toBe(0);
});

test('getDaysRemaining returns zero for past deadlines', function () {
    $startDate = now()->subDays(10);

    $period = EnrollmentPeriod::factory()->create([
        'status' => EnrollmentPeriodStatus::ACTIVE,
        'start_date' => $startDate,
        'end_date' => $startDate->copy()->addMonths(2),
        'regular_registration_deadline' => now()->subDays(5),
    ]);

    expect($period->getDaysRemaining())->toBe(0);
});

test('activate method sets period to active and closes other active periods', function () {
    $period1 = EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::ACTIVE]);
    $period2 = EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::UPCOMING]);

    $result = $period2->activate();

    expect($result)->toBeTrue();
    $period1->refresh();
    $period2->refresh();

    expect($period2->status)->toBe(EnrollmentPeriodStatus::ACTIVE)
        ->and($period1->status)->toBe(EnrollmentPeriodStatus::CLOSED);
});

test('activate method returns true if already active', function () {
    $period = EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::ACTIVE]);

    $result = $period->activate();

    expect($result)->toBeTrue()
        ->and($period->status)->toBe(EnrollmentPeriodStatus::ACTIVE);
});

test('close method sets period to closed', function () {
    $period = EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::ACTIVE]);

    $result = $period->close();

    expect($result)->toBeTrue();
    $period->refresh();

    expect($period->status)->toBe(EnrollmentPeriodStatus::CLOSED);
});

test('close method returns true if already closed', function () {
    $period = EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::CLOSED]);

    $result = $period->close();

    expect($result)->toBeTrue()
        ->and($period->status)->toBe(EnrollmentPeriodStatus::CLOSED);
});

test('enrollment period belongs to school year', function () {
    $period = EnrollmentPeriod::factory()->create(['school_year_id' => $this->schoolYear->id]);

    expect($period->schoolYear)->toBeInstanceOf(SchoolYear::class)
        ->and($period->schoolYear->id)->toBe($this->schoolYear->id);
});

test('enrollment period has many enrollments', function () {
    $period = EnrollmentPeriod::factory()->create();

    expect($period->enrollments())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('enrollment period has many grade level fees', function () {
    $period = EnrollmentPeriod::factory()->create();

    expect($period->gradeLevelFees())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('saving period with end date before start date throws exception', function () {
    expect(fn () => EnrollmentPeriod::factory()->create([
        'start_date' => now(),
        'end_date' => now()->subDays(1),
    ]))->toThrow(\InvalidArgumentException::class, 'End date must be after start date.');
});

test('saving period with registration deadline before start date throws exception', function () {
    expect(fn () => EnrollmentPeriod::factory()->create([
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(20),
        'regular_registration_deadline' => now(),
    ]))->toThrow(\InvalidArgumentException::class, 'Registration deadline must be within period dates.');
});

test('saving active period closes other active periods automatically', function () {
    $period1 = EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::ACTIVE]);
    $period2 = EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::UPCOMING]);

    $period2->update(['status' => EnrollmentPeriodStatus::ACTIVE]);

    $period1->refresh();

    expect($period1->status)->toBe(EnrollmentPeriodStatus::CLOSED)
        ->and($period2->status)->toBe(EnrollmentPeriodStatus::ACTIVE);
});

test('boolean fields cast correctly', function () {
    $period = EnrollmentPeriod::factory()->create([
        'allow_new_students' => true,
        'allow_returning_students' => false,
    ]);

    expect($period->allow_new_students)->toBeBool()->toBeTrue()
        ->and($period->allow_returning_students)->toBeBool()->toBeFalse();
});

<?php

use App\Enums\EnrollmentPeriodStatus;
use App\Models\EnrollmentPeriod;
use App\Models\User;
use App\Notifications\EnrollmentPeriodStatusChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();

    // Create roles for testing
    Role::create(['name' => 'super_admin']);
    Role::create(['name' => 'administrator']);

    // Create school years
    $this->sy2024 = \App\Models\SchoolYear::firstOrCreate([
        'name' => '2024-2025',
        'start_year' => 2024,
        'end_year' => 2025,
        'start_date' => '2024-06-01',
        'end_date' => '2025-05-31',
        'status' => 'active',
    ]);

    $this->sy2025 = \App\Models\SchoolYear::firstOrCreate([
        'name' => '2025-2026',
        'start_year' => 2025,
        'end_year' => 2026,
        'start_date' => '2025-06-01',
        'end_date' => '2026-05-31',
        'status' => 'upcoming',
    ]);

    $this->sy2026 = \App\Models\SchoolYear::firstOrCreate([
        'name' => '2026-2027',
        'start_year' => 2026,
        'end_year' => 2027,
        'start_date' => '2026-06-01',
        'end_date' => '2027-05-31',
        'status' => 'upcoming',
    ]);
});

test('command activates upcoming periods when start date is reached', function () {
    $period = EnrollmentPeriod::create([
        'school_year_id' => $this->sy2025->id,
        'status' => 'upcoming',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
        'early_registration_deadline' => now()->addDays(10),
        'regular_registration_deadline' => now()->addDays(20),
        'late_registration_deadline' => now()->addMonth()->subDays(5),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    Artisan::call('enrollment-periods:update-status');

    $period->refresh();
    expect($period->status)->toBe(EnrollmentPeriodStatus::ACTIVE);
});

test('command closes active periods when end date is passed', function () {
    $period = EnrollmentPeriod::create([
        'school_year_id' => $this->sy2024->id,
        'status' => 'active',
        'start_date' => now()->subMonth(),
        'end_date' => now()->subDay(),
        'early_registration_deadline' => now()->subMonth()->addDays(10),
        'regular_registration_deadline' => now()->subMonth()->addDays(20),
        'late_registration_deadline' => now()->subDays(2),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    Artisan::call('enrollment-periods:update-status');

    $period->refresh();
    expect($period->status)->toBe(EnrollmentPeriodStatus::CLOSED);
});

test('command does not change periods that are not ready', function () {
    $upcomingPeriod = EnrollmentPeriod::create([
        'school_year_id' => $this->sy2026->id,
        'status' => 'upcoming',
        'start_date' => now()->addWeek(),
        'end_date' => now()->addMonth(),
        'early_registration_deadline' => now()->addWeek()->addDays(10),
        'regular_registration_deadline' => now()->addWeek()->addDays(20),
        'late_registration_deadline' => now()->addMonth()->subDays(5),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $activePeriod = EnrollmentPeriod::create([
        'school_year_id' => $this->sy2025->id,
        'status' => 'active',
        'start_date' => now()->subWeek(),
        'end_date' => now()->addWeek(),
        'early_registration_deadline' => now()->subWeek()->addDays(2),
        'regular_registration_deadline' => now()->subWeek()->addDays(4),
        'late_registration_deadline' => now()->addWeek()->subDays(1),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    Artisan::call('enrollment-periods:update-status');

    $upcomingPeriod->refresh();
    $activePeriod->refresh();

    expect($upcomingPeriod->status)->toBe(EnrollmentPeriodStatus::UPCOMING);
    expect($activePeriod->status)->toBe(EnrollmentPeriodStatus::ACTIVE);
});

test('command closes previously active periods when activating new period', function () {
    $oldPeriod = EnrollmentPeriod::create([
        'school_year_id' => $this->sy2024->id,
        'status' => 'active',
        'start_date' => now()->subMonth(),
        'end_date' => now()->addWeek(),
        'early_registration_deadline' => now()->subMonth()->addDays(10),
        'regular_registration_deadline' => now()->subMonth()->addDays(20),
        'late_registration_deadline' => now()->addWeek()->subDays(2),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $newPeriod = EnrollmentPeriod::create([
        'school_year_id' => $this->sy2025->id,
        'status' => 'upcoming',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
        'early_registration_deadline' => now()->addDays(10),
        'regular_registration_deadline' => now()->addDays(20),
        'late_registration_deadline' => now()->addMonth()->subDays(5),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    Artisan::call('enrollment-periods:update-status');

    $oldPeriod->refresh();
    $newPeriod->refresh();

    expect($oldPeriod->status)->toBe(EnrollmentPeriodStatus::CLOSED);
    expect($newPeriod->status)->toBe(EnrollmentPeriodStatus::ACTIVE);
});

test('command runs successfully with dry-run option', function () {
    $period = EnrollmentPeriod::create([
        'school_year_id' => $this->sy2025->id,
        'status' => 'upcoming',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
        'early_registration_deadline' => now()->addDays(10),
        'regular_registration_deadline' => now()->addDays(20),
        'late_registration_deadline' => now()->addMonth()->subDays(5),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    Artisan::call('enrollment-periods:update-status', ['--dry-run' => true]);

    $period->refresh();
    expect($period->status)->toBe(EnrollmentPeriodStatus::UPCOMING);
});

test('command sends notifications when notify option is used', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    $admin = User::factory()->create();
    $admin->assignRole('administrator');

    EnrollmentPeriod::create([
        'school_year_id' => $this->sy2025->id,
        'status' => 'upcoming',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
        'early_registration_deadline' => now()->addDays(10),
        'regular_registration_deadline' => now()->addDays(20),
        'late_registration_deadline' => now()->addMonth()->subDays(5),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    Artisan::call('enrollment-periods:update-status', ['--notify' => true]);

    Notification::assertSentTo(
        [$superAdmin, $admin],
        EnrollmentPeriodStatusChangedNotification::class
    );
});

test('command does not send notifications without notify option', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    EnrollmentPeriod::create([
        'school_year_id' => $this->sy2025->id,
        'status' => 'upcoming',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
        'early_registration_deadline' => now()->addDays(10),
        'regular_registration_deadline' => now()->addDays(20),
        'late_registration_deadline' => now()->addMonth()->subDays(5),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    Artisan::call('enrollment-periods:update-status');

    Notification::assertNothingSent();
});

test('command logs activity for activated periods', function () {
    $period = EnrollmentPeriod::create([
        'school_year_id' => $this->sy2025->id,
        'status' => 'upcoming',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
        'early_registration_deadline' => now()->addDays(10),
        'regular_registration_deadline' => now()->addDays(20),
        'late_registration_deadline' => now()->addMonth()->subDays(5),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    Artisan::call('enrollment-periods:update-status');

    assertDatabaseHas('activity_log', [
        'subject_type' => EnrollmentPeriod::class,
        'subject_id' => $period->id,
        'description' => 'Enrollment period automatically activated',
    ]);
});

test('command logs activity for closed periods', function () {
    $period = EnrollmentPeriod::create([
        'school_year_id' => $this->sy2024->id,
        'status' => 'active',
        'start_date' => now()->subMonth(),
        'end_date' => now()->subDay(),
        'early_registration_deadline' => now()->subMonth()->addDays(10),
        'regular_registration_deadline' => now()->subMonth()->addDays(20),
        'late_registration_deadline' => now()->subDays(2),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    Artisan::call('enrollment-periods:update-status');

    assertDatabaseHas('activity_log', [
        'subject_type' => EnrollmentPeriod::class,
        'subject_id' => $period->id,
        'description' => 'Enrollment period automatically closed',
    ]);
});

test('command returns success status code', function () {
    $exitCode = Artisan::call('enrollment-periods:update-status');

    expect($exitCode)->toBe(0);
});

test('command handles multiple periods correctly', function () {
    EnrollmentPeriod::create([
        'school_year_id' => $this->sy2025->id,
        'status' => 'upcoming',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
        'early_registration_deadline' => now()->addDays(10),
        'regular_registration_deadline' => now()->addDays(20),
        'late_registration_deadline' => now()->addMonth()->subDays(5),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    EnrollmentPeriod::create([
        'school_year_id' => $this->sy2026->id,
        'status' => 'upcoming',
        'start_date' => now()->subHours(2),
        'end_date' => now()->addMonths(2),
        'early_registration_deadline' => now()->addDays(15),
        'regular_registration_deadline' => now()->addDays(25),
        'late_registration_deadline' => now()->addMonths(2)->subDays(5),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $toClose = EnrollmentPeriod::create([
        'school_year_id' => $this->sy2024->id,
        'status' => 'active',
        'start_date' => now()->subMonth(),
        'end_date' => now()->subDay(),
        'early_registration_deadline' => now()->subMonth()->addDays(10),
        'regular_registration_deadline' => now()->subMonth()->addDays(20),
        'late_registration_deadline' => now()->subDays(2),
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    Artisan::call('enrollment-periods:update-status');

    $toClose->refresh();
    expect($toClose->status)->toBe(EnrollmentPeriodStatus::CLOSED);

    $activeCount = EnrollmentPeriod::where('status', 'active')->count();
    expect($activeCount)->toBeGreaterThanOrEqual(1);
});

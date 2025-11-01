<?php

use App\Enums\EnrollmentPeriodStatus;
use App\Models\Enrollment;
use App\Models\EnrollmentPeriod;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles
    Role::create(['name' => 'administrator']);
    Role::create(['name' => 'registrar']);
    Role::create(['name' => 'guardian']);

    // Create administrator user for admin routes
    $this->admin = User::factory()->create();
    $this->admin->assignRole('administrator');

    // Create school year
    $this->schoolYear = SchoolYear::factory()->create([
        'start_year' => 2024,
        'end_year' => 2025,
        'status' => 'active',
    ]);
});

test('admin can view enrollment periods index', function () {
    EnrollmentPeriod::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)->get(route('admin.enrollment-periods.index'));

    $response->assertStatus(200);
})->skip('Frontend pages not yet implemented (#477)');

test('admin can view create enrollment period form', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.enrollment-periods.create'));

    $response->assertStatus(200);
})->skip('Frontend pages not yet implemented (#477)');

test('admin can create enrollment period', function () {
    $data = [
        'school_year_id' => $this->schoolYear->id,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonths(2)->toDateString(),
        'regular_registration_deadline' => now()->addMonth()->toDateString(),
        'status' => EnrollmentPeriodStatus::UPCOMING->value,
    ];

    $response = $this->actingAs($this->admin)->post(route('admin.enrollment-periods.store'), $data);

    $response->assertRedirect(route('admin.enrollment-periods.index'));
    $response->assertSessionHas('success');
    $this->assertDatabaseHas('enrollment_periods', [
        'school_year_id' => $this->schoolYear->id,
        'status' => EnrollmentPeriodStatus::UPCOMING->value,
    ]);
});

test('admin cannot create enrollment period with invalid data', function () {
    $data = [
        'school_year_id' => 999,
        'start_date' => 'invalid-date',
        'end_date' => now()->subDay()->toDateString(),
    ];

    $response = $this->actingAs($this->admin)->post(route('admin.enrollment-periods.store'), $data);

    $response->assertSessionHasErrors(['school_year_id', 'start_date']);
});

test('admin can view single enrollment period', function () {
    $period = EnrollmentPeriod::factory()->create();

    $response = $this->actingAs($this->admin)->get(route('admin.enrollment-periods.show', $period));

    $response->assertStatus(200);
})->skip('Frontend pages not yet implemented (#477)');

test('admin can view edit enrollment period form', function () {
    $period = EnrollmentPeriod::factory()->create();

    $response = $this->actingAs($this->admin)->get(route('admin.enrollment-periods.edit', $period));

    $response->assertStatus(200);
})->skip('Frontend pages not yet implemented (#477)');

test('admin can update enrollment period', function () {
    $period = EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::UPCOMING]);

    $newStartDate = now()->addDays(5);
    $data = [
        'school_year_id' => $period->school_year_id,
        'start_date' => $newStartDate->toDateString(),
        'end_date' => now()->addMonths(3)->toDateString(),
        'regular_registration_deadline' => now()->addMonths(2)->toDateString(),
        'status' => EnrollmentPeriodStatus::UPCOMING->value,
    ];

    $response = $this->actingAs($this->admin)->put(route('admin.enrollment-periods.update', $period), $data);

    $response->assertRedirect(route('admin.enrollment-periods.show', $period));
    $response->assertSessionHas('success');

    $period->refresh();
    expect($period->start_date->toDateString())->toBe($newStartDate->toDateString());
});

test('admin cannot delete active enrollment period', function () {
    $period = EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::ACTIVE]);

    $response = $this->actingAs($this->admin)->delete(route('admin.enrollment-periods.destroy', $period));

    $response->assertSessionHasErrors(['period']);
    $this->assertDatabaseHas('enrollment_periods', ['id' => $period->id]);
});

test('admin cannot delete period with existing enrollments', function () {
    $period = EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::UPCOMING]);
    Enrollment::factory()->create(['enrollment_period_id' => $period->id]);

    $response = $this->actingAs($this->admin)->delete(route('admin.enrollment-periods.destroy', $period));

    $response->assertSessionHasErrors(['period']);
    $this->assertDatabaseHas('enrollment_periods', ['id' => $period->id]);
});

test('admin can delete enrollment period without enrollments', function () {
    $period = EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::UPCOMING]);

    $response = $this->actingAs($this->admin)->delete(route('admin.enrollment-periods.destroy', $period));

    $response->assertRedirect(route('admin.enrollment-periods.index'));
    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('enrollment_periods', ['id' => $period->id]);
});

test('admin can activate enrollment period', function () {
    $period = EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::UPCOMING]);

    $response = $this->actingAs($this->admin)->post(route('admin.enrollment-periods.activate', $period));

    $response->assertSessionHas('success');
    $period->refresh();
    expect($period->status)->toBe(EnrollmentPeriodStatus::ACTIVE);
});

test('activating period closes other active periods', function () {
    $activePeriod = EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::ACTIVE]);
    $upcomingPeriod = EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::UPCOMING]);

    $this->actingAs($this->admin)->post(route('admin.enrollment-periods.activate', $upcomingPeriod));

    $activePeriod->refresh();
    $upcomingPeriod->refresh();

    expect($activePeriod->status)->toBe(EnrollmentPeriodStatus::CLOSED);
    expect($upcomingPeriod->status)->toBe(EnrollmentPeriodStatus::ACTIVE);
});

test('admin can close active enrollment period', function () {
    $period = EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::ACTIVE]);

    $response = $this->actingAs($this->admin)->post(route('admin.enrollment-periods.close', $period));

    $response->assertSessionHas('success');
    $period->refresh();
    expect($period->status)->toBe(EnrollmentPeriodStatus::CLOSED);
});

test('admin cannot close non-active period', function () {
    $period = EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::UPCOMING]);

    $response = $this->actingAs($this->admin)->post(route('admin.enrollment-periods.close', $period));

    $response->assertSessionHasErrors(['period']);
    $period->refresh();
    expect($period->status)->toBe(EnrollmentPeriodStatus::UPCOMING);
});

test('index shows active period separately', function () {
    $activePeriod = EnrollmentPeriod::factory()->create(['status' => EnrollmentPeriodStatus::ACTIVE]);
    EnrollmentPeriod::factory()->count(2)->create(['status' => EnrollmentPeriodStatus::UPCOMING]);

    $response = $this->actingAs($this->admin)->get(route('admin.enrollment-periods.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('activePeriod')
        ->where('activePeriod.id', $activePeriod->id)
    );
});

test('non-admin cannot access admin enrollment period routes', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');
    $period = EnrollmentPeriod::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.enrollment-periods.index'));
    $response->assertStatus(403);

    $response = $this->actingAs($user)->get(route('admin.enrollment-periods.create'));
    $response->assertStatus(403);

    $response = $this->actingAs($user)->post(route('admin.enrollment-periods.store'), []);
    $response->assertStatus(403);
});

test('enrollment periods are paginated', function () {
    EnrollmentPeriod::factory()->count(15)->create();

    $response = $this->actingAs($this->admin)->get(route('admin.enrollment-periods.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('periods.data', 10) // First page should have 10 items
        ->has('periods.links')
    );
});

test('enrollment periods are ordered by start date descending', function () {
    $olderStart = now()->subMonths(2);
    $older = EnrollmentPeriod::factory()->create([
        'start_date' => $olderStart,
        'end_date' => $olderStart->copy()->addMonths(2),
        'regular_registration_deadline' => $olderStart->copy()->addMonth(),
    ]);

    $newerStart = now();
    $newer = EnrollmentPeriod::factory()->create([
        'start_date' => $newerStart,
        'end_date' => $newerStart->copy()->addMonths(2),
        'regular_registration_deadline' => $newerStart->copy()->addMonth(),
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.enrollment-periods.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->where('periods.data.0.id', $newer->id)
        ->where('periods.data.1.id', $older->id)
    );
});

test('activity is logged when enrollment period is created', function () {
    $data = [
        'school_year_id' => $this->schoolYear->id,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonths(2)->toDateString(),
        'regular_registration_deadline' => now()->addMonth()->toDateString(),
        'status' => EnrollmentPeriodStatus::UPCOMING->value,
    ];

    $this->actingAs($this->admin)->post(route('admin.enrollment-periods.store'), $data);

    $this->assertDatabaseHas('activity_log', [
        'description' => 'Enrollment period created',
        'subject_type' => EnrollmentPeriod::class,
    ]);
});

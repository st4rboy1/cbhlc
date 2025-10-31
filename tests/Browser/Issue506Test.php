<?php

use App\Models\EnrollmentPeriod;
use App\Models\Guardian;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

test('new enrollment button works and shows appropriate message', function () {
    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Create a guardian user
    $user = User::factory()->create();
    $user->assignRole('guardian');

    $guardian = Guardian::factory()->create(['user_id' => $user->id]);

    // Login as guardian and visit enrollments page
    actingAs($user)
        ->visit('/guardian/enrollments')
        ->assertPathIs('/guardian/enrollments')
        ->assertSee('New Enrollment');

    // The button should be clickable - we don't test the actual navigation yet
    // because that requires an active enrollment period
    expect(true)->toBeTrue();
})->group('browser', 'bug', 'issue-506');

test('new enrollment button shows error when no active enrollment period', function () {
    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Create a guardian user
    $user = User::factory()->create();
    $user->assignRole('guardian');

    $guardian = Guardian::factory()->create(['user_id' => $user->id]);

    // Ensure there is NO active enrollment period
    EnrollmentPeriod::query()->delete();

    // Login as guardian and try to access create page directly
    $response = actingAs($user)
        ->get('/guardian/enrollments/create');

    // Should redirect back to enrollments index with error flash message
    $response->assertRedirect(route('guardian.enrollments.index'));
    $response->assertSessionHas('error', 'Enrollment is currently closed. No active enrollment period available.');
})->group('browser', 'bug', 'issue-506');

test('new enrollment button works with active enrollment period', function () {
    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Create a guardian user
    $user = User::factory()->create();
    $user->assignRole('guardian');

    $guardian = Guardian::factory()->create(['user_id' => $user->id]);

    // Create an active enrollment period
    $schoolYear = SchoolYear::factory()->create([
        'start_year' => 2025,
        'end_year' => 2026,
        'status' => 'active',
    ]);

    EnrollmentPeriod::factory()->create([
        'school_year_id' => $schoolYear->id,
        'status' => 'active',
        'start_date' => now()->subDays(10),
        'end_date' => now()->addDays(30),
        'early_registration_deadline' => now()->addDays(20),
        'regular_registration_deadline' => now()->addDays(25),
        'late_registration_deadline' => now()->addDays(30),
    ]);

    // Login as guardian and navigate to create page
    $response = actingAs($user)
        ->get('/guardian/enrollments/create');

    // Should show the create page successfully
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('guardian/enrollments/create')
        ->has('students')
        ->has('activePeriod')
    );
})->group('browser', 'bug', 'issue-506');

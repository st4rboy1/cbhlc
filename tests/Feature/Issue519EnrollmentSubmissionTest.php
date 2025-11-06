<?php

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\PaymentPlan;
use App\Enums\Quarter;
use App\Models\Enrollment;
use App\Models\EnrollmentPeriod;
use App\Models\GradeLevelFee;
use App\Models\Guardian;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

test('#519: enrollment submission succeeds even if notification fails', function () {
    // Fake events to verify EnrollmentCreated is dispatched
    Event::fake();

    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Create a guardian user
    $guardianUser = User::factory()->create();
    $guardianUser->assignRole('guardian');

    $guardian = Guardian::factory()->create(['user_id' => $guardianUser->id]);
    $student = Student::factory()->create();

    // Link student to guardian
    $guardian->children()->attach($student->id, [
        'relationship_type' => 'mother',
        'is_primary_contact' => true,
    ]);

    // Create active enrollment period
    $schoolYear = SchoolYear::factory()->create([
        'start_year' => 2024,
        'end_year' => 2025,
    ]);

    $enrollmentPeriod = EnrollmentPeriod::factory()->create([
        'school_year_id' => $schoolYear->id,
        'status' => \App\Enums\EnrollmentPeriodStatus::ACTIVE,
        'start_date' => now()->subDays(10),
        'end_date' => now()->addDays(30),
        'early_registration_deadline' => now()->addDays(15),
        'regular_registration_deadline' => now()->addDays(25),
        'late_registration_deadline' => now()->addDays(30),
    ]);

    // Create grade level fee
    GradeLevelFee::factory()->create([
        'grade_level' => GradeLevel::GRADE_1,
        'enrollment_period_id' => $enrollmentPeriod->id,
        'tuition_fee_cents' => 2000000, // 20,000 pesos
        'miscellaneous_fee_cents' => 500000, // 5,000 pesos
    ]);

    // Fake notifications to prevent actual email sending
    Notification::fake();

    // Attempt enrollment submission
    $response = $this->actingAs($guardianUser)
        ->post(route('guardian.enrollments.store'), [
            'student_id' => $student->id,
            'grade_level' => GradeLevel::GRADE_1->value,
            'quarter' => Quarter::FIRST->value,
            'payment_plan' => PaymentPlan::ANNUAL->value,
        ]);

    // Should redirect successfully (not 500 error)
    $response->assertRedirect(route('guardian.enrollments.index'));
    $response->assertSessionHas('success', 'Enrollment application submitted successfully. Please wait for approval.');

    // Verify enrollment was created
    $this->assertDatabaseHas('enrollments', [
        'student_id' => $student->id,
        'guardian_id' => $guardian->id,
        'school_year_id' => $schoolYear->id,
        'status' => EnrollmentStatus::PENDING->value,
    ]);

    // Verify enrollment exists in database
    $enrollment = Enrollment::where('student_id', $student->id)
        ->where('school_year_id', $schoolYear->id)
        ->first();

    expect($enrollment)->not->toBeNull();
    expect($enrollment->status)->toBe(EnrollmentStatus::PENDING);
})->group('browser', 'bug', 'issue-519');

test('#519: enrollment submission handles notification failures gracefully', function () {
    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Create users that will receive notifications
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    // Create a guardian user
    $guardianUser = User::factory()->create();
    $guardianUser->assignRole('guardian');

    $guardian = Guardian::factory()->create(['user_id' => $guardianUser->id]);
    $student = Student::factory()->create();

    // Link student to guardian
    $guardian->children()->attach($student->id, [
        'relationship_type' => 'father',
        'is_primary_contact' => true,
    ]);

    // Create active enrollment period
    $schoolYear = SchoolYear::factory()->create([
        'start_year' => 2024,
        'end_year' => 2025,
    ]);

    $enrollmentPeriod = EnrollmentPeriod::factory()->create([
        'school_year_id' => $schoolYear->id,
        'status' => \App\Enums\EnrollmentPeriodStatus::ACTIVE,
        'start_date' => now()->subDays(10),
        'end_date' => now()->addDays(30),
        'early_registration_deadline' => now()->addDays(15),
        'regular_registration_deadline' => now()->addDays(25),
        'late_registration_deadline' => now()->addDays(30),
    ]);

    // Create grade level fee
    GradeLevelFee::factory()->create([
        'grade_level' => GradeLevel::GRADE_1,
        'enrollment_period_id' => $enrollmentPeriod->id,
        'tuition_fee_cents' => 2000000,
        'miscellaneous_fee_cents' => 500000,
    ]);

    // Fake notifications to prevent actual email sending
    Notification::fake();

    // Attempt enrollment submission
    $response = $this->actingAs($guardianUser)
        ->post(route('guardian.enrollments.store'), [
            'student_id' => $student->id,
            'grade_level' => GradeLevel::GRADE_1->value,
            'quarter' => Quarter::FIRST->value,
            'payment_plan' => PaymentPlan::ANNUAL->value,
        ]);

    // Should redirect successfully even if notifications fail
    $response->assertRedirect(route('guardian.enrollments.index'));
    $response->assertSessionHas('success');

    // Verify EnrollmentCreated event was dispatched (which triggers all notifications)
    Event::assertDispatched(\App\Events\EnrollmentCreated::class, function ($event) use ($student) {
        return $event->enrollment->student_id === $student->id;
    });
})->group('browser', 'bug', 'issue-519');

test('#519: enrollment shows in guardian dashboard after submission', function () {
    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Create a guardian user
    $guardianUser = User::factory()->create();
    $guardianUser->assignRole('guardian');

    $guardian = Guardian::factory()->create(['user_id' => $guardianUser->id]);
    $student = Student::factory()->create();

    // Link student to guardian
    $guardian->children()->attach($student->id, [
        'relationship_type' => 'mother',
        'is_primary_contact' => true,
    ]);

    // Create active enrollment period
    $schoolYear = SchoolYear::factory()->create([
        'start_year' => 2024,
        'end_year' => 2025,
    ]);

    $enrollmentPeriod = EnrollmentPeriod::factory()->create([
        'school_year_id' => $schoolYear->id,
        'status' => \App\Enums\EnrollmentPeriodStatus::ACTIVE,
        'start_date' => now()->subDays(10),
        'end_date' => now()->addDays(30),
        'early_registration_deadline' => now()->addDays(15),
        'regular_registration_deadline' => now()->addDays(25),
        'late_registration_deadline' => now()->addDays(30),
    ]);

    // Create grade level fee
    GradeLevelFee::factory()->create([
        'grade_level' => GradeLevel::GRADE_1,
        'enrollment_period_id' => $enrollmentPeriod->id,
        'tuition_fee_cents' => 2000000,
        'miscellaneous_fee_cents' => 500000,
    ]);

    // Fake notifications
    Notification::fake();

    // Submit enrollment
    $this->actingAs($guardianUser)
        ->post(route('guardian.enrollments.store'), [
            'student_id' => $student->id,
            'grade_level' => GradeLevel::GRADE_1->value,
            'quarter' => Quarter::FIRST->value,
            'payment_plan' => PaymentPlan::ANNUAL->value,
        ]);

    // Check that enrollment appears in guardian's enrollment list
    $response = $this->actingAs($guardianUser)
        ->get(route('guardian.enrollments.index'));

    $response->assertSuccessful();

    // Get the enrollment from database to verify it was created
    $enrollment = Enrollment::where('student_id', $student->id)
        ->where('school_year_id', $schoolYear->id)
        ->first();

    expect($enrollment)->not->toBeNull();
    expect($enrollment->status)->toBe(EnrollmentStatus::PENDING);
})->group('browser', 'bug', 'issue-519');

test('#519: enrollment submission succeeds even when notification system throws exception', function () {
    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Create users that will receive notifications
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    // Create a guardian user
    $guardianUser = User::factory()->create();
    $guardianUser->assignRole('guardian');

    $guardian = Guardian::factory()->create(['user_id' => $guardianUser->id]);
    $student = Student::factory()->create();

    // Link student to guardian
    $guardian->children()->attach($student->id, [
        'relationship_type' => 'father',
        'is_primary_contact' => true,
    ]);

    // Create active enrollment period
    $schoolYear = SchoolYear::factory()->create([
        'start_year' => 2024,
        'end_year' => 2025,
    ]);

    $enrollmentPeriod = EnrollmentPeriod::factory()->create([
        'school_year_id' => $schoolYear->id,
        'status' => \App\Enums\EnrollmentPeriodStatus::ACTIVE,
        'start_date' => now()->subDays(10),
        'end_date' => now()->addDays(30),
        'early_registration_deadline' => now()->addDays(15),
        'regular_registration_deadline' => now()->addDays(25),
        'late_registration_deadline' => now()->addDays(30),
    ]);

    // Create grade level fee
    GradeLevelFee::factory()->create([
        'grade_level' => GradeLevel::GRADE_1,
        'enrollment_period_id' => $enrollmentPeriod->id,
        'tuition_fee_cents' => 2000000,
        'miscellaneous_fee_cents' => 500000,
    ]);

    // Notifications are now event-driven and queued, so they won't block enrollment creation
    // Even if mail server fails later, enrollment submission itself will succeed
    Event::fake();

    // Attempt enrollment submission - should succeed
    $response = $this->actingAs($guardianUser)
        ->post(route('guardian.enrollments.store'), [
            'student_id' => $student->id,
            'grade_level' => GradeLevel::GRADE_1->value,
            'quarter' => Quarter::FIRST->value,
            'payment_plan' => PaymentPlan::ANNUAL->value,
        ]);

    // Should redirect successfully because notifications are decoupled from enrollment creation
    $response->assertRedirect(route('guardian.enrollments.index'));
    $response->assertSessionHas('success', 'Enrollment application submitted successfully. Please wait for approval.');

    // Verify event was dispatched (notifications are triggered by this event)
    Event::assertDispatched(\App\Events\EnrollmentCreated::class);

    // Verify enrollment was still created despite notification failure
    $this->assertDatabaseHas('enrollments', [
        'student_id' => $student->id,
        'guardian_id' => $guardian->id,
        'school_year_id' => $schoolYear->id,
        'status' => EnrollmentStatus::PENDING->value,
    ]);
})->group('browser', 'bug', 'issue-519');

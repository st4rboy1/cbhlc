<?php

use App\Enums\EnrollmentPeriodStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\Quarter;
use App\Models\Enrollment;
use App\Models\EnrollmentPeriod;
use App\Models\GradeLevelFee;
use App\Models\Guardian;
use App\Models\GuardianStudent;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    // Create school years
    $this->sy2024 = \App\Models\SchoolYear::firstOrCreate([
        'name' => '2024-2025',
        'start_year' => 2024,
        'end_year' => 2025,
        'start_date' => '2024-06-01',
        'end_date' => '2025-05-31',
        'status' => 'active',
    ]);

    // Create guardian user
    $this->guardian = User::factory()->create();
    $this->guardian->assignRole('guardian');

    // Create guardian model
    $this->guardianModel = Guardian::create([
        'user_id' => $this->guardian->id,
        'first_name' => 'Test',
        'last_name' => 'Guardian',
        'contact_number' => '09123456789',
        'address' => '123 Test St',
    ]);

    // Create student
    $this->student = Student::factory()->create();

    // Link guardian to student
    GuardianStudent::create([
        'guardian_id' => $this->guardianModel->id,
        'student_id' => $this->student->id,
        'relationship_type' => 'mother',
        'is_primary_contact' => true,
    ]);

    // Create enrollment period for school year with 'upcoming' status
    $this->enrollmentPeriod = EnrollmentPeriod::create([
        'school_year_id' => $this->sy2024->id,
        'start_date' => '2024-06-01',
        'end_date' => '2025-05-31',
        'early_registration_deadline' => '2024-05-31',
        'regular_registration_deadline' => '2024-07-31',
        'late_registration_deadline' => '2024-08-31',
        'status' => 'upcoming',
        'description' => 'School Year 2024-2025 Enrollment Period',
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    // Create grade level fee directly without factory to avoid creating extra enrollment period
    GradeLevelFee::create([
        'grade_level' => GradeLevel::GRADE_1->value,
        'enrollment_period_id' => $this->enrollmentPeriod->id,
        'tuition_fee_cents' => 2000000,
        'registration_fee_cents' => 100000,
        'miscellaneous_fee_cents' => 500000,
        'laboratory_fee_cents' => 0,
        'library_fee_cents' => 0,
        'sports_fee_cents' => 0,
        'other_fees_cents' => 0,
        'down_payment_cents' => 0,
        'payment_terms' => 'ANNUAL',
        'is_active' => true,
    ]);
});

test('guardian cannot view create form when no active enrollment period', function () {
    // No active enrollment period created (beforeEach creates one with status='upcoming')

    // Debug: Check what periods exist
    $periods = EnrollmentPeriod::all();
    expect($periods)->toHaveCount(1);
    expect($periods->first()->status)->toBe('upcoming');

    // Verify no active periods exist
    $activePeriod = EnrollmentPeriod::where('status', 'active')->first();
    expect($activePeriod)->toBeNull();

    $response = $this->actingAs($this->guardian)
        ->from(route('guardian.dashboard'))
        ->get(route('guardian.enrollments.create'));

    $response->assertRedirect(route('guardian.dashboard'));
    $response->assertSessionHasErrors(['enrollment']);
    expect(session('errors')->get('enrollment')[0])->toContain('Enrollment is currently closed');
});

test('guardian cannot view create form when enrollment period is closed', function () {
    // Create a closed enrollment period (deadline in the past)
    EnrollmentPeriod::create([
        'school_year_id' => $this->sy2024->id,
        'start_date' => now()->subMonths(2),
        'end_date' => now()->subMonth(),
        'early_registration_deadline' => now()->subMonths(2),
        'regular_registration_deadline' => now()->subMonth(),
        'late_registration_deadline' => now()->subDays(5),
        'status' => EnrollmentPeriodStatus::ACTIVE->value,
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $response = $this->actingAs($this->guardian)
        ->get(route('guardian.enrollments.create'));

    $response->assertRedirect();
    $response->assertSessionHasErrors(['enrollment']);
    expect(session('errors')->get('enrollment')[0])->toContain('not currently open');
});

test('guardian can view create form when active enrollment period exists', function () {
    // Create an active open enrollment period
    EnrollmentPeriod::create([
        'school_year_id' => $this->sy2024->id,
        'start_date' => now()->subDays(5),
        'end_date' => now()->addMonths(2),
        'early_registration_deadline' => now()->addDays(10),
        'regular_registration_deadline' => now()->addMonth(),
        'late_registration_deadline' => now()->addMonths(2),
        'status' => EnrollmentPeriodStatus::ACTIVE->value,
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $response = $this->actingAs($this->guardian)
        ->get(route('guardian.enrollments.create'));

    $response->assertStatus(200);
});

test('guardian cannot enroll when no active enrollment period', function () {
    $response = $this->actingAs($this->guardian)
        ->from(route('guardian.enrollments.create'))
        ->post(route('guardian.enrollments.store'), [
            'student_id' => $this->student->id,
            'school_year_id' => $this->sy2024->id,
            'quarter' => Quarter::FIRST->value,
            'grade_level' => GradeLevel::GRADE_1->value,
        ]);

    $response->assertRedirect(route('guardian.enrollments.create'));
    $response->assertSessionHasErrors(['enrollment']);
});

test('guardian cannot enroll when enrollment period deadline passed', function () {
    // Create a closed enrollment period
    EnrollmentPeriod::create([
        'school_year_id' => $this->sy2024->id,
        'start_date' => now()->subMonths(2),
        'end_date' => now()->subMonth(),
        'early_registration_deadline' => now()->subMonths(2),
        'regular_registration_deadline' => now()->subMonth(),
        'late_registration_deadline' => now()->subDays(5),
        'status' => EnrollmentPeriodStatus::ACTIVE->value,
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $response = $this->actingAs($this->guardian)
        ->post(route('guardian.enrollments.store'), [
            'student_id' => $this->student->id,
            'school_year_id' => $this->sy2024->id,
            'quarter' => Quarter::FIRST->value,
            'grade_level' => GradeLevel::GRADE_1->value,
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['student_id']);
});

test('new student cannot enroll when period does not allow new students', function () {
    // Create enrollment period that doesn't allow new students
    EnrollmentPeriod::create([
        'school_year_id' => $this->sy2024->id,
        'start_date' => now()->subDays(5),
        'end_date' => now()->addMonths(2),
        'early_registration_deadline' => now()->addDays(10),
        'regular_registration_deadline' => now()->addMonth(),
        'late_registration_deadline' => now()->addMonths(2),
        'status' => EnrollmentPeriodStatus::ACTIVE->value,
        'allow_new_students' => false,
        'allow_returning_students' => true,
    ]);

    $response = $this->actingAs($this->guardian)
        ->post(route('guardian.enrollments.store'), [
            'student_id' => $this->student->id,
            'school_year_id' => $this->sy2024->id,
            'quarter' => Quarter::FIRST->value,
            'grade_level' => GradeLevel::GRADE_1->value,
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['student_id']);
});

test('returning student cannot enroll when period does not allow returning students', function () {
    // Create a previous enrollment to make this a returning student
    $sy2023 = \App\Models\SchoolYear::firstOrCreate(['name' => '2023-2024', 'start_year' => 2023, 'end_year' => 2024, 'start_date' => '2023-06-01', 'end_date' => '2024-05-31', 'status' => 'completed']);
    Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'guardian_id' => $this->guardianModel->id,
        'school_year_id' => $sy2023->id,
        'status' => EnrollmentStatus::COMPLETED->value,
    ]);

    // Create enrollment period that doesn't allow returning students
    EnrollmentPeriod::create([
        'school_year_id' => $this->sy2024->id,
        'start_date' => now()->subDays(5),
        'end_date' => now()->addMonths(2),
        'early_registration_deadline' => now()->addDays(10),
        'regular_registration_deadline' => now()->addMonth(),
        'late_registration_deadline' => now()->addMonths(2),
        'status' => EnrollmentPeriodStatus::ACTIVE->value,
        'allow_new_students' => true,
        'allow_returning_students' => false,
    ]);

    $response = $this->actingAs($this->guardian)
        ->post(route('guardian.enrollments.store'), [
            'student_id' => $this->student->id,
            'school_year_id' => $this->sy2024->id,
            'quarter' => Quarter::FIRST->value,
            'grade_level' => GradeLevel::GRADE_1->value,
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['student_id']);
});

test('guardian can enroll when all period conditions are met', function () {
    // Create an active open enrollment period
    $period = EnrollmentPeriod::create([
        'school_year_id' => $this->sy2024->id,
        'start_date' => now()->subDays(5),
        'end_date' => now()->addMonths(2),
        'early_registration_deadline' => now()->addDays(10),
        'regular_registration_deadline' => now()->addMonth(),
        'late_registration_deadline' => now()->addMonths(2),
        'status' => EnrollmentPeriodStatus::ACTIVE->value,
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $response = $this->actingAs($this->guardian)
        ->post(route('guardian.enrollments.store'), [
            'student_id' => $this->student->id,
            'school_year_id' => $this->sy2024->id,
            'quarter' => Quarter::FIRST->value,
            'grade_level' => GradeLevel::GRADE_1->value,
        ]);

    $response->assertRedirect(route('guardian.enrollments.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('enrollments', [
        'student_id' => $this->student->id,
        'school_year_id' => $this->sy2024->id,
        'enrollment_period_id' => $period->id,
    ]);
});

test('enrollment period id is set correctly on enrollment', function () {
    $period = EnrollmentPeriod::create([
        'school_year_id' => $this->sy2024->id,
        'start_date' => now()->subDays(5),
        'end_date' => now()->addMonths(2),
        'early_registration_deadline' => now()->addDays(10),
        'regular_registration_deadline' => now()->addMonth(),
        'late_registration_deadline' => now()->addMonths(2),
        'status' => EnrollmentPeriodStatus::ACTIVE->value,
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $this->actingAs($this->guardian)
        ->post(route('guardian.enrollments.store'), [
            'student_id' => $this->student->id,
            'school_year_id' => $this->sy2024->id,
            'quarter' => Quarter::FIRST->value,
            'grade_level' => GradeLevel::GRADE_1->value,
        ]);

    $enrollment = Enrollment::where('student_id', $this->student->id)
        ->where('school_year_id', $this->sy2024->id)
        ->first();

    expect($enrollment)->not->toBeNull();
    expect($enrollment->enrollment_period_id)->toBe($period->id);
});

test('school year is automatically set from active enrollment period and guardian input is ignored', function () {
    // Create enrollment period for 2024-2025
    EnrollmentPeriod::create([
        'school_year_id' => $this->sy2024->id,
        'start_date' => now()->subDays(5),
        'end_date' => now()->addMonths(2),
        'early_registration_deadline' => now()->addDays(10),
        'regular_registration_deadline' => now()->addMonth(),
        'late_registration_deadline' => now()->addMonths(2),
        'status' => EnrollmentPeriodStatus::ACTIVE->value,
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    // Create a different school year using factory (let factory generate unique name)
    $differentSchoolYear = \App\Models\SchoolYear::factory()->create();

    // Guardian tries to enroll for a different school year - this should be ignored
    $response = $this->actingAs($this->guardian)
        ->post(route('guardian.enrollments.store'), [
            'student_id' => $this->student->id,
            'school_year_id' => $differentSchoolYear->id, // Guardian tries different school year (will be ignored)
            'quarter' => Quarter::FIRST->value,
            'grade_level' => GradeLevel::GRADE_1->value,
        ]);

    // Should succeed and use the active period's school year, not the one guardian provided
    $response->assertRedirect();

    // Verify enrollment was created with the active period's school year (2024), NOT the different one
    expect(Enrollment::where('student_id', $this->student->id)
        ->where('school_year_id', $this->sy2024->id)
        ->exists())->toBeTrue();

    // Verify enrollment was NOT created with the different school year
    expect(Enrollment::where('student_id', $this->student->id)
        ->where('school_year_id', $differentSchoolYear->id)
        ->exists())->toBeFalse();
});

test('enrollment relationship with enrollment period works', function () {
    $period = EnrollmentPeriod::create([
        'school_year_id' => $this->sy2024->id,
        'start_date' => now()->subDays(5),
        'end_date' => now()->addMonths(2),
        'early_registration_deadline' => now()->addDays(10),
        'regular_registration_deadline' => now()->addMonth(),
        'late_registration_deadline' => now()->addMonths(2),
        'status' => EnrollmentPeriodStatus::ACTIVE->value,
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $this->actingAs($this->guardian)
        ->post(route('guardian.enrollments.store'), [
            'student_id' => $this->student->id,
            'school_year_id' => $this->sy2024->id,
            'quarter' => Quarter::FIRST->value,
            'grade_level' => GradeLevel::GRADE_1->value,
        ]);

    $enrollment = Enrollment::where('student_id', $this->student->id)
        ->where('school_year_id', $this->sy2024->id)
        ->first();

    expect($enrollment->enrollmentPeriod)->not->toBeNull();
    expect($enrollment->enrollmentPeriod->id)->toBe($period->id);
    expect($enrollment->enrollmentPeriod->schoolYear->name)->toBe('2024-2025');
});

test('canEnrollForPeriod method validates period is open', function () {
    $closedPeriod = EnrollmentPeriod::create([
        'school_year_id' => $this->sy2024->id,
        'start_date' => now()->subMonths(2),
        'end_date' => now()->subMonth(),
        'early_registration_deadline' => now()->subMonths(2),
        'regular_registration_deadline' => now()->subMonth(),
        'late_registration_deadline' => now()->subDays(5),
        'status' => EnrollmentPeriodStatus::CLOSED->value,
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $errors = Enrollment::canEnrollForPeriod($closedPeriod, $this->student);

    expect($errors)->not->toBeEmpty();
    expect($errors[0])->toContain('not currently open');
});

test('canEnrollForPeriod method validates new student eligibility', function () {
    $period = EnrollmentPeriod::create([
        'school_year_id' => $this->sy2024->id,
        'start_date' => now()->subDays(5),
        'end_date' => now()->addMonths(2),
        'early_registration_deadline' => now()->addDays(10),
        'regular_registration_deadline' => now()->addMonth(),
        'late_registration_deadline' => now()->addMonths(2),
        'status' => EnrollmentPeriodStatus::ACTIVE->value,
        'allow_new_students' => false,
        'allow_returning_students' => true,
    ]);

    $errors = Enrollment::canEnrollForPeriod($period, $this->student);

    expect($errors)->not->toBeEmpty();
    expect($errors[0])->toContain('does not accept new students');
});

test('canEnrollForPeriod method validates returning student eligibility', function () {
    // Make student a returning student
    $sy2023 = \App\Models\SchoolYear::firstOrCreate(['name' => '2023-2024', 'start_year' => 2023, 'end_year' => 2024, 'start_date' => '2023-06-01', 'end_date' => '2024-05-31', 'status' => 'completed']);
    Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'guardian_id' => $this->guardianModel->id,
        'school_year_id' => $sy2023->id,
        'status' => EnrollmentStatus::COMPLETED->value,
    ]);

    $period = EnrollmentPeriod::create([
        'school_year_id' => $this->sy2024->id,
        'start_date' => now()->subDays(5),
        'end_date' => now()->addMonths(2),
        'early_registration_deadline' => now()->addDays(10),
        'regular_registration_deadline' => now()->addMonth(),
        'late_registration_deadline' => now()->addMonths(2),
        'status' => EnrollmentPeriodStatus::ACTIVE->value,
        'allow_new_students' => true,
        'allow_returning_students' => false,
    ]);

    $errors = Enrollment::canEnrollForPeriod($period, $this->student);

    expect($errors)->not->toBeEmpty();
    expect($errors[0])->toContain('does not accept returning students');
});

test('canEnrollForPeriod method returns empty array when eligible', function () {
    $period = EnrollmentPeriod::create([
        'school_year_id' => $this->sy2024->id,
        'start_date' => now()->subDays(5),
        'end_date' => now()->addMonths(2),
        'early_registration_deadline' => now()->addDays(10),
        'regular_registration_deadline' => now()->addMonth(),
        'late_registration_deadline' => now()->addMonths(2),
        'status' => EnrollmentPeriodStatus::ACTIVE->value,
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $errors = Enrollment::canEnrollForPeriod($period, $this->student);

    expect($errors)->toBeEmpty();
});

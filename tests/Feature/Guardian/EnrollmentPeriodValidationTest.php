<?php

namespace Tests\Feature\Guardian;

use App\Enums\EnrollmentPeriodStatus;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentPeriodValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $guardian;
    protected Student $student;
    protected Guardian $guardianModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

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

        // Create grade level fee
        GradeLevelFee::create([
            'grade_level' => GradeLevel::GRADE_1->value,
            'school_year' => '2024-2025',
            'tuition_fee' => 20000,
            'miscellaneous_fee' => 5000,
        ]);
    }

    /** @test */
    public function guardian_cannot_view_create_form_when_no_active_enrollment_period()
    {
        // No active enrollment period created

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.enrollments.create'));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['enrollment']);
        $this->assertTrue(str_contains(session('errors')->get('enrollment')[0], 'Enrollment is currently closed'));
    }

    /** @test */
    public function guardian_cannot_view_create_form_when_enrollment_period_is_closed()
    {
        // Create a closed enrollment period (deadline in the past)
        EnrollmentPeriod::create([
            'school_year' => '2024-2025',
            'start_date' => now()->subMonths(2),
            'end_date' => now()->subMonth(),
            'early_registration_deadline' => now()->subMonths(2),
            'regular_registration_deadline' => now()->subMonth(),
            'late_registration_deadline' => now()->subDays(5),
            'status' => EnrollmentPeriodStatus::OPEN->value,
            'allow_new_students' => true,
            'allow_returning_students' => true,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.enrollments.create'));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['enrollment']);
        $this->assertTrue(str_contains(session('errors')->get('enrollment')[0], 'not currently open'));
    }

    /** @test */
    public function guardian_can_view_create_form_when_active_enrollment_period_exists()
    {
        // Create an active open enrollment period
        EnrollmentPeriod::create([
            'school_year' => '2024-2025',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addMonths(2),
            'early_registration_deadline' => now()->addDays(10),
            'regular_registration_deadline' => now()->addMonth(),
            'late_registration_deadline' => now()->addMonths(2),
            'status' => EnrollmentPeriodStatus::OPEN->value,
            'allow_new_students' => true,
            'allow_returning_students' => true,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.enrollments.create'));

        $response->assertStatus(200);
    }

    /** @test */
    public function guardian_cannot_enroll_when_no_active_enrollment_period()
    {
        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.enrollments.store'), [
                'student_id' => $this->student->id,
                'school_year' => '2024-2025',
                'quarter' => Quarter::FIRST->value,
                'grade_level' => GradeLevel::GRADE_1->value,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['enrollment']);
    }

    /** @test */
    public function guardian_cannot_enroll_when_enrollment_period_deadline_passed()
    {
        // Create a closed enrollment period
        EnrollmentPeriod::create([
            'school_year' => '2024-2025',
            'start_date' => now()->subMonths(2),
            'end_date' => now()->subMonth(),
            'early_registration_deadline' => now()->subMonths(2),
            'regular_registration_deadline' => now()->subMonth(),
            'late_registration_deadline' => now()->subDays(5),
            'status' => EnrollmentPeriodStatus::OPEN->value,
            'allow_new_students' => true,
            'allow_returning_students' => true,
        ]);

        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.enrollments.store'), [
                'student_id' => $this->student->id,
                'school_year' => '2024-2025',
                'quarter' => Quarter::FIRST->value,
                'grade_level' => GradeLevel::GRADE_1->value,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['enrollment']);
    }

    /** @test */
    public function new_student_cannot_enroll_when_period_does_not_allow_new_students()
    {
        // Create enrollment period that doesn't allow new students
        EnrollmentPeriod::create([
            'school_year' => '2024-2025',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addMonths(2),
            'early_registration_deadline' => now()->addDays(10),
            'regular_registration_deadline' => now()->addMonth(),
            'late_registration_deadline' => now()->addMonths(2),
            'status' => EnrollmentPeriodStatus::OPEN->value,
            'allow_new_students' => false,
            'allow_returning_students' => true,
        ]);

        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.enrollments.store'), [
                'student_id' => $this->student->id,
                'school_year' => '2024-2025',
                'quarter' => Quarter::FIRST->value,
                'grade_level' => GradeLevel::GRADE_1->value,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['enrollment_period']);
    }

    /** @test */
    public function returning_student_cannot_enroll_when_period_does_not_allow_returning_students()
    {
        // Create a previous enrollment to make this a returning student
        Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardianModel->id,
            'school_year' => '2023-2024',
        ]);

        // Create enrollment period that doesn't allow returning students
        EnrollmentPeriod::create([
            'school_year' => '2024-2025',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addMonths(2),
            'early_registration_deadline' => now()->addDays(10),
            'regular_registration_deadline' => now()->addMonth(),
            'late_registration_deadline' => now()->addMonths(2),
            'status' => EnrollmentPeriodStatus::OPEN->value,
            'allow_new_students' => true,
            'allow_returning_students' => false,
        ]);

        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.enrollments.store'), [
                'student_id' => $this->student->id,
                'school_year' => '2024-2025',
                'quarter' => Quarter::FIRST->value,
                'grade_level' => GradeLevel::GRADE_1->value,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['enrollment_period']);
    }

    /** @test */
    public function guardian_can_enroll_when_all_period_conditions_are_met()
    {
        // Create an active open enrollment period
        $period = EnrollmentPeriod::create([
            'school_year' => '2024-2025',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addMonths(2),
            'early_registration_deadline' => now()->addDays(10),
            'regular_registration_deadline' => now()->addMonth(),
            'late_registration_deadline' => now()->addMonths(2),
            'status' => EnrollmentPeriodStatus::OPEN->value,
            'allow_new_students' => true,
            'allow_returning_students' => true,
        ]);

        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.enrollments.store'), [
                'student_id' => $this->student->id,
                'school_year' => '2024-2025',
                'quarter' => Quarter::FIRST->value,
                'grade_level' => GradeLevel::GRADE_1->value,
            ]);

        $response->assertRedirect(route('guardian.enrollments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('enrollments', [
            'student_id' => $this->student->id,
            'school_year' => '2024-2025',
            'enrollment_period_id' => $period->id,
        ]);
    }

    /** @test */
    public function enrollment_period_id_is_set_correctly_on_enrollment()
    {
        $period = EnrollmentPeriod::create([
            'school_year' => '2024-2025',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addMonths(2),
            'early_registration_deadline' => now()->addDays(10),
            'regular_registration_deadline' => now()->addMonth(),
            'late_registration_deadline' => now()->addMonths(2),
            'status' => EnrollmentPeriodStatus::OPEN->value,
            'allow_new_students' => true,
            'allow_returning_students' => true,
        ]);

        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.enrollments.store'), [
                'student_id' => $this->student->id,
                'school_year' => '2024-2025',
                'quarter' => Quarter::FIRST->value,
                'grade_level' => GradeLevel::GRADE_1->value,
            ]);

        $enrollment = Enrollment::where('student_id', $this->student->id)
            ->where('school_year', '2024-2025')
            ->first();

        $this->assertNotNull($enrollment);
        $this->assertEquals($period->id, $enrollment->enrollment_period_id);
    }

    /** @test */
    public function school_year_must_match_active_enrollment_period()
    {
        // Create enrollment period for 2024-2025
        EnrollmentPeriod::create([
            'school_year' => '2024-2025',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addMonths(2),
            'early_registration_deadline' => now()->addDays(10),
            'regular_registration_deadline' => now()->addMonth(),
            'late_registration_deadline' => now()->addMonths(2),
            'status' => EnrollmentPeriodStatus::OPEN->value,
            'allow_new_students' => true,
            'allow_returning_students' => true,
        ]);

        // Try to enroll for a different school year
        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.enrollments.store'), [
                'student_id' => $this->student->id,
                'school_year' => '2025-2026', // Different school year
                'quarter' => Quarter::FIRST->value,
                'grade_level' => GradeLevel::GRADE_1->value,
            ]);

        $response->assertSessionHasErrors(['school_year']);
    }

    /** @test */
    public function enrollment_relationship_with_enrollment_period_works()
    {
        $period = EnrollmentPeriod::create([
            'school_year' => '2024-2025',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addMonths(2),
            'early_registration_deadline' => now()->addDays(10),
            'regular_registration_deadline' => now()->addMonth(),
            'late_registration_deadline' => now()->addMonths(2),
            'status' => EnrollmentPeriodStatus::OPEN->value,
            'allow_new_students' => true,
            'allow_returning_students' => true,
        ]);

        $this->actingAs($this->guardian)
            ->post(route('guardian.enrollments.store'), [
                'student_id' => $this->student->id,
                'school_year' => '2024-2025',
                'quarter' => Quarter::FIRST->value,
                'grade_level' => GradeLevel::GRADE_1->value,
            ]);

        $enrollment = Enrollment::where('student_id', $this->student->id)
            ->where('school_year', '2024-2025')
            ->first();

        $this->assertNotNull($enrollment->enrollmentPeriod);
        $this->assertEquals($period->id, $enrollment->enrollmentPeriod->id);
        $this->assertEquals('2024-2025', $enrollment->enrollmentPeriod->school_year);
    }

    /** @test */
    public function can_enroll_for_period_method_validates_period_is_open()
    {
        $closedPeriod = EnrollmentPeriod::create([
            'school_year' => '2024-2025',
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

        $this->assertNotEmpty($errors);
        $this->assertTrue(str_contains($errors[0], 'not currently open'));
    }

    /** @test */
    public function can_enroll_for_period_method_validates_new_student_eligibility()
    {
        $period = EnrollmentPeriod::create([
            'school_year' => '2024-2025',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addMonths(2),
            'early_registration_deadline' => now()->addDays(10),
            'regular_registration_deadline' => now()->addMonth(),
            'late_registration_deadline' => now()->addMonths(2),
            'status' => EnrollmentPeriodStatus::OPEN->value,
            'allow_new_students' => false,
            'allow_returning_students' => true,
        ]);

        $errors = Enrollment::canEnrollForPeriod($period, $this->student);

        $this->assertNotEmpty($errors);
        $this->assertTrue(str_contains($errors[0], 'does not accept new students'));
    }

    /** @test */
    public function can_enroll_for_period_method_validates_returning_student_eligibility()
    {
        // Make student a returning student
        Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardianModel->id,
            'school_year' => '2023-2024',
        ]);

        $period = EnrollmentPeriod::create([
            'school_year' => '2024-2025',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addMonths(2),
            'early_registration_deadline' => now()->addDays(10),
            'regular_registration_deadline' => now()->addMonth(),
            'late_registration_deadline' => now()->addMonths(2),
            'status' => EnrollmentPeriodStatus::OPEN->value,
            'allow_new_students' => true,
            'allow_returning_students' => false,
        ]);

        $errors = Enrollment::canEnrollForPeriod($period, $this->student);

        $this->assertNotEmpty($errors);
        $this->assertTrue(str_contains($errors[0], 'does not accept returning students'));
    }

    /** @test */
    public function can_enroll_for_period_method_returns_empty_array_when_eligible()
    {
        $period = EnrollmentPeriod::create([
            'school_year' => '2024-2025',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addMonths(2),
            'early_registration_deadline' => now()->addDays(10),
            'regular_registration_deadline' => now()->addMonth(),
            'late_registration_deadline' => now()->addMonths(2),
            'status' => EnrollmentPeriodStatus::OPEN->value,
            'allow_new_students' => true,
            'allow_returning_students' => true,
        ]);

        $errors = Enrollment::canEnrollForPeriod($period, $this->student);

        $this->assertEmpty($errors);
    }
}

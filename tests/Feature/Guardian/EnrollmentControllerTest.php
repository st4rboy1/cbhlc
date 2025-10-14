<?php

namespace Tests\Feature\Guardian;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\PaymentStatus;
use App\Enums\Quarter;
use App\Models\Enrollment;
use App\Models\GradeLevelFee;
use App\Models\Guardian;
use App\Models\GuardianStudent;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class EnrollmentControllerTest extends TestCase
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
    }

    /** @test */
    public function guardian_can_view_enrollment_index()
    {
        // Create some enrollments
        Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.enrollments.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/enrollments/index')
            ->has('enrollments')
        );
    }

    /** @test */
    public function guardian_can_view_create_enrollment_form()
    {
        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.enrollments.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/enrollments/create')
            ->has('students')
            ->has('gradeLevels')
            ->has('quarters')
            ->has('currentSchoolYear')
        );
    }

    /** @test */
    public function guardian_can_create_enrollment_for_new_student()
    {
        // Setup grade level fee
        GradeLevelFee::create([
            'grade_level' => GradeLevel::GRADE_1->value,
            'school_year' => '2024-2025',
            'tuition_fee' => 20000,
            'miscellaneous_fee' => 5000,
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
            'guardian_id' => $this->guardian->id,
            'school_year' => '2024-2025',
            'quarter' => Quarter::FIRST->value,
            'grade_level' => GradeLevel::GRADE_1->value,
            'status' => EnrollmentStatus::PENDING->value,
        ]);
    }

    /** @test */
    public function existing_student_must_enroll_in_first_quarter()
    {
        // Create previous enrollment
        Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'school_year' => '2023-2024',
            'quarter' => Quarter::FIRST->value,
            'grade_level' => GradeLevel::GRADE_1->value,
            'status' => EnrollmentStatus::COMPLETED->value,
        ]);

        // Setup grade level fee
        GradeLevelFee::create([
            'grade_level' => GradeLevel::GRADE_2->value,
            'school_year' => '2024-2025',
            'tuition_fee' => 22000,
            'miscellaneous_fee' => 5500,
        ]);

        // Try to enroll in second quarter
        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.enrollments.store'), [
                'student_id' => $this->student->id,
                'school_year' => '2024-2025',
                'quarter' => Quarter::SECOND->value, // This should be overridden to FIRST
                'grade_level' => GradeLevel::GRADE_2->value,
            ]);

        $response->assertRedirect(route('guardian.enrollments.index'));

        // Check that quarter was forced to FIRST
        $this->assertDatabaseHas('enrollments', [
            'student_id' => $this->student->id,
            'school_year' => '2024-2025',
            'quarter' => Quarter::FIRST->value, // Should be FIRST, not SECOND
            'grade_level' => GradeLevel::GRADE_2->value,
        ]);
    }

    /** @test */
    public function student_cannot_enroll_in_lower_grade_than_previous()
    {
        // Create previous enrollment
        Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'school_year' => '2023-2024',
            'quarter' => Quarter::FIRST->value,
            'grade_level' => GradeLevel::GRADE_3->value,
            'status' => EnrollmentStatus::COMPLETED->value,
        ]);

        // Setup grade level fee
        GradeLevelFee::create([
            'grade_level' => GradeLevel::GRADE_2->value,
            'school_year' => '2024-2025',
            'tuition_fee' => 22000,
            'miscellaneous_fee' => 5500,
        ]);

        // Try to enroll in lower grade
        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.enrollments.store'), [
                'student_id' => $this->student->id,
                'school_year' => '2024-2025',
                'quarter' => Quarter::FIRST->value,
                'grade_level' => GradeLevel::GRADE_2->value, // Lower than GRADE_3
            ]);

        $response->assertSessionHasErrors(['grade_level']);

        // Ensure enrollment was not created
        $this->assertDatabaseMissing('enrollments', [
            'student_id' => $this->student->id,
            'school_year' => '2024-2025',
        ]);
    }

    /** @test */
    public function guardian_cannot_enroll_student_with_pending_enrollment()
    {
        // Create pending enrollment
        Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'school_year' => '2024-2025',
            'status' => EnrollmentStatus::PENDING->value,
        ]);

        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.enrollments.store'), [
                'student_id' => $this->student->id,
                'school_year' => '2024-2025',
                'quarter' => Quarter::FIRST->value,
                'grade_level' => GradeLevel::GRADE_1->value,
            ]);

        $response->assertSessionHasErrors(['student_id']);
    }

    /** @test */
    public function guardian_cannot_enroll_student_with_active_enrollment()
    {
        // Create active enrollment
        Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'school_year' => '2024-2025',
            'status' => EnrollmentStatus::ENROLLED->value,
        ]);

        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.enrollments.store'), [
                'student_id' => $this->student->id,
                'school_year' => '2025-2026',
                'quarter' => Quarter::FIRST->value,
                'grade_level' => GradeLevel::GRADE_2->value,
            ]);

        $response->assertSessionHasErrors(['student_id']);
    }

    /** @test */
    public function guardian_can_view_own_enrollment()
    {
        $enrollment = Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.enrollments.show', $enrollment));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/enrollments/show')
            ->where('enrollment.id', $enrollment->id)
        );
    }

    /** @test */
    public function guardian_cannot_view_other_student_enrollment()
    {
        $otherStudent = Student::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'student_id' => $otherStudent->id,
            'guardian_id' => Guardian::factory()->create()->id,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.enrollments.show', $enrollment));

        $response->assertStatus(403);
    }

    /** @test */
    public function guardian_can_edit_pending_enrollment()
    {
        $enrollment = Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'status' => EnrollmentStatus::PENDING->value,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.enrollments.edit', $enrollment));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/enrollments/edit')
            ->where('enrollment.id', $enrollment->id)
        );
    }

    /** @test */
    public function guardian_cannot_edit_approved_enrollment()
    {
        $enrollment = Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'status' => EnrollmentStatus::ENROLLED->value,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.enrollments.edit', $enrollment));

        $response->assertRedirect(route('guardian.enrollments.show', $enrollment));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function guardian_can_update_pending_enrollment()
    {
        $enrollment = Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'status' => EnrollmentStatus::PENDING->value,
            'quarter' => Quarter::FIRST->value,
            'grade_level' => GradeLevel::GRADE_1->value,
        ]);

        $response = $this->actingAs($this->guardian)
            ->put(route('guardian.enrollments.update', $enrollment), [
                'quarter' => Quarter::SECOND->value,
                'grade_level' => GradeLevel::GRADE_2->value,
            ]);

        $response->assertRedirect(route('guardian.enrollments.show', $enrollment));
        $response->assertSessionHas('success');

        $enrollment->refresh();
        $this->assertEquals(Quarter::SECOND, $enrollment->quarter);
        $this->assertEquals(GradeLevel::GRADE_2, $enrollment->grade_level);
    }

    /** @test */
    public function guardian_can_cancel_pending_enrollment()
    {
        $enrollment = Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'status' => EnrollmentStatus::PENDING->value,
        ]);

        $response = $this->actingAs($this->guardian)
            ->delete(route('guardian.enrollments.destroy', $enrollment));

        $response->assertRedirect(route('guardian.enrollments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('enrollments', [
            'id' => $enrollment->id,
        ]);
    }

    /** @test */
    public function guardian_cannot_cancel_approved_enrollment()
    {
        $enrollment = Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'status' => EnrollmentStatus::ENROLLED->value,
        ]);

        $response = $this->actingAs($this->guardian)
            ->delete(route('guardian.enrollments.destroy', $enrollment));

        $response->assertRedirect(route('guardian.enrollments.show', $enrollment));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
        ]);
    }

    /** @test */
    public function enrollment_calculates_fees_correctly()
    {
        // Setup grade level fee
        $gradeLevelFee = GradeLevelFee::create([
            'grade_level' => GradeLevel::GRADE_1->value,
            'school_year' => '2024-2025',
            'tuition_fee' => 20000.50,
            'miscellaneous_fee' => 5000.25,
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

        $this->assertEquals(2000050, $enrollment->tuition_fee_cents);
        $this->assertEquals(500025, $enrollment->miscellaneous_fee_cents);
        $this->assertEquals(2500075, $enrollment->total_amount_cents);
        $this->assertEquals(2500075, $enrollment->net_amount_cents);
        $this->assertEquals(0, $enrollment->amount_paid_cents);
        $this->assertEquals(2500075, $enrollment->balance_cents);
        $this->assertEquals(PaymentStatus::PENDING, $enrollment->payment_status);
    }

    /** @test */
    public function guardian_cannot_access_other_guardians_student()
    {
        $otherGuardian = User::factory()->create();
        $otherGuardian->assignRole('guardian');

        $response = $this->actingAs($otherGuardian)
            ->post(route('guardian.enrollments.store'), [
                'student_id' => $this->student->id,
                'school_year' => '2024-2025',
                'quarter' => Quarter::FIRST->value,
                'grade_level' => GradeLevel::GRADE_1->value,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function create_form_shows_selected_student()
    {
        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.enrollments.create', ['student_id' => $this->student->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/enrollments/create')
            ->where('selectedStudentId', (string) $this->student->id)
        );
    }

    /** @test */
    public function student_information_includes_enrollment_status()
    {
        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.enrollments.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/enrollments/create')
            ->has('students.0.id')
            ->has('students.0.first_name')
            ->has('students.0.is_new_student')
            ->has('students.0.available_grade_levels')
        );
    }

    /** @test */
    public function guardian_cannot_edit_other_guardians_enrollment()
    {
        // Create another guardian and student
        $otherGuardian = User::factory()->create();
        $otherGuardian->assignRole('guardian');

        $otherGuardianModel = Guardian::create([
            'user_id' => $otherGuardian->id,
            'first_name' => 'Other',
            'last_name' => 'Guardian',
            'contact_number' => '09987654321',
            'address' => '456 Other St',
        ]);

        $otherStudent = Student::factory()->create();

        GuardianStudent::create([
            'guardian_id' => $otherGuardianModel->id,
            'student_id' => $otherStudent->id,
            'relationship_type' => 'father',
            'is_primary_contact' => true,
        ]);

        $enrollment = Enrollment::factory()->create([
            'student_id' => $otherStudent->id,
            'guardian_id' => $otherGuardianModel->id,
            'status' => EnrollmentStatus::PENDING->value,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.enrollments.edit', $enrollment));

        $response->assertStatus(403);
    }

    /** @test */
    public function guardian_cannot_update_other_guardians_enrollment()
    {
        // Create another guardian and student
        $otherGuardian = User::factory()->create();
        $otherGuardian->assignRole('guardian');

        $otherGuardianModel = Guardian::create([
            'user_id' => $otherGuardian->id,
            'first_name' => 'Other',
            'last_name' => 'Guardian',
            'contact_number' => '09987654321',
            'address' => '456 Other St',
        ]);

        $otherStudent = Student::factory()->create();

        GuardianStudent::create([
            'guardian_id' => $otherGuardianModel->id,
            'student_id' => $otherStudent->id,
            'relationship_type' => 'father',
            'is_primary_contact' => true,
        ]);

        $enrollment = Enrollment::factory()->create([
            'student_id' => $otherStudent->id,
            'guardian_id' => $otherGuardianModel->id,
            'status' => EnrollmentStatus::PENDING->value,
        ]);

        $response = $this->actingAs($this->guardian)
            ->put(route('guardian.enrollments.update', $enrollment), [
                'quarter' => Quarter::SECOND->value,
                'grade_level' => GradeLevel::GRADE_2->value,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function guardian_cannot_delete_other_guardians_enrollment()
    {
        // Create another guardian and student
        $otherGuardian = User::factory()->create();
        $otherGuardian->assignRole('guardian');

        $otherGuardianModel = Guardian::create([
            'user_id' => $otherGuardian->id,
            'first_name' => 'Other',
            'last_name' => 'Guardian',
            'contact_number' => '09987654321',
            'address' => '456 Other St',
        ]);

        $otherStudent = Student::factory()->create();

        GuardianStudent::create([
            'guardian_id' => $otherGuardianModel->id,
            'student_id' => $otherStudent->id,
            'relationship_type' => 'father',
            'is_primary_contact' => true,
        ]);

        $enrollment = Enrollment::factory()->create([
            'student_id' => $otherStudent->id,
            'guardian_id' => $otherGuardianModel->id,
            'status' => EnrollmentStatus::PENDING->value,
        ]);

        $response = $this->actingAs($this->guardian)
            ->delete(route('guardian.enrollments.destroy', $enrollment));

        $response->assertStatus(403);
    }

    /** @test */
    public function guardian_cannot_update_enrollment_to_non_pending_status()
    {
        $enrollment = Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'status' => EnrollmentStatus::ENROLLED->value,
            'quarter' => Quarter::FIRST->value,
            'grade_level' => GradeLevel::GRADE_1->value,
        ]);

        $response = $this->actingAs($this->guardian)
            ->put(route('guardian.enrollments.update', $enrollment), [
                'quarter' => Quarter::SECOND->value,
                'grade_level' => GradeLevel::GRADE_2->value,
            ]);

        $response->assertRedirect(route('guardian.enrollments.show', $enrollment));
        $response->assertSessionHas('error');

        $enrollment->refresh();
        // Should not be updated
        $this->assertEquals(Quarter::FIRST, $enrollment->quarter);
        $this->assertEquals(GradeLevel::GRADE_1, $enrollment->grade_level);
    }

    /** @test */
    public function student_cannot_have_duplicate_enrollment_for_same_school_year()
    {
        // Create an existing enrollment
        Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'school_year' => '2024-2025',
            'status' => EnrollmentStatus::COMPLETED->value,
        ]);

        // Setup grade level fee
        GradeLevelFee::create([
            'grade_level' => GradeLevel::GRADE_2->value,
            'school_year' => '2024-2025',
            'tuition_fee' => 22000,
            'miscellaneous_fee' => 5500,
        ]);

        // Try to create another enrollment for the same school year
        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.enrollments.store'), [
                'student_id' => $this->student->id,
                'school_year' => '2024-2025',
                'quarter' => Quarter::FIRST->value,
                'grade_level' => GradeLevel::GRADE_2->value,
            ]);

        $response->assertSessionHasErrors(['student_id']);

        // Count should still be 1
        $this->assertEquals(1, Enrollment::where('student_id', $this->student->id)
            ->where('school_year', '2024-2025')
            ->count());
    }

    /** @test */
    public function enrollment_handles_missing_grade_level_fee()
    {
        // Don't create a GradeLevelFee - simulate missing fee

        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.enrollments.store'), [
                'student_id' => $this->student->id,
                'school_year' => '2024-2025',
                'quarter' => Quarter::FIRST->value,
                'grade_level' => GradeLevel::GRADE_1->value,
            ]);

        $response->assertRedirect(route('guardian.enrollments.index'));

        $enrollment = Enrollment::where('student_id', $this->student->id)
            ->where('school_year', '2024-2025')
            ->first();

        // Should have zero fees when grade level fee is not found
        $this->assertEquals(0, $enrollment->tuition_fee_cents);
        $this->assertEquals(0, $enrollment->miscellaneous_fee_cents);
        $this->assertEquals(0, $enrollment->total_amount_cents);
    }

    /** @test */
    public function enrollment_handles_invalid_grade_level_gracefully()
    {
        // Create previous enrollment with an invalid grade level
        $previousEnrollment = Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'school_year' => '2023-2024',
            'quarter' => Quarter::FIRST->value,
            'status' => EnrollmentStatus::COMPLETED->value,
        ]);

        // Manually set an invalid grade level in the database
        \DB::table('enrollments')
            ->where('id', $previousEnrollment->id)
            ->update(['grade_level' => 'InvalidGrade']);

        // Setup grade level fee
        GradeLevelFee::create([
            'grade_level' => GradeLevel::GRADE_2->value,
            'school_year' => '2024-2025',
            'tuition_fee' => 22000,
            'miscellaneous_fee' => 5500,
        ]);

        // Should handle the invalid grade level gracefully and allow enrollment
        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.enrollments.store'), [
                'student_id' => $this->student->id,
                'school_year' => '2024-2025',
                'quarter' => Quarter::FIRST->value,
                'grade_level' => GradeLevel::GRADE_2->value,
            ]);

        $response->assertRedirect(route('guardian.enrollments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('enrollments', [
            'student_id' => $this->student->id,
            'school_year' => '2024-2025',
            'grade_level' => GradeLevel::GRADE_2->value,
        ]);
    }

    /** @test */
    public function guardian_with_multiple_students_sees_all_in_create_form()
    {
        // Create a second student for the same guardian
        $secondStudent = Student::factory()->create();

        GuardianStudent::create([
            'guardian_id' => $this->guardianModel->id,
            'student_id' => $secondStudent->id,
            'relationship_type' => 'mother',
            'is_primary_contact' => true,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.enrollments.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/enrollments/create')
            ->has('students', 2)
            ->where('students.0.id', $this->student->id)
            ->where('students.1.id', $secondStudent->id)
        );
    }

    /** @test */
    public function create_form_shows_student_with_middle_name()
    {
        // Update student to have a middle name
        $this->student->update(['middle_name' => 'Middle']);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.enrollments.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/enrollments/create')
            ->where('students.0.middle_name', 'Middle')
        );
    }

    /** @test */
    public function enrollment_index_paginates_results()
    {
        // Create 15 enrollments for the student
        for ($i = 0; $i < 15; $i++) {
            Enrollment::factory()->create([
                'student_id' => $this->student->id,
                'guardian_id' => $this->guardian->id,
                'school_year' => '202'.$i.'-202'.($i + 1),
            ]);
        }

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.enrollments.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/enrollments/index')
            ->has('enrollments.data', 10) // Should paginate to 10 per page
            ->has('enrollments.links')
        );
    }

    /** @test */
    public function enrollment_validation_requires_all_fields()
    {
        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.enrollments.store'), []);

        $response->assertSessionHasErrors([
            'student_id',
            'school_year',
            'quarter',
            'grade_level',
        ]);
    }

    /** @test */
    public function enrollment_validation_requires_existing_student()
    {
        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.enrollments.store'), [
                'student_id' => 99999, // Non-existent student
                'school_year' => '2024-2025',
                'quarter' => Quarter::FIRST->value,
                'grade_level' => GradeLevel::GRADE_1->value,
            ]);

        $response->assertSessionHasErrors(['student_id']);
    }

    /** @test */
    public function update_enrollment_validation_requires_fields()
    {
        $enrollment = Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'status' => EnrollmentStatus::PENDING->value,
        ]);

        $response = $this->actingAs($this->guardian)
            ->put(route('guardian.enrollments.update', $enrollment), []);

        $response->assertSessionHasErrors(['quarter', 'grade_level']);
    }
}

<?php

namespace Tests\Unit\Http\Requests\SuperAdmin;

use App\Enums\EnrollmentStatus;
use App\Http\Requests\SuperAdmin\UpdateEnrollmentRequest;
use App\Models\Guardian;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateEnrollmentRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_authorize_returns_true_for_super_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $request = new UpdateEnrollmentRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_validation_rules(): void
    {
        $request = new UpdateEnrollmentRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('student_id', $rules);
        $this->assertArrayHasKey('guardian_id', $rules);
        $this->assertArrayHasKey('grade_level', $rules);
        $this->assertArrayHasKey('school_year_id', $rules);
        $this->assertArrayHasKey('quarter', $rules);
        $this->assertArrayHasKey('type', $rules);
        $this->assertArrayHasKey('payment_plan', $rules);
        $this->assertArrayHasKey('status', $rules);
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $student = Student::factory()->create();
        $guardian = Guardian::factory()->create();
        $schoolYear = SchoolYear::factory()->create();

        $data = [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'grade_level' => 'Grade 1',
            'school_year_id' => $schoolYear->id,
            'quarter' => 'First',
            'type' => 'new',
            'previous_school' => 'Previous School',
            'payment_plan' => 'monthly',
            'status' => EnrollmentStatus::APPROVED->value,
        ];

        $request = new UpdateEnrollmentRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_without_previous_school(): void
    {
        $student = Student::factory()->create();
        $guardian = Guardian::factory()->create();
        $schoolYear = SchoolYear::factory()->create();

        $data = [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'grade_level' => 'Grade 2',
            'school_year_id' => $schoolYear->id,
            'quarter' => 'Second',
            'type' => 'continuing',
            'payment_plan' => 'annual',
            'status' => EnrollmentStatus::PENDING->value,
        ];

        $request = new UpdateEnrollmentRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_invalid_school_year_id(): void
    {
        $student = Student::factory()->create();
        $guardian = Guardian::factory()->create();

        $data = [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'grade_level' => 'Grade 1',
            'school_year_id' => 99999, // Non-existent ID
            'quarter' => 'First',
            'type' => 'new',
            'payment_plan' => 'monthly',
            'status' => EnrollmentStatus::PENDING->value,
        ];

        $request = new UpdateEnrollmentRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('school_year_id', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_invalid_type(): void
    {
        $student = Student::factory()->create();
        $guardian = Guardian::factory()->create();
        $schoolYear = SchoolYear::factory()->create();

        $data = [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'grade_level' => 'Grade 1',
            'school_year_id' => $schoolYear->id,
            'quarter' => 'First',
            'type' => 'invalid_type',
            'payment_plan' => 'monthly',
            'status' => EnrollmentStatus::PENDING->value,
        ];

        $request = new UpdateEnrollmentRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_invalid_payment_plan(): void
    {
        $student = Student::factory()->create();
        $guardian = Guardian::factory()->create();
        $schoolYear = SchoolYear::factory()->create();

        $data = [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'grade_level' => 'Grade 1',
            'school_year_id' => $schoolYear->id,
            'quarter' => 'First',
            'type' => 'new',
            'payment_plan' => 'invalid_plan',
            'status' => EnrollmentStatus::PENDING->value,
        ];

        $request = new UpdateEnrollmentRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('payment_plan', $validator->errors()->toArray());
    }

    public function test_no_custom_messages_needed(): void
    {
        $request = new UpdateEnrollmentRequest;
        $rules = $request->rules();

        // Verify school_year_id uses standard exists validation
        $this->assertContains('exists:school_years,id', $rules['school_year_id']);
    }
}

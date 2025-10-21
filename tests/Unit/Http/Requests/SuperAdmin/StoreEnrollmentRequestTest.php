<?php

namespace Tests\Unit\Http\Requests\SuperAdmin;

use App\Http\Requests\SuperAdmin\StoreEnrollmentRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreEnrollmentRequestTest extends TestCase
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

        $request = new StoreEnrollmentRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_validation_rules(): void
    {
        $request = new StoreEnrollmentRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('student_id', $rules);
        $this->assertArrayNotHasKey('guardian_id', $rules); // Guardian is now automatically selected
        $this->assertArrayHasKey('grade_level', $rules);
        $this->assertArrayHasKey('school_year', $rules);
        $this->assertArrayHasKey('quarter', $rules);
        $this->assertArrayHasKey('type', $rules);
        $this->assertArrayHasKey('payment_plan', $rules);
        $this->assertArrayHasKey('previous_school', $rules);
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $student = Student::factory()->create();

        $data = [
            'student_id' => $student->id,
            'grade_level' => 'grade_1',
            'school_year' => '2024-2025',
            'quarter' => 'first_quarter',
            'type' => 'new',
            'previous_school' => 'Previous School Name',
            'payment_plan' => 'monthly',
        ];

        $request = new StoreEnrollmentRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_invalid_school_year_format(): void
    {
        $student = Student::factory()->create();

        $data = [
            'student_id' => $student->id,
            'grade_level' => 'grade_1',
            'school_year' => '2024', // Invalid format
            'quarter' => 'first_quarter',
            'type' => 'new',
            'payment_plan' => 'monthly',
        ];

        $request = new StoreEnrollmentRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('school_year', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_invalid_type(): void
    {
        $student = Student::factory()->create();

        $data = [
            'student_id' => $student->id,
            'grade_level' => 'grade_1',
            'school_year' => '2024-2025',
            'quarter' => 'first_quarter',
            'type' => 'invalid_type',
            'payment_plan' => 'monthly',
        ];

        $request = new StoreEnrollmentRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_invalid_payment_plan(): void
    {
        $student = Student::factory()->create();

        $data = [
            'student_id' => $student->id,
            'grade_level' => 'grade_1',
            'school_year' => '2024-2025',
            'quarter' => 'first_quarter',
            'type' => 'new',
            'payment_plan' => 'weekly', // Invalid
        ];

        $request = new StoreEnrollmentRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('payment_plan', $validator->errors()->toArray());
    }

    public function test_custom_messages(): void
    {
        $request = new StoreEnrollmentRequest;
        $messages = $request->messages();

        $this->assertArrayHasKey('school_year.regex', $messages);
        $this->assertStringContainsString('YYYY-YYYY', $messages['school_year.regex']);
    }
}

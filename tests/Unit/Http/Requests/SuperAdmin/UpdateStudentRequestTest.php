<?php

namespace Tests\Unit\Http\Requests\SuperAdmin;

use App\Http\Requests\SuperAdmin\UpdateStudentRequest;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateStudentRequestTest extends TestCase
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

        $request = new UpdateStudentRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_validation_rules(): void
    {
        $existingStudent = Student::factory()->create();

        $request = new class($existingStudent) extends UpdateStudentRequest
        {
            private $student;

            public function __construct($student)
            {
                $this->student = $student;
            }

            public function route($param = null, $default = null)
            {
                if ($param === 'student') {
                    return $this->student;
                }

                return parent::route($param, $default);
            }
        };

        $rules = $request->rules();

        $this->assertArrayHasKey('first_name', $rules);
        $this->assertArrayHasKey('last_name', $rules);
        $this->assertArrayHasKey('birth_date', $rules);
        $this->assertArrayHasKey('gender', $rules);
        $this->assertArrayHasKey('address', $rules);
        $this->assertArrayHasKey('grade_level', $rules);
        $this->assertArrayHasKey('guardian_ids', $rules);

        // Check that email rule excludes current student
        $emailRule = collect($rules['email'])->first(fn ($rule) => is_string($rule) && str_starts_with($rule, 'unique:'));
        $this->assertStringContainsString($existingStudent->id, $emailRule);
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $existingStudent = Student::factory()->create();
        $guardian = Guardian::factory()->create();

        $data = [
            'first_name' => 'Updated',
            'middle_name' => 'Middle',
            'last_name' => 'Name',
            'birth_date' => '2010-01-01',
            'birth_place' => 'Manila',
            'gender' => 'Male',
            'nationality' => 'Filipino',
            'religion' => 'Christian',
            'address' => '123 Updated Street',
            'phone' => '09123456789',
            'email' => 'updated@example.com',
            'grade_level' => 'Grade 2',
            'guardian_ids' => [$guardian->id],
        ];

        $request = new class($existingStudent) extends UpdateStudentRequest
        {
            private $student;

            public function __construct($student)
            {
                $this->student = $student;
            }

            public function route($param = null, $default = null)
            {
                if ($param === 'student') {
                    return $this->student;
                }

                return parent::route($param, $default);
            }
        };

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_same_email(): void
    {
        $existingStudent = Student::factory()->create(['email' => 'test@example.com']);
        $guardian = Guardian::factory()->create();

        $data = [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'birth_date' => '2010-01-01',
            'gender' => 'Male',
            'address' => '123 Updated Street',
            'email' => 'test@example.com', // Same email
            'grade_level' => 'Grade 2',
            'guardian_ids' => [$guardian->id],
        ];

        $request = new class($existingStudent) extends UpdateStudentRequest
        {
            private $student;

            public function __construct($student)
            {
                $this->student = $student;
            }

            public function route($param = null, $default = null)
            {
                if ($param === 'student') {
                    return $this->student;
                }

                return parent::route($param, $default);
            }
        };

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_custom_messages(): void
    {
        $request = new UpdateStudentRequest;
        $messages = $request->messages();

        $this->assertArrayHasKey('guardian_ids.required', $messages);
        $this->assertArrayHasKey('guardian_ids.min', $messages);
        $this->assertEquals('At least one guardian must be selected.', $messages['guardian_ids.required']);
    }
}

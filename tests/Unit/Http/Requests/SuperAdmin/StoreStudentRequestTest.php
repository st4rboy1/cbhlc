<?php

namespace Tests\Unit\Http\Requests\SuperAdmin;

use App\Http\Requests\SuperAdmin\StoreStudentRequest;
use App\Models\Guardian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreStudentRequestTest extends TestCase
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

        $request = new StoreStudentRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_validation_rules(): void
    {
        $request = new StoreStudentRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('first_name', $rules);
        $this->assertArrayHasKey('last_name', $rules);
        $this->assertArrayHasKey('birthdate', $rules);
        $this->assertArrayHasKey('gender', $rules);
        $this->assertArrayHasKey('address', $rules);
        $this->assertArrayHasKey('grade_level', $rules);
        $this->assertArrayHasKey('guardian_ids', $rules);
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $guardian = Guardian::factory()->create();

        $data = [
            'first_name' => 'John',
            'middle_name' => 'Michael',
            'last_name' => 'Doe',
            'birthdate' => '2010-01-01',
            'birth_place' => 'Manila',
            'gender' => 'Male',
            'nationality' => 'Filipino',
            'religion' => 'Christian',
            'address' => '123 Test Street',
            'phone' => '09123456789',
            'email' => 'john.doe@example.com',
            'grade_level' => 'Grade 1',
            'guardian_ids' => [$guardian->id],
        ];

        $request = new StoreStudentRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_without_guardian(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'birthdate' => '2010-01-01',
            'gender' => 'Male',
            'address' => '123 Test Street',
            'grade_level' => 'Grade 1',
            'guardian_ids' => [], // Empty array
        ];

        $request = new StoreStudentRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('guardian_ids', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_invalid_gender(): void
    {
        $guardian = Guardian::factory()->create();

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'birthdate' => '2010-01-01',
            'gender' => 'invalid',
            'address' => '123 Test Street',
            'grade_level' => 'Grade 1',
            'guardian_ids' => [$guardian->id],
        ];

        $request = new StoreStudentRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('gender', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_future_birth_date(): void
    {
        $guardian = Guardian::factory()->create();

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'birthdate' => now()->addDay()->format('Y-m-d'),
            'gender' => 'Male',
            'address' => '123 Test Street',
            'grade_level' => 'Grade 1',
            'guardian_ids' => [$guardian->id],
        ];

        $request = new StoreStudentRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('birthdate', $validator->errors()->toArray());
    }

    public function test_custom_messages(): void
    {
        $request = new StoreStudentRequest;
        $messages = $request->messages();

        $this->assertArrayHasKey('guardian_ids.required', $messages);
        $this->assertArrayHasKey('guardian_ids.min', $messages);
        $this->assertEquals('At least one guardian must be selected.', $messages['guardian_ids.required']);
    }
}

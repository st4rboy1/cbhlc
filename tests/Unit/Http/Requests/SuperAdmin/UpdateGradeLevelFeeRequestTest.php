<?php

namespace Tests\Unit\Http\Requests\SuperAdmin;

use App\Http\Requests\SuperAdmin\UpdateGradeLevelFeeRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateGradeLevelFeeRequestTest extends TestCase
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

        $request = new UpdateGradeLevelFeeRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_validation_rules(): void
    {
        $request = new UpdateGradeLevelFeeRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('grade_level', $rules);
        $this->assertArrayHasKey('school_year', $rules);
        $this->assertArrayHasKey('tuition_fee', $rules);
        $this->assertArrayHasKey('miscellaneous_fee', $rules);
        $this->assertArrayHasKey('other_fees', $rules);
        $this->assertArrayHasKey('total_fee', $rules);
        $this->assertArrayHasKey('payment_plan_annual', $rules);
        $this->assertArrayHasKey('payment_plan_semestral', $rules);
        $this->assertArrayHasKey('payment_plan_quarterly', $rules);
        $this->assertArrayHasKey('payment_plan_monthly', $rules);
        $this->assertArrayHasKey('description', $rules);
        $this->assertArrayHasKey('is_active', $rules);
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $data = [
            'grade_level' => 'Grade 1',
            'school_year' => '2024-2025',
            'tuition_fee' => 30000,
            'miscellaneous_fee' => 5000,
            'other_fees' => 2000,
            'total_fee' => 37000,
            'payment_plan_annual' => 37000,
            'payment_plan_semestral' => 19000,
            'payment_plan_quarterly' => 9500,
            'payment_plan_monthly' => 3700,
            'description' => 'Grade 1 fees for school year 2024-2025',
            'is_active' => true,
        ];

        $request = new UpdateGradeLevelFeeRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_without_optional_fields(): void
    {
        $data = [
            'grade_level' => 'Grade 2',
            'school_year' => '2024-2025',
            'tuition_fee' => 32000,
            'miscellaneous_fee' => 5000,
            'total_fee' => 37000,
            'payment_plan_annual' => 37000,
            'payment_plan_semestral' => 19000,
            'payment_plan_quarterly' => 9500,
            'payment_plan_monthly' => 3700,
            // No other_fees, description, or is_active
        ];

        $request = new UpdateGradeLevelFeeRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_invalid_school_year_format(): void
    {
        $data = [
            'grade_level' => 'Grade 1',
            'school_year' => '2024/2025', // Invalid format
            'tuition_fee' => 30000,
            'miscellaneous_fee' => 5000,
            'total_fee' => 35000,
            'payment_plan_annual' => 35000,
            'payment_plan_semestral' => 18000,
            'payment_plan_quarterly' => 9000,
            'payment_plan_monthly' => 3500,
        ];

        $request = new UpdateGradeLevelFeeRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('school_year', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_negative_fees(): void
    {
        $data = [
            'grade_level' => 'Grade 1',
            'school_year' => '2024-2025',
            'tuition_fee' => -1000, // Negative value
            'miscellaneous_fee' => 5000,
            'total_fee' => 4000,
            'payment_plan_annual' => 4000,
            'payment_plan_semestral' => 2000,
            'payment_plan_quarterly' => 1000,
            'payment_plan_monthly' => 400,
        ];

        $request = new UpdateGradeLevelFeeRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('tuition_fee', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_missing_required_fields(): void
    {
        $data = [
            'grade_level' => 'Grade 1',
            'school_year' => '2024-2025',
            // Missing required fee fields
        ];

        $request = new UpdateGradeLevelFeeRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('tuition_fee', $validator->errors()->toArray());
        $this->assertArrayHasKey('miscellaneous_fee', $validator->errors()->toArray());
        $this->assertArrayHasKey('total_fee', $validator->errors()->toArray());
        $this->assertArrayHasKey('payment_plan_annual', $validator->errors()->toArray());
    }

    public function test_custom_messages(): void
    {
        $request = new UpdateGradeLevelFeeRequest;
        $messages = $request->messages();

        $this->assertArrayHasKey('school_year.regex', $messages);
        $this->assertEquals('School year must be in the format YYYY-YYYY (e.g., 2024-2025).', $messages['school_year.regex']);
    }
}

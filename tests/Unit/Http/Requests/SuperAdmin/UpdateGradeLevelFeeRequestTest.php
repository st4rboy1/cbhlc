<?php

namespace Tests\Unit\Http\Requests\SuperAdmin;

use App\Http\Requests\SuperAdmin\UpdateGradeLevelFeeRequest;
use App\Models\SchoolYear;
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
        $this->assertArrayHasKey('school_year_id', $rules);
        $this->assertArrayHasKey('tuition_fee', $rules);
        $this->assertArrayHasKey('miscellaneous_fee', $rules);
        $this->assertArrayHasKey('other_fees', $rules);
        $this->assertArrayHasKey('payment_terms', $rules);
        $this->assertArrayHasKey('is_active', $rules);
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $schoolYear = SchoolYear::factory()->create();

        $data = [
            'grade_level' => 'Grade 1',
            'school_year_id' => $schoolYear->id,
            'tuition_fee' => 30000,
            'miscellaneous_fee' => 5000,
            'other_fees' => 2000,
            'payment_terms' => 'annual',
            'is_active' => true,
        ];

        $request = new UpdateGradeLevelFeeRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_without_optional_fields(): void
    {
        $schoolYear = SchoolYear::factory()->create();

        $data = [
            'grade_level' => 'Grade 2',
            'school_year_id' => $schoolYear->id,
            'tuition_fee' => 32000,
            'miscellaneous_fee' => 5000,
            'payment_terms' => 'semestral',
            // No other_fees or is_active (optional)
        ];

        $request = new UpdateGradeLevelFeeRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_invalid_school_year_id(): void
    {
        $data = [
            'grade_level' => 'Grade 1',
            'school_year_id' => 99999, // Non-existent ID
            'tuition_fee' => 30000,
            'miscellaneous_fee' => 5000,
            'payment_terms' => 'annual',
        ];

        $request = new UpdateGradeLevelFeeRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('school_year_id', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_negative_fees(): void
    {
        $schoolYear = SchoolYear::factory()->create();

        $data = [
            'grade_level' => 'Grade 1',
            'school_year_id' => $schoolYear->id,
            'tuition_fee' => -1000, // Negative value
            'miscellaneous_fee' => 5000,
            'payment_terms' => 'annual',
        ];

        $request = new UpdateGradeLevelFeeRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('tuition_fee', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_missing_required_fields(): void
    {
        $schoolYear = SchoolYear::factory()->create();

        $data = [
            'grade_level' => 'Grade 1',
            'school_year_id' => $schoolYear->id,
            // Missing required fee fields
        ];

        $request = new UpdateGradeLevelFeeRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('tuition_fee', $validator->errors()->toArray());
        $this->assertArrayHasKey('miscellaneous_fee', $validator->errors()->toArray());
        $this->assertArrayHasKey('payment_terms', $validator->errors()->toArray());
    }

    public function test_custom_messages(): void
    {
        $request = new UpdateGradeLevelFeeRequest;
        $messages = $request->messages();

        $this->assertArrayHasKey('grade_level.unique', $messages);
        $this->assertEquals('A fee structure for this grade level, school year, and payment term already exists.', $messages['grade_level.unique']);
    }
}

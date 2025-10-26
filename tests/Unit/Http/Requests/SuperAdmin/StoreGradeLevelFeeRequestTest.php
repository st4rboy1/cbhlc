<?php

namespace Tests\Unit\Http\Requests\SuperAdmin;

use App\Http\Requests\SuperAdmin\StoreGradeLevelFeeRequest;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreGradeLevelFeeRequestTest extends TestCase
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

        $request = new StoreGradeLevelFeeRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_validation_rules(): void
    {
        $request = new StoreGradeLevelFeeRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('grade_level', $rules);
        $this->assertArrayHasKey('school_year_id', $rules);
        $this->assertArrayHasKey('tuition_fee', $rules);
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $schoolYear = SchoolYear::factory()->create();

        $data = [
            'grade_level' => 'Grade 1',
            'school_year_id' => $schoolYear->id,
            'tuition_fee' => 15000.00,
            'miscellaneous_fee' => 2500.00,
            'payment_terms' => 'ANNUAL',
            'is_active' => true,
        ];

        $request = new StoreGradeLevelFeeRequest;
        $validator = validator($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_fails_with_invalid_school_year_id(): void
    {
        $data = [
            'grade_level' => 'Grade 1',
            'school_year_id' => 99999,
            'tuition_fee' => 15000.00,
            'miscellaneous_fee' => 2500.00,
            'payment_terms' => 'ANNUAL',
        ];

        $request = new StoreGradeLevelFeeRequest;
        $validator = validator($data, $request->rules());

        $this->assertTrue($validator->fails());
    }

    public function test_custom_messages(): void
    {
        $request = new StoreGradeLevelFeeRequest;
        $messages = $request->messages();

        $this->assertArrayHasKey('grade_level.unique', $messages);
    }
}

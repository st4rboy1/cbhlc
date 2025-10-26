<?php

namespace Tests\Unit\Http\Requests\SuperAdmin;

use App\Http\Requests\SuperAdmin\StoreSchoolYearRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreSchoolYearRequestTest extends TestCase
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

        $request = new StoreSchoolYearRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_authorize_returns_true_for_administrator(): void
    {
        $user = User::factory()->create();
        $user->assignRole('administrator');

        $request = new StoreSchoolYearRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_authorize_returns_false_for_non_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('registrar');

        $request = new StoreSchoolYearRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertFalse($request->authorize());
    }

    public function test_validation_rules(): void
    {
        $request = new StoreSchoolYearRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('start_year', $rules);
        $this->assertArrayHasKey('end_year', $rules);
        $this->assertArrayHasKey('start_date', $rules);
        $this->assertArrayHasKey('end_date', $rules);
        $this->assertArrayHasKey('status', $rules);
        $this->assertArrayHasKey('is_active', $rules);
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $data = [
            'name' => '2025-2026',
            'start_year' => 2025,
            'end_year' => 2026,
            'start_date' => '2025-06-01',
            'end_date' => '2026-03-31',
            'status' => 'upcoming',
            'is_active' => false,
        ];

        $request = new StoreSchoolYearRequest;
        $validator = validator($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_fails_with_end_year_not_greater_than_start_year(): void
    {
        $data = [
            'name' => '2025-2025',
            'start_year' => 2025,
            'end_year' => 2025,
            'start_date' => '2025-06-01',
            'end_date' => '2025-03-31',
            'status' => 'upcoming',
            'is_active' => false,
        ];

        $request = new StoreSchoolYearRequest;
        $validator = validator($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('end_year', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_end_date_before_start_date(): void
    {
        $data = [
            'name' => '2025-2026',
            'start_year' => 2025,
            'end_year' => 2026,
            'start_date' => '2025-06-01',
            'end_date' => '2025-03-31',
            'status' => 'upcoming',
            'is_active' => false,
        ];

        $request = new StoreSchoolYearRequest;
        $validator = validator($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('end_date', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_invalid_status(): void
    {
        $data = [
            'name' => '2025-2026',
            'start_year' => 2025,
            'end_year' => 2026,
            'start_date' => '2025-06-01',
            'end_date' => '2026-03-31',
            'status' => 'invalid_status',
            'is_active' => false,
        ];

        $request = new StoreSchoolYearRequest;
        $validator = validator($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_missing_required_fields(): void
    {
        $data = [];

        $request = new StoreSchoolYearRequest;
        $validator = validator($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('start_year', $validator->errors()->toArray());
        $this->assertArrayHasKey('end_year', $validator->errors()->toArray());
        $this->assertArrayHasKey('start_date', $validator->errors()->toArray());
        $this->assertArrayHasKey('end_date', $validator->errors()->toArray());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
        $this->assertArrayHasKey('is_active', $validator->errors()->toArray());
    }

    public function test_custom_messages(): void
    {
        $request = new StoreSchoolYearRequest;
        $messages = $request->messages();

        $this->assertArrayHasKey('name.required', $messages);
        $this->assertArrayHasKey('name.unique', $messages);
        $this->assertArrayHasKey('start_year.required', $messages);
        $this->assertArrayHasKey('end_year.required', $messages);
        $this->assertArrayHasKey('end_year.gt', $messages);
        $this->assertArrayHasKey('start_date.required', $messages);
        $this->assertArrayHasKey('end_date.required', $messages);
        $this->assertArrayHasKey('end_date.after', $messages);
        $this->assertArrayHasKey('status.required', $messages);
        $this->assertArrayHasKey('status.in', $messages);
    }
}

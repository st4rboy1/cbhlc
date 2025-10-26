<?php

namespace Tests\Unit\Http\Requests\SuperAdmin;

use App\Http\Requests\SuperAdmin\StoreGuardianRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreGuardianRequestTest extends TestCase
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

        $request = new StoreGuardianRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_authorize_returns_false_for_non_super_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('administrator');

        $request = new StoreGuardianRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertFalse($request->authorize());
    }

    public function test_validation_rules(): void
    {
        $request = new StoreGuardianRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
        $this->assertArrayHasKey('first_name', $rules);
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $data = [
            'email' => 'guardian@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '09171234567',
            'address' => '123 Main St',
        ];

        $request = new StoreGuardianRequest;
        $validator = validator($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_fails_with_invalid_email(): void
    {
        $data = [
            'email' => 'not-an-email',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '09171234567',
            'address' => '123 Main St',
        ];

        $request = new StoreGuardianRequest;
        $validator = validator($data, $request->rules());

        $this->assertTrue($validator->fails());
    }

    public function test_validation_fails_with_password_mismatch(): void
    {
        $data = [
            'email' => 'guardian@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'DifferentPassword!',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '09171234567',
            'address' => '123 Main St',
        ];

        $request = new StoreGuardianRequest;
        $validator = validator($data, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

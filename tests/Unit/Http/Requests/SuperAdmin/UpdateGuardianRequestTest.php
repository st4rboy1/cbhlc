<?php

namespace Tests\Unit\Http\Requests\SuperAdmin;

use App\Http\Requests\SuperAdmin\UpdateGuardianRequest;
use App\Models\Guardian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateGuardianRequestTest extends TestCase
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

        $request = new UpdateGuardianRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_validation_rules(): void
    {
        $existingGuardian = Guardian::factory()->create();

        $request = new class($existingGuardian) extends UpdateGuardianRequest {
            private $guardian;
            public function __construct($guardian)
            {
                $this->guardian = $guardian;
            }
            public function route($param = null, $default = null)
            {
                if ($param === 'guardian') {
                    return $this->guardian;
                }
                return parent::route($param, $default);
            }
        };

        $rules = $request->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
        $this->assertArrayHasKey('first_name', $rules);
        $this->assertArrayHasKey('last_name', $rules);
        $this->assertArrayHasKey('relationship_type', $rules);
        $this->assertArrayHasKey('phone', $rules);
        $this->assertArrayHasKey('address', $rules);

        // Check that email rule excludes current guardian's user
        $emailRule = collect($rules['email'])->first(fn ($rule) => is_string($rule) && str_starts_with($rule, 'unique:'));
        $this->assertStringContainsString($existingGuardian->user_id, $emailRule);
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $existingGuardian = Guardian::factory()->create();

        $data = [
            'email' => 'updated@example.com',
            'first_name' => 'Updated',
            'middle_name' => 'Middle',
            'last_name' => 'Guardian',
            'relationship_type' => 'father',
            'phone' => '09123456789',
            'occupation' => 'Engineer',
            'employer' => 'Tech Company',
            'address' => '123 Updated Street',
            'emergency_contact' => true,
        ];

        $request = new class($existingGuardian) extends UpdateGuardianRequest {
            private $guardian;
            public function __construct($guardian)
            {
                $this->guardian = $guardian;
            }
            public function route($param = null, $default = null)
            {
                if ($param === 'guardian') {
                    return $this->guardian;
                }
                return parent::route($param, $default);
            }
        };

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_same_email(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $existingGuardian = Guardian::factory()->create(['user_id' => $user->id]);

        $data = [
            'email' => 'test@example.com', // Same email
            'first_name' => 'Updated',
            'last_name' => 'Guardian',
            'relationship_type' => 'mother',
            'phone' => '09123456789',
            'address' => '123 Updated Street',
            'emergency_contact' => false,
        ];

        $request = new class($existingGuardian) extends UpdateGuardianRequest {
            private $guardian;
            public function __construct($guardian)
            {
                $this->guardian = $guardian;
            }
            public function route($param = null, $default = null)
            {
                if ($param === 'guardian') {
                    return $this->guardian;
                }
                return parent::route($param, $default);
            }
        };

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_without_password(): void
    {
        $existingGuardian = Guardian::factory()->create();

        $data = [
            'email' => 'updated@example.com',
            'first_name' => 'Updated',
            'last_name' => 'Guardian',
            'relationship_type' => 'guardian',
            'phone' => '09123456789',
            'address' => '123 Updated Street',
            'emergency_contact' => false,
            // No password field
        ];

        $request = new class($existingGuardian) extends UpdateGuardianRequest {
            private $guardian;
            public function __construct($guardian)
            {
                $this->guardian = $guardian;
            }
            public function route($param = null, $default = null)
            {
                if ($param === 'guardian') {
                    return $this->guardian;
                }
                return parent::route($param, $default);
            }
        };

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_invalid_relationship_type(): void
    {
        $existingGuardian = Guardian::factory()->create();

        $data = [
            'email' => 'updated@example.com',
            'first_name' => 'Updated',
            'last_name' => 'Guardian',
            'relationship_type' => 'invalid_type',
            'phone' => '09123456789',
            'address' => '123 Updated Street',
            'emergency_contact' => false,
        ];

        $request = new class($existingGuardian) extends UpdateGuardianRequest {
            private $guardian;
            public function __construct($guardian)
            {
                $this->guardian = $guardian;
            }
            public function route($param = null, $default = null)
            {
                if ($param === 'guardian') {
                    return $this->guardian;
                }
                return parent::route($param, $default);
            }
        };

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('relationship_type', $validator->errors()->toArray());
    }
}

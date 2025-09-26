<?php

namespace Tests\Unit\Http\Requests\SuperAdmin;

use App\Http\Requests\SuperAdmin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdateUserRequestTest extends TestCase
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

        $request = new UpdateUserRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_authorize_returns_false_for_non_super_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('administrator');

        $request = new UpdateUserRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertFalse($request->authorize());
    }

    public function test_validation_rules(): void
    {
        // We test without mocking route to avoid PHPUnit method conflict
        $request = new class extends UpdateUserRequest {
            public function route($param = null, $default = null)
            {
                if ($param === 'user') {
                    return User::factory()->create();
                }
                return parent::route($param, $default);
            }
        };

        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
        $this->assertArrayHasKey('role', $rules);

        // Check that email has unique rule
        $emailRule = collect($rules['email'])->first(fn ($rule) => is_string($rule) && str_starts_with($rule, 'unique:'));
        $this->assertNotNull($emailRule);
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $existingUser = User::factory()->create();
        Role::create(['name' => 'test_role']);

        $data = [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
            'role' => 'test_role',
        ];

        $request = new class extends UpdateUserRequest {
            private $user;
            public function __construct($user = null)
            {
                $this->user = $user;
            }
            public function route($param = null, $default = null)
            {
                if ($param === 'user') {
                    return $this->user;
                }
                return parent::route($param, $default);
            }
        };
        $request = new $request($existingUser);

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_same_email(): void
    {
        $existingUser = User::factory()->create(['email' => 'test@example.com']);
        Role::create(['name' => 'test_role']);

        $data = [
            'name' => 'Updated User',
            'email' => 'test@example.com', // Same email as existing
            'role' => 'test_role',
        ];

        $request = new class($existingUser) extends UpdateUserRequest {
            private $user;
            public function __construct($user)
            {
                $this->user = $user;
            }
            public function route($param = null, $default = null)
            {
                if ($param === 'user') {
                    return $this->user;
                }
                return parent::route($param, $default);
            }
        };

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_without_password(): void
    {
        $existingUser = User::factory()->create();
        Role::create(['name' => 'test_role']);

        $data = [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'role' => 'test_role',
            // No password field
        ];

        $request = new class($existingUser) extends UpdateUserRequest {
            private $user;
            public function __construct($user)
            {
                $this->user = $user;
            }
            public function route($param = null, $default = null)
            {
                if ($param === 'user') {
                    return $this->user;
                }
                return parent::route($param, $default);
            }
        };

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_duplicate_email(): void
    {
        $existingUser = User::factory()->create();
        $otherUser = User::factory()->create(['email' => 'taken@example.com']);
        Role::create(['name' => 'test_role']);

        $data = [
            'name' => 'Updated User',
            'email' => 'taken@example.com', // Email belongs to another user
            'role' => 'test_role',
        ];

        $request = new class($existingUser) extends UpdateUserRequest {
            private $user;
            public function __construct($user)
            {
                $this->user = $user;
            }
            public function route($param = null, $default = null)
            {
                if ($param === 'user') {
                    return $this->user;
                }
                return parent::route($param, $default);
            }
        };

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }
}

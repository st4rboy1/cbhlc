<?php

namespace Tests\Unit\Http\Requests\SuperAdmin;

use App\Http\Requests\SuperAdmin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
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

        $request = new UpdateUserRequest();
        $request->setUserResolver(fn() => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_authorize_returns_false_for_non_super_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('administrator');

        $request = new UpdateUserRequest();
        $request->setUserResolver(fn() => $user);

        $this->assertFalse($request->authorize());
    }

    public function test_validation_rules_with_user(): void
    {
        $existingUser = User::factory()->create();

        // Mock the route parameter
        $request = new UpdateUserRequest();
        $request->setRouteResolver(function () use ($existingUser) {
            $route = new RoutingRoute('PUT', 'test', []);
            $route->setParameter('user', $existingUser);
            return $route;
        });

        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
        $this->assertArrayHasKey('role', $rules);

        // Check that email rule excludes current user
        $emailRule = collect($rules['email'])->first(fn($rule) => is_string($rule) && str_starts_with($rule, 'unique:'));
        $this->assertStringContainsString($existingUser->id, $emailRule);
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

        $request = new UpdateUserRequest();
        $request->setRouteResolver(function () use ($existingUser) {
            $route = new RoutingRoute('PUT', 'test', []);
            $route->setParameter('user', $existingUser);
            return $route;
        });

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

        $request = new UpdateUserRequest();
        $request->setRouteResolver(function () use ($existingUser) {
            $route = new RoutingRoute('PUT', 'test', []);
            $route->setParameter('user', $existingUser);
            return $route;
        });

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

        $request = new UpdateUserRequest();
        $request->setRouteResolver(function () use ($existingUser) {
            $route = new RoutingRoute('PUT', 'test', []);
            $route->setParameter('user', $existingUser);
            return $route;
        });

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }
}
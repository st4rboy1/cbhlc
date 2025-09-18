<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Configure the model factory to create with parent role.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (\App\Models\User $user) {
            // Default role is parent if no role is assigned
            if (! $user->hasAnyRole()) {
                $user->assignRole('parent');
            }
        });
    }

    /**
     * Indicate that the user is a super admin.
     */
    public function superAdmin(): static
    {
        return $this->afterCreating(function (\App\Models\User $user) {
            $user->syncRoles('super_admin');
        });
    }

    /**
     * Indicate that the user is an administrator.
     */
    public function administrator(): static
    {
        return $this->afterCreating(function (\App\Models\User $user) {
            $user->syncRoles('administrator');
        });
    }

    /**
     * Indicate that the user is a registrar.
     */
    public function registrar(): static
    {
        return $this->afterCreating(function (\App\Models\User $user) {
            $user->syncRoles('registrar');
        });
    }

    /**
     * Indicate that the user is a parent.
     */
    public function parent(): static
    {
        return $this->afterCreating(function (\App\Models\User $user) {
            $user->syncRoles('parent');
        });
    }

    /**
     * Indicate that the user is a student.
     */
    public function student(): static
    {
        return $this->afterCreating(function (\App\Models\User $user) {
            $user->syncRoles('student');
        });
    }
}

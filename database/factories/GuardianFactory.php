<?php

namespace Database\Factories;

use App\Models\Guardian;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guardian>
 */
class GuardianFactory extends Factory
{
    protected $model = Guardian::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hasEmergencyContact = fake()->boolean(80); // 80% chance of having emergency contact

        return [
            'user_id' => User::factory(),
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->optional(0.5)->lastName(), // 50% chance of having middle name
            'last_name' => fake()->lastName(),
            'contact_number' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'occupation' => fake()->jobTitle(),
            'employer' => fake()->company(),
            'emergency_contact_name' => $hasEmergencyContact ? fake()->name() : null,
            'emergency_contact_phone' => $hasEmergencyContact ? fake()->phoneNumber() : null,
            'emergency_contact_relationship' => $hasEmergencyContact
                ? fake()->randomElement(['Spouse', 'Parent', 'Sibling', 'Friend', 'Relative'])
                : null,
        ];
    }
}

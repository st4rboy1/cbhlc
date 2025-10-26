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
        $hasEmergencyContact = $this->faker->boolean(80); // 80% chance of having emergency contact

        return [
            'user_id' => User::factory(),
            'first_name' => $this->faker->firstName(),
            'middle_name' => $this->faker->optional(0.5)->lastName(), // 50% chance of having middle name
            'last_name' => $this->faker->lastName(),
            'contact_number' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'occupation' => $this->faker->jobTitle(),
            'employer' => $this->faker->company(),
            'emergency_contact_name' => $hasEmergencyContact ? $this->faker->name() : null,
            'emergency_contact_phone' => $hasEmergencyContact ? $this->faker->phoneNumber() : null,
            'emergency_contact_relationship' => $hasEmergencyContact
                ? $this->faker->randomElement(['Spouse', 'Parent', 'Sibling', 'Friend', 'Relative'])
                : null,
        ];
    }
}

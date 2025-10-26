<?php

namespace Database\Factories;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NotificationPreference>
 */
class NotificationPreferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = array_keys(NotificationPreference::availableTypes());

        return [
            'user_id' => User::factory(),
            'notification_type' => $this->faker->randomElement($types),
            'email_enabled' => $this->faker->boolean(80), // 80% chance of being enabled
            'database_enabled' => $this->faker->boolean(80),
        ];
    }
}

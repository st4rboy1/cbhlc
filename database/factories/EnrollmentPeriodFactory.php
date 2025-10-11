<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnrollmentPeriod>
 */
class EnrollmentPeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = fake()->numberBetween(2024, 2030);
        $startDate = fake()->dateTimeBetween("$year-06-01", "$year-08-01");
        $endDate = fake()->dateTimeBetween($startDate, "$year-12-31");

        return [
            'school_year' => "$year-".($year + 1),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'early_registration_deadline' => fake()->dateTimeBetween($startDate, "+30 days"),
            'regular_registration_deadline' => fake()->dateTimeBetween($startDate, "+60 days"),
            'late_registration_deadline' => fake()->dateTimeBetween($startDate, "+90 days"),
            'status' => fake()->randomElement(['upcoming', 'active', 'closed']),
            'description' => fake()->sentence(),
            'allow_new_students' => true,
            'allow_returning_students' => true,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'upcoming',
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
        ]);
    }
}

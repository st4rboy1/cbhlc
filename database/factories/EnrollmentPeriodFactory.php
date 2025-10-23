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
        $schoolYearName = "$year-".($year + 1);

        // Find or create the school year
        $schoolYear = \App\Models\SchoolYear::firstOrCreate(
            ['name' => $schoolYearName],
            [
                'start_year' => $year,
                'end_year' => $year + 1,
                'status' => 'upcoming',
            ]
        );

        return [
            'school_year_id' => $schoolYear->id,
            'school_year' => $schoolYearName,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'early_registration_deadline' => fake()->dateTimeBetween($startDate, '+30 days'),
            'regular_registration_deadline' => fake()->dateTimeBetween($startDate, '+60 days'),
            'late_registration_deadline' => fake()->dateTimeBetween($startDate, '+90 days'),
            'status' => fake()->randomElement(['upcoming', 'active', 'closed']),
            'description' => fake()->sentence(),
            'allow_new_students' => true,
            'allow_returning_students' => true,
        ];
    }

    /**
     * Set a specific school year for the enrollment period.
     */
    public function schoolYear(string $schoolYearName): static
    {
        return $this->state(function (array $attributes) use ($schoolYearName) {
            // Parse the school year name to get start and end years
            [$startYear, $endYear] = explode('-', $schoolYearName);

            // Find or create the school year
            $schoolYear = \App\Models\SchoolYear::firstOrCreate(
                ['name' => $schoolYearName],
                [
                    'start_year' => (int) $startYear,
                    'end_year' => (int) $endYear,
                    'status' => 'upcoming',
                ]
            );

            return [
                'school_year_id' => $schoolYear->id,
                'school_year' => $schoolYearName,
            ];
        });
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

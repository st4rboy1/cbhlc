<?php

namespace Database\Factories;

use App\Enums\GradeLevel;
use App\Models\GradeLevelFee;
use App\Models\SchoolYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GradeLevelFee>
 */
class GradeLevelFeeFactory extends Factory
{
    protected $model = GradeLevelFee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currentYear = now()->year;
        $schoolYear = $currentYear.'-'.($currentYear + 1);

        // Create or get existing school year
        $schoolYearModel = SchoolYear::firstOrCreate(
            ['name' => $schoolYear],
            [
                'start_year' => $currentYear,
                'end_year' => $currentYear + 1,
                'start_date' => $currentYear.'-06-01',
                'end_date' => ($currentYear + 1).'-03-31',
                'status' => 'active',
                'is_active' => true,
            ]
        );

        return [
            'grade_level' => fake()->randomElement(GradeLevel::values()),
            'school_year' => $schoolYear,
            'school_year_id' => $schoolYearModel->id,
            'tuition_fee_cents' => fake()->numberBetween(2000000, 5000000), // 20,000 to 50,000 pesos
            'registration_fee_cents' => fake()->numberBetween(100000, 300000), // 1,000 to 3,000 pesos
            'miscellaneous_fee_cents' => fake()->numberBetween(50000, 150000), // 500 to 1,500 pesos
            'laboratory_fee_cents' => fake()->numberBetween(0, 100000), // 0 to 1,000 pesos
            'library_fee_cents' => fake()->numberBetween(20000, 50000), // 200 to 500 pesos
            'sports_fee_cents' => fake()->numberBetween(10000, 30000), // 100 to 300 pesos
            'other_fees_cents' => fake()->numberBetween(0, 50000), // 0 to 500 pesos
            'payment_terms' => fake()->randomElement(['ANNUAL', 'SEMESTRAL', 'QUARTERLY', 'MONTHLY']),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the grade level fee is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set a specific grade level.
     */
    public function gradeLevel(GradeLevel $gradeLevel): static
    {
        return $this->state(fn (array $attributes) => [
            'grade_level' => $gradeLevel->value,
        ]);
    }

    /**
     * Set a specific school year.
     */
    public function schoolYear(string $schoolYear): static
    {
        return $this->state(function (array $attributes) use ($schoolYear) {
            // Parse the school year string (e.g., "2024-2025")
            $years = explode('-', $schoolYear);
            $startYear = (int) $years[0];
            $endYear = (int) $years[1];

            // Create or get existing school year
            $schoolYearModel = SchoolYear::firstOrCreate(
                ['name' => $schoolYear],
                [
                    'start_year' => $startYear,
                    'end_year' => $endYear,
                    'start_date' => $startYear.'-06-01',
                    'end_date' => $endYear.'-03-31',
                    'status' => 'active',
                    'is_active' => true,
                ]
            );

            return [
                'school_year' => $schoolYear,
                'school_year_id' => $schoolYearModel->id,
            ];
        });
    }
}

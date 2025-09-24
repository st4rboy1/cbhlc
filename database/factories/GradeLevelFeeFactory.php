<?php

namespace Database\Factories;

use App\Enums\GradeLevel;
use App\Models\GradeLevelFee;
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

        return [
            'grade_level' => $this->faker->randomElement(GradeLevel::values()),
            'tuition_fee_cents' => $this->faker->numberBetween(2000000, 5000000), // 20,000 to 50,000 pesos
            'registration_fee_cents' => $this->faker->numberBetween(100000, 300000), // 1,000 to 3,000 pesos
            'miscellaneous_fee_cents' => $this->faker->numberBetween(50000, 150000), // 500 to 1,500 pesos
            'laboratory_fee_cents' => $this->faker->numberBetween(0, 100000), // 0 to 1,000 pesos
            'library_fee_cents' => $this->faker->numberBetween(20000, 50000), // 200 to 500 pesos
            'sports_fee_cents' => $this->faker->numberBetween(10000, 30000), // 100 to 300 pesos
            'school_year' => $schoolYear,
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
        return $this->state(fn (array $attributes) => [
            'school_year' => $schoolYear,
        ]);
    }
}

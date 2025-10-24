<?php

namespace Database\Factories;

use App\Models\SchoolYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SchoolYear>
 */
class SchoolYearFactory extends Factory
{
    protected $model = SchoolYear::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate unique school year names for parallel test execution
        // Use combination of microtime and random number to ensure uniqueness across processes
        $uniqueId = (int) (microtime(true) * 10000) + mt_rand(0, 9999);
        $startYear = 2020 + ($uniqueId % 100); // Keep years in reasonable range
        $endYear = $startYear + 1;

        // Add random suffix to ensure uniqueness when years collide
        $suffix = substr(md5(uniqid((string) mt_rand(), true)), 0, 6);
        $name = "{$startYear}-{$endYear}-{$suffix}";

        return [
            'name' => $name,
            'start_year' => $startYear,
            'end_year' => $endYear,
            'start_date' => "{$startYear}-06-01",
            'end_date' => "{$endYear}-05-31",
            'status' => 'active',
            'is_active' => false,
        ];
    }

    /**
     * Indicate that the school year is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the school year is upcoming.
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'upcoming',
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the school year is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'is_active' => false,
        ]);
    }
}

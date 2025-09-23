<?php

namespace Database\Factories;

use App\Enums\GradeLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a birthdate between 6 and 17 years ago (under 18)
        $yearsAgo = fake()->numberBetween(6, 17);
        $birthdate = now()->subYears($yearsAgo)->subDays(fake()->numberBetween(0, 365))->addDays(1);

        // Use a combination of timestamp and random to ensure uniqueness even in parallel tests
        $uniqueId = substr(md5(uniqid(mt_rand(), true)), 0, 8);

        return [
            'student_id' => 'TEST-'.$uniqueId,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'middle_name' => fake()->optional()->firstName(),
            'birthdate' => $birthdate->format('Y-m-d'),
            'gender' => fake()->randomElement(['Male', 'Female']),
            'address' => fake()->address(),
            'contact_number' => fake()->optional()->numerify('+63 9## ### ####'),
            'email' => fake()->optional()->safeEmail(),
            'grade_level' => fake()->randomElement(GradeLevel::cases())->value,
            'section' => fake()->optional()->word(),
            'user_id' => null, // Can be set explicitly when needed
        ];
    }
}

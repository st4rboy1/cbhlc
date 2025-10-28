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
        $yearsAgo = $this->faker->numberBetween(6, 17);
        $birthdate = now()->subYears($yearsAgo)->subDays($this->faker->numberBetween(0, 365))->addDays(1);

        // Use a combination of timestamp and random to ensure uniqueness even in parallel tests
        $uniqueId = substr(md5(uniqid(mt_rand(), true)), 0, 8);

        return [
            'student_id' => 'TEST-'.$uniqueId,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'middle_name' => $this->faker->optional()->lastName(),
            'birthdate' => $birthdate->format('Y-m-d'),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'birth_place' => $this->faker->city().', '.$this->faker->country(),
            'nationality' => $this->faker->randomElement(['Filipino', 'American', 'Chinese', 'Korean', 'Japanese', 'Other']),
            'religion' => $this->faker->randomElement(['Catholic', 'Protestant', 'Islam', 'Buddhism', 'Other']),
            'address' => $this->faker->address(),
            'contact_number' => $this->faker->optional()->numerify('+63 9## ### ####'),
            'email' => $this->faker->optional()->safeEmail(),
            'grade_level' => $this->faker->randomElement(GradeLevel::cases())->value,
            'section' => $this->faker->optional()->word(),
            'user_id' => null, // Can be set explicitly when needed (for students with user accounts)
            'guardian_id' => null, // Can be set explicitly when needed (references guardians table)
        ];
    }
}

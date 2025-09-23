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
        $birthdate = $this->faker->dateTimeBetween('-18 years', '-6 years');

        // Use a combination of timestamp and random to ensure uniqueness even in parallel tests
        $uniqueId = substr(md5(uniqid(mt_rand(), true)), 0, 8);

        return [
            'student_id' => 'TEST-'.$uniqueId,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'middle_name' => $this->faker->optional()->firstName(),
            'birthdate' => $birthdate->format('Y-m-d'),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'address' => $this->faker->address(),
            'contact_number' => $this->faker->optional()->phoneNumber(),
            'email' => $this->faker->optional()->email(),
            'grade_level' => $this->faker->randomElement(GradeLevel::cases())->value,
            'section' => $this->faker->optional()->word(),
            'user_id' => null, // Can be set explicitly when needed
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\GradeLevel;
use App\Models\Student;
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

        return [
            'student_id' => Student::generateStudentId(),
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

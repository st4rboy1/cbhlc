<?php

namespace Database\Factories;

use App\Enums\RelationshipType;
use App\Models\GuardianStudent;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GuardianStudent>
 */
class GuardianStudentFactory extends Factory
{
    protected $model = GuardianStudent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'guardian_id' => User::factory(),
            'student_id' => Student::factory(),
            'relationship_type' => $this->faker->randomElement(RelationshipType::values()),
            'is_primary_contact' => $this->faker->boolean(70), // 70% chance of being primary contact
        ];
    }

    /**
     * Indicate that this is the primary contact.
     */
    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary_contact' => true,
        ]);
    }

    /**
     * Indicate that this is not the primary contact.
     */
    public function secondary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary_contact' => false,
        ]);
    }

    /**
     * Set a specific relationship type.
     */
    public function relationship(RelationshipType|string $type): static
    {
        $value = $type instanceof RelationshipType ? $type->value : $type;

        return $this->state(fn (array $attributes) => [
            'relationship_type' => $value,
        ]);
    }

    /**
     * Set parent relationship (mother or father).
     */
    public function parent(): static
    {
        return $this->state(fn (array $attributes) => [
            'relationship_type' => $this->faker->randomElement([
                RelationshipType::Mother->value,
                RelationshipType::Father->value,
            ]),
        ]);
    }
}

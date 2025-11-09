<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting>
 */
class SettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Setting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->word().'_setting',
            'value' => fake()->word(),
            'type' => 'string',
            'description' => fake()->sentence(),
            'is_public' => fake()->boolean(),
        ];
    }

    /**
     * Indicate that the setting is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the setting is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * Set the setting as a boolean type.
     */
    public function boolean(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'boolean',
            'value' => fake()->boolean() ? '1' : '0',
        ]);
    }

    /**
     * Set the setting as an integer type.
     */
    public function integer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'integer',
            'value' => (string) fake()->numberBetween(1, 1000),
        ]);
    }

    /**
     * Set the setting as a float type.
     */
    public function float(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'float',
            'value' => (string) fake()->randomFloat(2, 1, 1000),
        ]);
    }

    /**
     * Set the setting as an array/json type.
     */
    public function array(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'array',
            'value' => json_encode([fake()->word(), fake()->word()]),
        ]);
    }
}

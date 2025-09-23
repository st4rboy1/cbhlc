<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'payment_method' => fake()->randomElement(PaymentMethod::cases()),
            'payment_date' => fake()->dateTimeThisMonth(),
            'reference_number' => fake()->optional()->bothify('PAY-######'),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => PaymentMethod::CASH,
        ]);
    }

    public function bankTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => PaymentMethod::BANK_TRANSFER,
            'reference_number' => 'BT-'.fake()->numberBetween(100000, 999999),
        ]);
    }

    public function check(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => PaymentMethod::CHECK,
            'reference_number' => 'CHK-'.fake()->numberBetween(1000, 9999),
        ]);
    }
}

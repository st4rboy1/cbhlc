<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\User;
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
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'payment_method' => $this->faker->randomElement(PaymentMethod::cases()),
            'payment_date' => $this->faker->dateTimeThisMonth(),
            'reference_number' => $this->faker->optional()->bothify('PAY-######'),
            'notes' => $this->faker->optional()->sentence(),
            'processed_by' => User::factory(),
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
            'reference_number' => 'BT-'.$this->faker->numberBetween(100000, 999999),
        ]);
    }
}

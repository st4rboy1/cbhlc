<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalAmount = fake()->randomFloat(2, 1000, 50000);
        $paidAmount = fake()->randomFloat(2, 0, $totalAmount);
        $status = $paidAmount >= $totalAmount ? InvoiceStatus::PAID :
                 ($paidAmount > 0 ? InvoiceStatus::PARTIALLY_PAID : InvoiceStatus::SENT);

        return [
            'invoice_number' => 'INV-'.fake()->unique()->numberBetween(100000, 999999),
            'enrollment_id' => Enrollment::factory(),
            'invoice_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'status' => $status,
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
            'paid_at' => $status === InvoiceStatus::PAID ? fake()->dateTimeThisMonth() : null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::DRAFT,
            'paid_amount' => 0,
            'paid_at' => null,
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::SENT,
            'paid_amount' => 0,
            'paid_at' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::PAID,
            'paid_amount' => $attributes['total_amount'] ?? fake()->randomFloat(2, 1000, 50000),
            'paid_at' => fake()->dateTimeThisMonth(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::OVERDUE,
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'paid_amount' => 0,
            'paid_at' => null,
        ]);
    }
}

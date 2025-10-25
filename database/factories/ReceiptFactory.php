<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Receipt>
 */
class ReceiptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $payment = Payment::factory()->create();

        return [
            'receipt_number' => Receipt::generateReceiptNumber(),
            'payment_id' => $payment->id,
            'invoice_id' => $payment->invoice_id,
            'receipt_date' => $payment->payment_date ?? now(),
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method->value,
            'received_by' => User::factory(),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Receipt with specific amount
     */
    public function amount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
        ]);
    }

    /**
     * Receipt for specific payment
     */
    public function forPayment(Payment $payment): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_id' => $payment->id,
            'invoice_id' => $payment->invoice_id,
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method->value,
            'receipt_date' => $payment->payment_date ?? now(),
        ]);
    }

    /**
     * Receipt received by specific user
     */
    public function receivedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'received_by' => $user->id,
        ]);
    }
}

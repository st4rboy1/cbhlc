<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $descriptions = [
            'Tuition Fee',
            'Miscellaneous Fee',
            'Laboratory Fee',
            'Library Fee',
            'Sports Fee',
            'Registration Fee',
            'ID Card Fee',
            'Insurance Fee',
            'Computer Lab Fee',
            'Activity Fee',
        ];

        $quantity = $this->faker->numberBetween(1, 3);
        $unitPrice = $this->faker->randomFloat(2, 100, 5000);
        $amount = $quantity * $unitPrice;

        return [
            'invoice_id' => Invoice::factory(),
            'description' => $this->faker->randomElement($descriptions),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'amount' => $amount,
        ];
    }

    /**
     * Create a tuition fee item.
     */
    public function tuitionFee(?float $amount = null): static
    {
        return $this->state(function (array $attributes) use ($amount) {
            $finalAmount = $amount ?? $this->faker->randomFloat(2, 10000, 50000);

            return [
                'description' => 'Tuition Fee',
                'quantity' => 1,
                'unit_price' => $finalAmount,
                'amount' => $finalAmount,
            ];
        });
    }

    /**
     * Create a miscellaneous fee item.
     */
    public function miscellaneousFee(?float $amount = null): static
    {
        return $this->state(function (array $attributes) use ($amount) {
            $finalAmount = $amount ?? $this->faker->randomFloat(2, 500, 3000);

            return [
                'description' => 'Miscellaneous Fee',
                'quantity' => 1,
                'unit_price' => $finalAmount,
                'amount' => $finalAmount,
            ];
        });
    }

    /**
     * Create a custom fee item.
     */
    public function customFee(string $description, float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $description,
            'quantity' => 1,
            'unit_price' => $amount,
            'amount' => $amount,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\Quarter;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Enrollment>
 */
class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tuitionFee = $this->faker->numberBetween(15000, 30000) * 100; // in cents
        $miscFee = $this->faker->numberBetween(3000, 8000) * 100;
        $labFee = $this->faker->numberBetween(1000, 5000) * 100;
        $totalAmount = $tuitionFee + $miscFee + $labFee;
        $amountPaid = $this->faker->numberBetween(0, $totalAmount);
        $balance = $totalAmount - $amountPaid;

        return [
            'student_id' => Student::factory(),
            'guardian_id' => function (array $attributes) {
                // Get a guardian user or create one
                $guardianUser = \App\Models\User::whereHas('roles', function ($q) {
                    $q->where('name', 'guardian');
                })->inRandomOrder()->first();

                if (! $guardianUser) {
                    $guardianUser = \App\Models\User::factory()->create();
                    $guardianUser->assignRole('guardian');

                    // Create Guardian model
                    Guardian::create([
                        'user_id' => $guardianUser->id,
                        'first_name' => $this->faker->firstName,
                        'last_name' => $this->faker->lastName,
                        'contact_number' => $this->faker->phoneNumber,
                        'address' => $this->faker->address,
                    ]);
                }

                return $guardianUser->id;
            },
            'school_year' => $this->faker->randomElement(['2023-2024', '2024-2025', '2025-2026']),
            'quarter' => $this->faker->randomElement(Quarter::values()),
            'status' => $this->faker->randomElement(EnrollmentStatus::values()),
            'tuition_fee_cents' => $tuitionFee,
            'miscellaneous_fee_cents' => $miscFee,
            'laboratory_fee_cents' => $labFee,
            'total_amount_cents' => $totalAmount,
            'net_amount_cents' => $totalAmount,
            'amount_paid_cents' => $amountPaid,
            'balance_cents' => $balance,
            'payment_status' => $balance == 0 ? PaymentStatus::PAID : ($amountPaid > 0 ? PaymentStatus::PARTIAL : PaymentStatus::PENDING),
            'approved_at' => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    /**
     * Indicate that the enrollment is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EnrollmentStatus::APPROVED,
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the enrollment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EnrollmentStatus::PENDING,
            'approved_at' => null,
        ]);
    }

    /**
     * Indicate that the enrollment is fully paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount_paid_cents' => $attributes['total_amount_cents'],
            'balance_cents' => 0,
            'payment_status' => PaymentStatus::PAID,
        ]);
    }
}

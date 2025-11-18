<?php

namespace Database\Factories;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\PaymentPlan;
use App\Enums\PaymentStatus;
use App\Enums\Quarter;
use App\Models\Enrollment;
use App\Models\EnrollmentPeriod;
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
        $libraryFee = $this->faker->numberBetween(500, 1500) * 100;
        $sportsFee = $this->faker->numberBetween(500, 2000) * 100;
        $discount = $this->faker->optional(0.2)->numberBetween(500, 5000) * 100; // 20% chance of discount

        $totalAmount = $tuitionFee + $miscFee + $labFee + $libraryFee + $sportsFee;
        $netAmount = $totalAmount - ($discount ?? 0);
        $amountPaid = $this->faker->numberBetween(0, $netAmount);
        $balance = $netAmount - $amountPaid;

        // Generate a unique enrollment ID
        $enrollmentId = 'ENR-'.date('Y').'-'.str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT);

        $status = $this->faker->randomElement(EnrollmentStatus::values());
        $approvedAt = null;
        $rejectedAt = null;
        $approvedBy = null;

        if ($status === EnrollmentStatus::APPROVED->value) {
            $approvedAt = $this->faker->dateTimeBetween('-1 week', 'now');
            $approvedBy = \App\Models\User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['super-admin', 'administrator', 'registrar']);
            })->inRandomOrder()->value('id');
        } elseif ($status === EnrollmentStatus::REJECTED->value) {
            $rejectedAt = $this->faker->dateTimeBetween('-1 week', 'now');
        }

        return [
            'enrollment_id' => $enrollmentId,
            'student_id' => Student::factory(),
            'guardian_id' => function (array $attributes) {
                // Get an existing guardian or create one
                $guardian = Guardian::inRandomOrder()->first();

                if (! $guardian) {
                    $guardianUser = \App\Models\User::factory()->create();
                    $guardianUser->assignRole('guardian');

                    // Create Guardian model
                    $guardian = Guardian::create([
                        'user_id' => $guardianUser->id,
                        'first_name' => $this->faker->firstName,
                        'last_name' => $this->faker->lastName,
                        'contact_number' => $this->faker->phoneNumber,
                        'address' => $this->faker->address,
                    ]);
                }

                return $guardian->id;
            },
            'school_year_id' => \App\Models\SchoolYear::factory(),
            'enrollment_period_id' => EnrollmentPeriod::factory(),
            'quarter' => $this->faker->randomElement(Quarter::values()),
            'grade_level' => $this->faker->randomElement(GradeLevel::values()),
            'payment_plan' => $this->faker->randomElement(PaymentPlan::values()),
            'status' => $status,
            'tuition_fee_cents' => $tuitionFee,
            'miscellaneous_fee_cents' => $miscFee,
            'laboratory_fee_cents' => $labFee,
            'library_fee_cents' => $libraryFee,
            'sports_fee_cents' => $sportsFee,
            'total_amount_cents' => $totalAmount,
            'discount_cents' => $discount ?? 0,
            'net_amount_cents' => $netAmount,
            'amount_paid_cents' => $amountPaid,
            'balance_cents' => $balance,
            'payment_status' => $balance == 0 ? PaymentStatus::PAID : ($amountPaid > 0 ? PaymentStatus::PARTIAL : PaymentStatus::PENDING),
            'payment_due_date' => $this->faker->dateTimeBetween('now', '+3 months'),
            'remarks' => $this->faker->optional(0.3)->sentence(), // 30% chance of having remarks
            'approved_at' => $approvedAt,
            'rejected_at' => $rejectedAt,
            'approved_by' => $approvedBy,
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
            'rejected_at' => null,
            'approved_by' => \App\Models\User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['super-admin', 'administrator', 'registrar']);
            })->inRandomOrder()->value('id') ?? 1,
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
            'rejected_at' => null,
            'approved_by' => null,
        ]);
    }

    /**
     * Indicate that the enrollment is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EnrollmentStatus::REJECTED,
            'approved_at' => null,
            'rejected_at' => now(),
            'approved_by' => null,
            'remarks' => $this->faker->sentence(),
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

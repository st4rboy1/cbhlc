<?php

namespace Database\Factories;

use App\Enums\GradeLevel;
use App\Models\EnrollmentPeriod;
use App\Models\GradeLevelFee;
use App\Models\SchoolYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GradeLevelFee>
 */
class GradeLevelFeeFactory extends Factory
{
    protected $model = GradeLevelFee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get or create an enrollment period
        $enrollmentPeriod = EnrollmentPeriod::first();

        if (! $enrollmentPeriod) {
            // Create a default school year if needed
            $currentYear = now()->year;
            $schoolYear = SchoolYear::firstOrCreate(
                ['name' => $currentYear.'-'.($currentYear + 1)],
                [
                    'start_year' => $currentYear,
                    'end_year' => $currentYear + 1,
                    'start_date' => $currentYear.'-06-01',
                    'end_date' => ($currentYear + 1).'-03-31',
                    'status' => 'active',
                    'is_active' => true,
                ]
            );

            // Create enrollment period for this school year
            $enrollmentPeriod = EnrollmentPeriod::create([
                'school_year_id' => $schoolYear->id,
                'start_date' => $currentYear.'-06-01',
                'end_date' => ($currentYear + 1).'-03-31',
                'early_registration_deadline' => $currentYear.'-05-31',
                'regular_registration_deadline' => $currentYear.'-07-31',
                'late_registration_deadline' => $currentYear.'-08-31',
                'status' => 'active',
                'description' => "School Year {$currentYear}-".($currentYear + 1).' Enrollment Period',
                'allow_new_students' => true,
                'allow_returning_students' => true,
            ]);
        }

        return [
            'grade_level' => $this->faker->randomElement(GradeLevel::values()),
            'enrollment_period_id' => $enrollmentPeriod->id,
            'tuition_fee_cents' => $this->faker->numberBetween(2000000, 5000000), // 20,000 to 50,000 pesos
            'registration_fee_cents' => $this->faker->numberBetween(100000, 300000), // 1,000 to 3,000 pesos
            'miscellaneous_fee_cents' => $this->faker->numberBetween(50000, 150000), // 500 to 1,500 pesos
            'laboratory_fee_cents' => $this->faker->numberBetween(0, 100000), // 0 to 1,000 pesos
            'library_fee_cents' => $this->faker->numberBetween(20000, 50000), // 200 to 500 pesos
            'sports_fee_cents' => $this->faker->numberBetween(10000, 30000), // 100 to 300 pesos
            'other_fees_cents' => $this->faker->numberBetween(0, 50000), // 0 to 500 pesos
            'payment_terms' => $this->faker->randomElement(['ANNUAL', 'SEMESTRAL', 'QUARTERLY', 'MONTHLY']),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the grade level fee is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set a specific grade level.
     */
    public function gradeLevel(GradeLevel $gradeLevel): static
    {
        return $this->state(fn (array $attributes) => [
            'grade_level' => $gradeLevel->value,
        ]);
    }

    /**
     * Set a specific school year.
     */
    public function schoolYear(string $schoolYear): static
    {
        return $this->state(function (array $attributes) use ($schoolYear) {
            // Parse the school year string (e.g., "2024-2025")
            $years = explode('-', $schoolYear);
            $startYear = (int) $years[0];
            $endYear = (int) $years[1];

            // Create or get existing school year
            $schoolYearModel = SchoolYear::firstOrCreate(
                ['name' => $schoolYear],
                [
                    'start_year' => $startYear,
                    'end_year' => $endYear,
                    'start_date' => $startYear.'-06-01',
                    'end_date' => $endYear.'-03-31',
                    'status' => 'active',
                    'is_active' => true,
                ]
            );

            // Create or get enrollment period for this school year
            $enrollmentPeriod = EnrollmentPeriod::firstOrCreate(
                [
                    'school_year_id' => $schoolYearModel->id,
                ],
                [
                    'start_date' => $startYear.'-06-01',
                    'end_date' => $endYear.'-03-31',
                    'early_registration_deadline' => $startYear.'-05-31',
                    'regular_registration_deadline' => $startYear.'-07-31',
                    'late_registration_deadline' => $startYear.'-08-31',
                    'status' => 'active',
                    'description' => "School Year {$schoolYear} Enrollment Period",
                    'allow_new_students' => true,
                    'allow_returning_students' => true,
                ]
            );

            return [
                'enrollment_period_id' => $enrollmentPeriod->id,
            ];
        });
    }
}

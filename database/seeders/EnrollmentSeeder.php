<?php

namespace Database\Seeders;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\PaymentStatus;
use App\Models\Enrollment;
use App\Models\EnrollmentPeriod;
use App\Models\GradeLevelFee;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Idempotent: Checks if enrollments already exist before creating
     */
    public function run(): void
    {
        // Check if enrollments already exist
        if (Enrollment::count() > 0) {
            $this->command->info('Enrollments already exist. Skipping enrollment seeder.');

            return;
        }

        $this->command->info('Seeding enrollments...');

        // Get or create enrollment period
        $enrollmentPeriod = EnrollmentPeriod::firstOrCreate(
            ['school_year' => '2024-2025'],
            [
                'start_date' => '2024-06-01',
                'end_date' => '2025-03-31',
                'early_registration_deadline' => '2024-07-15',
                'regular_registration_deadline' => '2024-08-15',
                'late_registration_deadline' => '2024-09-15',
                'status' => 'active',
                'allow_new_students' => true,
                'allow_returning_students' => true,
            ]
        );

        // Get super admin for approvals
        $superAdmin = User::role('super_admin')->first();

        if (! $superAdmin) {
            $this->command->error('No super admin found. Please run UserSeeder first.');

            return;
        }

        // Get students and guardians
        $students = Student::with('guardians')->get();

        if ($students->isEmpty()) {
            $this->command->warn('No students found. Creating sample students...');
            $students = Student::factory()->count(10)->create();
        }

        $guardians = Guardian::all();

        if ($guardians->isEmpty()) {
            $this->command->warn('No guardians found. Creating sample guardians...');
            $guardians = Guardian::factory()->count(5)->create();
        }

        $enrollmentCount = 0;

        foreach ($students as $student) {
            // Get or assign guardian
            $guardian = $student->guardians()->first() ?? $guardians->random();

            // Ensure student has a guardian relationship if not
            if (! $student->guardians()->where('guardian_id', $guardian->id)->exists()) {
                $student->guardians()->attach($guardian->id, [
                    'relationship_type' => 'parent',
                    'is_primary_contact' => true,
                ]);
            }

            // Get or create grade level fee
            $gradeLevel = $student->grade_level ?? GradeLevel::GRADE_1;
            $gradeLevelFee = GradeLevelFee::where('grade_level', $gradeLevel)->first();

            if (! $gradeLevelFee) {
                $gradeLevelFee = GradeLevelFee::factory()->create([
                    'grade_level' => $gradeLevel,
                ]);
            }

            // Calculate fees (in cents)
            $tuitionFeeCents = $gradeLevelFee->tuition_fee_cents;
            $miscellaneousFeeCents = $gradeLevelFee->miscellaneous_fee_cents;
            $laboratoryFeeCents = $gradeLevelFee->laboratory_fee_cents ?? 0;
            $libraryFeeCents = $gradeLevelFee->library_fee_cents ?? 0;
            $sportsFeeCents = $gradeLevelFee->sports_fee_cents ?? 0;

            $totalAmountCents = $tuitionFeeCents + $miscellaneousFeeCents + $laboratoryFeeCents + $libraryFeeCents + $sportsFeeCents;
            $discountCents = 0; // No discount for seeded data
            $netAmountCents = $totalAmountCents - $discountCents;

            // Randomly decide payment status
            $paymentStatuses = [PaymentStatus::PENDING, PaymentStatus::PARTIAL, PaymentStatus::PAID];
            $paymentStatus = fake()->randomElement($paymentStatuses);

            $amountPaidCents = match ($paymentStatus) {
                PaymentStatus::PAID => $netAmountCents,
                PaymentStatus::PARTIAL => (int) ($netAmountCents * fake()->randomFloat(2, 0.3, 0.7)),
                default => 0,
            };

            $balanceCents = $netAmountCents - $amountPaidCents;

            // Create enrollment
            $enrollment = Enrollment::create([
                'enrollment_id' => 'ENR-'.now()->format('Ym').'-'.str_pad((string) ($enrollmentCount + 1), 4, '0', STR_PAD_LEFT),
                'student_id' => $student->id,
                'guardian_id' => $guardian->id,
                'school_year' => '2024-2025',
                'enrollment_period_id' => $enrollmentPeriod->id,
                'quarter' => 'first',
                'grade_level' => $gradeLevel,
                'status' => EnrollmentStatus::APPROVED,
                'tuition_fee_cents' => $tuitionFeeCents,
                'miscellaneous_fee_cents' => $miscellaneousFeeCents,
                'laboratory_fee_cents' => $laboratoryFeeCents,
                'library_fee_cents' => $libraryFeeCents,
                'sports_fee_cents' => $sportsFeeCents,
                'total_amount_cents' => $totalAmountCents,
                'discount_cents' => $discountCents,
                'net_amount_cents' => $netAmountCents,
                'payment_status' => $paymentStatus,
                'amount_paid_cents' => $amountPaidCents,
                'balance_cents' => $balanceCents,
                'payment_due_date' => now()->addDays(30),
                'approved_at' => now()->subDays(rand(1, 30)),
                'approved_by' => $superAdmin->id,
            ]);

            $enrollmentCount++;
        }

        $this->command->info("Created {$enrollmentCount} enrollments successfully.");
    }
}

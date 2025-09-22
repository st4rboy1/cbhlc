<?php

namespace Database\Seeders;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\PaymentStatus;
use App\Enums\Quarter;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\GuardianStudent;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only create default users if we're not in production
        // or if the users table is empty (initial setup)
        if (app()->environment('local', 'testing') || User::count() === 0) {
            $this->createDefaultUsers();
        }
    }

    /**
     * Create default users without using factories (for production compatibility)
     */
    private function createDefaultUsers(): void
    {
        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'super.admin@cbhlc.edu'],
            [
                'name' => 'Super Admin',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );
        if (! $superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole('super_admin');
        }

        // Administrator
        $admin = User::firstOrCreate(
            ['email' => 'admin@cbhlc.edu'],
            [
                'name' => 'Administrator',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );
        if (! $admin->hasRole('administrator')) {
            $admin->assignRole('administrator');
        }

        // Registrar
        $registrar = User::firstOrCreate(
            ['email' => 'registrar@cbhlc.edu'],
            [
                'name' => 'Registrar User',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );
        if (! $registrar->hasRole('registrar')) {
            $registrar->assignRole('registrar');
        }

        // Guardian with children
        $guardianUser = User::firstOrCreate(
            ['email' => 'maria.santos@example.com'],
            [
                'name' => 'Maria Santos',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );
        if (! $guardianUser->hasRole('guardian')) {
            $guardianUser->assignRole('guardian');
        }

        // Create Guardian profile record
        Guardian::firstOrCreate(
            ['user_id' => $guardianUser->id],
            [
                'first_name' => 'Maria',
                'middle_name' => 'Cruz',
                'last_name' => 'Santos',
                'contact_number' => '+63987654321',
                'address' => '123 Rizal Street, Pasig City',
                'occupation' => 'Teacher',
                'employer' => 'Department of Education',
                'emergency_contact_name' => 'Juan Santos',
                'emergency_contact_phone' => '+63912345678',
                'emergency_contact_relationship' => 'Spouse',
            ]
        );

        // Create students linked to the guardian
        $this->createStudentsForGuardian($guardianUser);
    }

    /**
     * Create student records for a guardian (all students get login accounts)
     */
    private function createStudentsForGuardian(User $guardian): void
    {
        // Create first child with login account
        $studentUser1 = User::firstOrCreate(
            ['email' => 'juan.santos@student.cbhlc.edu'],
            [
                'name' => 'Juan Santos',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );
        if (! $studentUser1->hasRole('student')) {
            $studentUser1->assignRole('student');
        }

        $student1 = Student::firstOrCreate(
            ['first_name' => 'Juan', 'last_name' => 'Santos', 'birthdate' => '2012-03-15'],
            [
                'student_id' => Student::generateStudentId(),
                'middle_name' => 'Garcia',
                'gender' => 'Male',
                'grade_level' => GradeLevel::GRADE_6->value,
                'address' => '123 Rizal Street, Pasig City',
                'contact_number' => '+63912345678',
                'guardian_name' => 'Maria Santos',
                'guardian_contact' => '+63987654321',
                'guardian_email' => 'maria.santos@example.com',
                'user_id' => $studentUser1->id,
            ]
        );

        // Link student to guardian (check if relationship already exists)
        GuardianStudent::firstOrCreate([
            'guardian_id' => $guardian->id,
            'student_id' => $student1->id,
        ], [
            'relationship_type' => 'mother',
            'is_primary_contact' => true,
        ]);

        // Create second child with login account (all students now get login accounts)
        $studentUser2 = User::firstOrCreate(
            ['email' => 'ana.santos@student.cbhlc.edu'],
            [
                'name' => 'Ana Santos',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );
        if (! $studentUser2->hasRole('student')) {
            $studentUser2->assignRole('student');
        }

        $student2 = Student::firstOrCreate(
            ['first_name' => 'Ana', 'last_name' => 'Santos', 'birthdate' => '2015-08-22'],
            [
                'student_id' => Student::generateStudentId(),
                'middle_name' => 'Garcia',
                'gender' => 'Female',
                'grade_level' => GradeLevel::GRADE_3->value,
                'address' => '123 Rizal Street, Pasig City',
                'contact_number' => '+63912345678',
                'guardian_name' => 'Maria Santos',
                'guardian_contact' => '+63987654321',
                'guardian_email' => 'maria.santos@example.com',
                'user_id' => $studentUser2->id,
            ]
        );

        // Link second student to guardian
        GuardianStudent::firstOrCreate([
            'guardian_id' => $guardian->id,
            'student_id' => $student2->id,
        ], [
            'relationship_type' => 'mother',
            'is_primary_contact' => false,
        ]);

        // Create enrollment history for Juan (old student)
        $this->createEnrollmentHistory($student1, $guardian);

        // Ana is a new student with no enrollment history
    }

    /**
     * Create enrollment history to make a student an "old student"
     */
    private function createEnrollmentHistory(Student $student, User $guardian): void
    {
        // Create completed enrollments for previous years
        // Juan was in Grade 4 in 2022-2023 (completed)
        Enrollment::firstOrCreate([
            'student_id' => $student->id,
            'school_year' => '2022-2023',
        ], [
            'guardian_id' => $guardian->id,
            'quarter' => Quarter::FIRST,
            'grade_level' => GradeLevel::GRADE_4->value,
            'status' => EnrollmentStatus::COMPLETED,
            'tuition_fee_cents' => 2000000, // 20,000
            'miscellaneous_fee_cents' => 500000, // 5,000
            'laboratory_fee_cents' => 200000, // 2,000
            'total_amount_cents' => 2700000,
            'net_amount_cents' => 2700000,
            'amount_paid_cents' => 2700000,
            'balance_cents' => 0,
            'payment_status' => PaymentStatus::PAID,
            'approved_at' => now()->subYears(2),
        ]);

        // Juan was in Grade 5 in 2023-2024 (completed)
        Enrollment::firstOrCreate([
            'student_id' => $student->id,
            'school_year' => '2023-2024',
        ], [
            'guardian_id' => $guardian->id,
            'quarter' => Quarter::FIRST,
            'grade_level' => GradeLevel::GRADE_5->value,
            'status' => EnrollmentStatus::COMPLETED,
            'tuition_fee_cents' => 2200000, // 22,000
            'miscellaneous_fee_cents' => 500000, // 5,000
            'laboratory_fee_cents' => 300000, // 3,000
            'total_amount_cents' => 3000000,
            'net_amount_cents' => 3000000,
            'amount_paid_cents' => 3000000,
            'balance_cents' => 0,
            'payment_status' => PaymentStatus::PAID,
            'approved_at' => now()->subYear(),
        ]);

        // Juan is currently enrolled in Grade 6 in 2024-2025
        Enrollment::firstOrCreate([
            'student_id' => $student->id,
            'school_year' => '2024-2025',
        ], [
            'guardian_id' => $guardian->id,
            'quarter' => Quarter::FIRST,
            'grade_level' => GradeLevel::GRADE_6->value,
            'status' => EnrollmentStatus::ENROLLED,
            'tuition_fee_cents' => 2500000, // 25,000
            'miscellaneous_fee_cents' => 600000, // 6,000
            'laboratory_fee_cents' => 400000, // 4,000
            'total_amount_cents' => 3500000,
            'net_amount_cents' => 3500000,
            'amount_paid_cents' => 1750000, // Partial payment
            'balance_cents' => 1750000,
            'payment_status' => PaymentStatus::PARTIAL,
            'approved_at' => now()->subMonths(3),
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\PaymentStatus;
use App\Enums\Quarter;
use App\Enums\RelationshipType;
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
            $this->createAdditionalTestData();
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
     * Create additional test data for comprehensive dashboard testing
     */
    private function createAdditionalTestData(): void
    {
        // Guardian 2 - Roberto Dela Cruz with pending applications
        $guardian2 = User::firstOrCreate(
            ['email' => 'roberto.delacruz@example.com'],
            [
                'name' => 'Roberto Dela Cruz',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );
        if (! $guardian2->hasRole('guardian')) {
            $guardian2->assignRole('guardian');
        }

        Guardian::firstOrCreate(
            ['user_id' => $guardian2->id],
            [
                'first_name' => 'Roberto',
                'middle_name' => 'Santos',
                'last_name' => 'Dela Cruz',
                'contact_number' => '+63917123456',
                'address' => '456 Bonifacio Street, Makati City',
                'occupation' => 'Engineer',
                'employer' => 'Tech Company Inc.',
                'emergency_contact_name' => 'Carmen Dela Cruz',
                'emergency_contact_phone' => '+63918234567',
                'emergency_contact_relationship' => 'Spouse',
            ]
        );

        // Create students with pending enrollments
        $this->createPendingEnrollmentStudents($guardian2);

        // Guardian 3 - Linda Garcia with rejected and overdue payments
        $guardian3 = User::firstOrCreate(
            ['email' => 'linda.garcia@example.com'],
            [
                'name' => 'Linda Garcia',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );
        if (! $guardian3->hasRole('guardian')) {
            $guardian3->assignRole('guardian');
        }

        Guardian::firstOrCreate(
            ['user_id' => $guardian3->id],
            [
                'first_name' => 'Linda',
                'middle_name' => 'Reyes',
                'last_name' => 'Garcia',
                'contact_number' => '+63927345678',
                'address' => '789 Aguinaldo Avenue, Quezon City',
                'occupation' => 'Business Owner',
                'employer' => 'Self-employed',
                'emergency_contact_name' => 'Pedro Garcia',
                'emergency_contact_phone' => '+63928456789',
                'emergency_contact_relationship' => 'Spouse',
            ]
        );

        // Create students with various enrollment statuses
        $this->createMixedStatusStudents($guardian3);

        // Guardian 4 - New guardian with new students
        $guardian4 = User::firstOrCreate(
            ['email' => 'jose.mendoza@example.com'],
            [
                'name' => 'Jose Mendoza',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );
        if (! $guardian4->hasRole('guardian')) {
            $guardian4->assignRole('guardian');
        }

        Guardian::firstOrCreate(
            ['user_id' => $guardian4->id],
            [
                'first_name' => 'Jose',
                'middle_name' => 'Cruz',
                'last_name' => 'Mendoza',
                'contact_number' => '+63937567890',
                'address' => '321 Luna Street, Taguig City',
                'occupation' => 'Doctor',
                'employer' => 'City Medical Center',
                'emergency_contact_name' => 'Anna Mendoza',
                'emergency_contact_phone' => '+63938678901',
                'emergency_contact_relationship' => 'Spouse',
            ]
        );

        // Create new students (no enrollment history)
        $this->createNewStudents($guardian4);
    }

    /**
     * Create student records for a guardian (all students get login accounts)
     */
    private function createStudentsForGuardian(User $guardian): void
    {
        $guardianModel = Guardian::where('user_id', $guardian->id)->first();

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
                'user_id' => $studentUser1->id,
            ]
        );

        // Link student to guardian (check if relationship already exists)
        GuardianStudent::firstOrCreate([
            'guardian_id' => $guardianModel->id,
            'student_id' => $student1->id,
        ], [
            'relationship_type' => RelationshipType::MOTHER->value,
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
                'user_id' => $studentUser2->id,
            ]
        );

        // Link second student to guardian
        GuardianStudent::firstOrCreate([
            'guardian_id' => $guardianModel->id,
            'student_id' => $student2->id,
        ], [
            'relationship_type' => RelationshipType::MOTHER->value,
            'is_primary_contact' => false,
        ]);

        // Create enrollment history for Juan (old student)
        $this->createEnrollmentHistory($student1, $guardian);

        // Ana is a new student with no enrollment history
    }

    /**
     * Create students with pending enrollment applications
     */
    private function createPendingEnrollmentStudents(User $guardian): void
    {
        $guardianModel = Guardian::where('user_id', $guardian->id)->first();

        // Get or create school years
        $sy2025 = \App\Models\SchoolYear::firstOrCreate(
            ['name' => '2025-2026'],
            ['start_year' => 2025, 'end_year' => 2026, 'start_date' => '2025-06-01', 'end_date' => '2026-05-31', 'status' => 'active']
        );
        $sy2024 = \App\Models\SchoolYear::firstOrCreate(
            ['name' => '2024-2025'],
            ['start_year' => 2024, 'end_year' => 2025, 'start_date' => '2024-06-01', 'end_date' => '2025-05-31', 'status' => 'closed']
        );

        // Student 1 - Pending enrollment for Grade 5
        $student1 = Student::firstOrCreate(
            ['first_name' => 'Miguel', 'last_name' => 'Dela Cruz', 'birthdate' => '2013-05-20'],
            [
                'student_id' => Student::generateStudentId(),
                'middle_name' => 'Santos',
                'gender' => 'Male',
                'grade_level' => GradeLevel::GRADE_4->value,
                'address' => '456 Bonifacio Street, Makati City',
                'contact_number' => '+63917123456',
            ]
        );

        GuardianStudent::firstOrCreate([
            'guardian_id' => $guardianModel->id,
            'student_id' => $student1->id,
        ], [
            'relationship_type' => RelationshipType::FATHER->value,
            'is_primary_contact' => true,
        ]);

        // Create pending enrollment for next school year
        Enrollment::firstOrCreate([
            'student_id' => $student1->id,
            'school_year_id' => $sy2025->id,
        ], [
            'guardian_id' => $guardianModel->id,
            'quarter' => Quarter::FIRST,
            'grade_level' => GradeLevel::GRADE_5->value,
            'status' => EnrollmentStatus::PENDING,
            'tuition_fee_cents' => 2300000,
            'miscellaneous_fee_cents' => 550000,
            'laboratory_fee_cents' => 300000,
            'total_amount_cents' => 3150000,
            'net_amount_cents' => 3150000,
            'amount_paid_cents' => 0,
            'balance_cents' => 3150000,
            'payment_status' => PaymentStatus::PENDING,
            'created_at' => now()->subDays(2),
        ]);

        // Student 2 - Another pending enrollment
        $student2 = Student::firstOrCreate(
            ['first_name' => 'Sofia', 'last_name' => 'Dela Cruz', 'birthdate' => '2014-11-10'],
            [
                'student_id' => Student::generateStudentId(),
                'middle_name' => 'Santos',
                'gender' => 'Female',
                'grade_level' => GradeLevel::GRADE_3->value,
                'address' => '456 Bonifacio Street, Makati City',
                'contact_number' => '+63917123456',
            ]
        );

        GuardianStudent::firstOrCreate([
            'guardian_id' => $guardianModel->id,
            'student_id' => $student2->id,
        ], [
            'relationship_type' => RelationshipType::FATHER->value,
            'is_primary_contact' => false,
        ]);

        // Pending enrollment for Grade 4
        Enrollment::firstOrCreate([
            'student_id' => $student2->id,
            'school_year_id' => $sy2025->id,
        ], [
            'guardian_id' => $guardianModel->id,
            'quarter' => Quarter::FIRST,
            'grade_level' => GradeLevel::GRADE_4->value,
            'status' => EnrollmentStatus::PENDING,
            'tuition_fee_cents' => 2200000,
            'miscellaneous_fee_cents' => 500000,
            'laboratory_fee_cents' => 250000,
            'total_amount_cents' => 2950000,
            'net_amount_cents' => 2950000,
            'amount_paid_cents' => 0,
            'balance_cents' => 2950000,
            'payment_status' => PaymentStatus::PENDING,
            'created_at' => now()->subDays(5),
        ]);
    }

    /**
     * Create students with mixed enrollment statuses (rejected, overdue payments)
     */
    private function createMixedStatusStudents(User $guardian): void
    {
        $guardianModel = Guardian::where('user_id', $guardian->id)->first();

        // Get or create school years
        $sy2025 = \App\Models\SchoolYear::firstOrCreate(
            ['name' => '2025-2026'],
            ['start_year' => 2025, 'end_year' => 2026, 'start_date' => '2025-06-01', 'end_date' => '2026-05-31', 'status' => 'active']
        );
        $sy2024 = \App\Models\SchoolYear::firstOrCreate(
            ['name' => '2024-2025'],
            ['start_year' => 2024, 'end_year' => 2025, 'start_date' => '2024-06-01', 'end_date' => '2025-05-31', 'status' => 'closed']
        );

        // Student 1 - With rejected enrollment
        $student1 = Student::firstOrCreate(
            ['first_name' => 'Carlos', 'last_name' => 'Garcia', 'birthdate' => '2011-07-15'],
            [
                'student_id' => Student::generateStudentId(),
                'middle_name' => 'Reyes',
                'gender' => 'Male',
                'grade_level' => GradeLevel::GRADE_5->value,
                'address' => '789 Aguinaldo Avenue, Quezon City',
                'contact_number' => '+63927345678',
            ]
        );

        GuardianStudent::firstOrCreate([
            'guardian_id' => $guardianModel->id,
            'student_id' => $student1->id,
        ], [
            'relationship_type' => RelationshipType::MOTHER->value,
            'is_primary_contact' => true,
        ]);

        // Create rejected enrollment
        Enrollment::firstOrCreate([
            'student_id' => $student1->id,
            'school_year_id' => $sy2025->id,
        ], [
            'guardian_id' => $guardianModel->id,
            'quarter' => Quarter::SECOND,
            'grade_level' => GradeLevel::GRADE_6->value,
            'status' => EnrollmentStatus::REJECTED,
            'tuition_fee_cents' => 2800000,
            'miscellaneous_fee_cents' => 600000,
            'laboratory_fee_cents' => 500000,
            'total_amount_cents' => 3900000,
            'net_amount_cents' => 3900000,
            'amount_paid_cents' => 0,
            'balance_cents' => 3900000,
            'payment_status' => PaymentStatus::PENDING,
            'remarks' => 'Incomplete documentation',
            'created_at' => now()->subDays(10),
            'approved_at' => now()->subDays(7),
        ]);

        // Student 2 - With overdue payment
        $student2 = Student::firstOrCreate(
            ['first_name' => 'Isabella', 'last_name' => 'Garcia', 'birthdate' => '2013-02-28'],
            [
                'student_id' => Student::generateStudentId(),
                'middle_name' => 'Reyes',
                'gender' => 'Female',
                'grade_level' => GradeLevel::GRADE_5->value,
                'address' => '789 Aguinaldo Avenue, Quezon City',
                'contact_number' => '+63927345678',
            ]
        );

        GuardianStudent::firstOrCreate([
            'guardian_id' => $guardianModel->id,
            'student_id' => $student2->id,
        ], [
            'relationship_type' => RelationshipType::MOTHER->value,
            'is_primary_contact' => false,
        ]);

        // Create enrollment with overdue payment
        Enrollment::firstOrCreate([
            'student_id' => $student2->id,
            'school_year_id' => $sy2024->id,
        ], [
            'guardian_id' => $guardianModel->id,
            'quarter' => Quarter::FIRST,
            'grade_level' => GradeLevel::GRADE_5->value,
            'status' => EnrollmentStatus::ENROLLED,
            'tuition_fee_cents' => 2300000,
            'miscellaneous_fee_cents' => 550000,
            'laboratory_fee_cents' => 300000,
            'total_amount_cents' => 3150000,
            'net_amount_cents' => 3150000,
            'amount_paid_cents' => 500000, // Only paid 5,000 out of 31,500
            'balance_cents' => 2650000,
            'payment_status' => PaymentStatus::OVERDUE,
            'approved_at' => now()->subMonths(6),
            'created_at' => now()->subMonths(7),
        ]);
    }

    /**
     * Create new students with no enrollment history
     */
    private function createNewStudents(User $guardian): void
    {
        $guardianModel = Guardian::where('user_id', $guardian->id)->first();

        // Get or create school years
        $sy2025 = \App\Models\SchoolYear::firstOrCreate(
            ['name' => '2025-2026'],
            ['start_year' => 2025, 'end_year' => 2026, 'start_date' => '2025-06-01', 'end_date' => '2026-05-31', 'status' => 'active']
        );

        // Student 1 - New to school
        $student1 = Student::firstOrCreate(
            ['first_name' => 'Gabriel', 'last_name' => 'Mendoza', 'birthdate' => '2016-04-12'],
            [
                'student_id' => Student::generateStudentId(),
                'middle_name' => 'Cruz',
                'gender' => 'Male',
                'grade_level' => null, // New student, no grade level yet
                'address' => '321 Luna Street, Taguig City',
                'contact_number' => '+63937567890',
            ]
        );

        GuardianStudent::firstOrCreate([
            'guardian_id' => $guardianModel->id,
            'student_id' => $student1->id,
        ], [
            'relationship_type' => RelationshipType::FATHER->value,
            'is_primary_contact' => true,
        ]);

        // Pending enrollment for Kindergarten
        Enrollment::firstOrCreate([
            'student_id' => $student1->id,
            'school_year_id' => $sy2025->id,
        ], [
            'guardian_id' => $guardianModel->id,
            'quarter' => Quarter::FIRST,
            'grade_level' => GradeLevel::KINDER->value,
            'status' => EnrollmentStatus::PENDING,
            'tuition_fee_cents' => 1800000,
            'miscellaneous_fee_cents' => 400000,
            'laboratory_fee_cents' => 0,
            'total_amount_cents' => 2200000,
            'net_amount_cents' => 2200000,
            'amount_paid_cents' => 0,
            'balance_cents' => 2200000,
            'payment_status' => PaymentStatus::PENDING,
            'created_at' => now()->subDay(),
        ]);

        // Student 2 - New student for Grade 1
        $student2 = Student::firstOrCreate(
            ['first_name' => 'Sophia', 'last_name' => 'Mendoza', 'birthdate' => '2017-09-05'],
            [
                'student_id' => Student::generateStudentId(),
                'middle_name' => 'Cruz',
                'gender' => 'Female',
                'grade_level' => null, // New student, no grade level yet
                'address' => '321 Luna Street, Taguig City',
                'contact_number' => '+63937567890',
            ]
        );

        GuardianStudent::firstOrCreate([
            'guardian_id' => $guardianModel->id,
            'student_id' => $student2->id,
        ], [
            'relationship_type' => RelationshipType::FATHER->value,
            'is_primary_contact' => false,
        ]);

        // Create pending enrollment for Grade 1
        Enrollment::firstOrCreate([
            'student_id' => $student2->id,
            'school_year_id' => $sy2025->id,
        ], [
            'guardian_id' => $guardianModel->id,
            'quarter' => Quarter::FIRST,
            'grade_level' => GradeLevel::GRADE_1->value,
            'status' => EnrollmentStatus::PENDING,
            'tuition_fee_cents' => 1900000,
            'miscellaneous_fee_cents' => 450000,
            'laboratory_fee_cents' => 100000,
            'total_amount_cents' => 2450000,
            'net_amount_cents' => 2450000,
            'amount_paid_cents' => 0,
            'balance_cents' => 2450000,
            'payment_status' => PaymentStatus::PENDING,
            'created_at' => now()->subHours(3),
        ]);
    }

    /**
     * Create enrollment history to make a student an "old student"
     */
    private function createEnrollmentHistory(Student $student, User $guardian): void
    {
        $guardianModel = Guardian::where('user_id', $guardian->id)->first();

        // Get or create school years
        $sy2022 = \App\Models\SchoolYear::firstOrCreate(
            ['name' => '2022-2023'],
            ['start_year' => 2022, 'end_year' => 2023, 'start_date' => '2022-06-01', 'end_date' => '2023-05-31', 'status' => 'closed']
        );
        $sy2023 = \App\Models\SchoolYear::firstOrCreate(
            ['name' => '2023-2024'],
            ['start_year' => 2023, 'end_year' => 2024, 'start_date' => '2023-06-01', 'end_date' => '2024-05-31', 'status' => 'closed']
        );
        $sy2024 = \App\Models\SchoolYear::firstOrCreate(
            ['name' => '2024-2025'],
            ['start_year' => 2024, 'end_year' => 2025, 'start_date' => '2024-06-01', 'end_date' => '2025-05-31', 'status' => 'closed']
        );

        // Create completed enrollments for previous years
        // Juan was in Grade 4 in 2022-2023 (completed)
        Enrollment::firstOrCreate([
            'student_id' => $student->id,
            'school_year_id' => $sy2022->id,
        ], [
            'guardian_id' => $guardianModel->id,
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
            'school_year_id' => $sy2023->id,
        ], [
            'guardian_id' => $guardianModel->id,
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
            'school_year_id' => $sy2024->id,
        ], [
            'guardian_id' => $guardianModel->id,
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

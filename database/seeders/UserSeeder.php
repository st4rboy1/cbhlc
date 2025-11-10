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
use Illuminate\Support\Facades\Config;

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
            // Temporarily set mail driver to 'log' to prevent sending actual emails during seeding
            $originalMailDriver = Config::get('mail.default');
            Config::set('mail.default', 'log');

            try {
                $this->createDefaultUsers();
                $this->createAdditionalTestData();
            } finally {
                // Restore original mail driver
                Config::set('mail.default', $originalMailDriver);
            }
        }
    }

    /**
     * Create default users without using factories (for production compatibility)
     */
    private function createDefaultUsers(): void
    {
        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@cbhlc.edu'],
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
        // $admin = User::firstOrCreate(
        //     ['email' => 'admin@cbhlc.edu'],
        //     [
        //         'name' => 'Administrator',
        //         'email_verified_at' => now(),
        //         'password' => bcrypt('password'),
        //     ]
        // );
        // if (! $admin->hasRole('administrator')) {
        //     $admin->assignRole('administrator');
        // }

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

        // Add dashboard statistics test data (issues #315-322)
        $this->createDashboardStatisticsData();
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
            ['start_year' => 2024, 'end_year' => 2025, 'start_date' => '2024-06-01', 'end_date' => '2025-05-31', 'status' => 'completed']
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
            ['start_year' => 2024, 'end_year' => 2025, 'start_date' => '2024-06-01', 'end_date' => '2025-05-31', 'status' => 'completed']
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
            ['start_year' => 2022, 'end_year' => 2023, 'start_date' => '2022-06-01', 'end_date' => '2023-05-31', 'status' => 'completed']
        );
        $sy2023 = \App\Models\SchoolYear::firstOrCreate(
            ['name' => '2023-2024'],
            ['start_year' => 2023, 'end_year' => 2024, 'start_date' => '2023-06-01', 'end_date' => '2024-05-31', 'status' => 'completed']
        );
        $sy2024 = \App\Models\SchoolYear::firstOrCreate(
            ['name' => '2024-2025'],
            ['start_year' => 2024, 'end_year' => 2025, 'start_date' => '2024-06-01', 'end_date' => '2025-05-31', 'status' => 'completed']
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

    /**
     * Create additional data for dashboard statistics (issues #315-322)
     */
    private function createDashboardStatisticsData(): void
    {
        // Issue #315: Unverified users
        $unverifiedUser1 = User::firstOrCreate(
            ['email' => 'unverified.guardian1@example.com'],
            [
                'name' => 'Unverified Guardian One',
                'email_verified_at' => null, // Not verified
                'password' => bcrypt('password'),
            ]
        );
        if (! $unverifiedUser1->hasRole('guardian')) {
            $unverifiedUser1->assignRole('guardian');
        }

        $unverifiedUser2 = User::firstOrCreate(
            ['email' => 'unverified.guardian2@example.com'],
            [
                'name' => 'Unverified Guardian Two',
                'email_verified_at' => null, // Not verified
                'password' => bcrypt('password'),
            ]
        );
        if (! $unverifiedUser2->hasRole('guardian')) {
            $unverifiedUser2->assignRole('guardian');
        }

        // Issue #316: Guardian without students
        $guardianNoStudents = User::firstOrCreate(
            ['email' => 'no.students@example.com'],
            [
                'name' => 'Guardian Without Students',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );
        if (! $guardianNoStudents->hasRole('guardian')) {
            $guardianNoStudents->assignRole('guardian');
        }

        Guardian::firstOrCreate(
            ['user_id' => $guardianNoStudents->id],
            [
                'first_name' => 'NoStudents',
                'middle_name' => 'Test',
                'last_name' => 'Guardian',
                'contact_number' => '+63999999999',
                'address' => '999 Test Street, Manila',
                'occupation' => 'Professional',
                'employer' => 'Company Inc.',
                'emergency_contact_name' => 'Emergency Contact',
                'emergency_contact_phone' => '+63988888888',
                'emergency_contact_relationship' => 'Sibling',
            ]
        );

        // Issue #317: Guardian with students but no enrollments
        $guardianNoEnrollments = User::firstOrCreate(
            ['email' => 'no.enrollments@example.com'],
            [
                'name' => 'Guardian No Enrollments',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );
        if (! $guardianNoEnrollments->hasRole('guardian')) {
            $guardianNoEnrollments->assignRole('guardian');
        }

        $guardianNoEnrollmentsModel = Guardian::firstOrCreate(
            ['user_id' => $guardianNoEnrollments->id],
            [
                'first_name' => 'NoEnrollment',
                'middle_name' => 'Test',
                'last_name' => 'Guardian',
                'contact_number' => '+63977777777',
                'address' => '777 Test Avenue, Quezon City',
                'occupation' => 'Teacher',
                'employer' => 'School District',
                'emergency_contact_name' => 'Test Emergency',
                'emergency_contact_phone' => '+63966666666',
                'emergency_contact_relationship' => 'Parent',
            ]
        );

        // Create student for this guardian but no enrollments
        $studentNoEnrollment = Student::firstOrCreate(
            ['first_name' => 'NoEnrollment', 'last_name' => 'Student', 'birthdate' => '2015-06-15'],
            [
                'student_id' => Student::generateStudentId(),
                'middle_name' => 'Test',
                'gender' => 'Male',
                'grade_level' => null,
                'address' => '777 Test Avenue, Quezon City',
                'contact_number' => '+63977777777',
            ]
        );

        GuardianStudent::firstOrCreate([
            'guardian_id' => $guardianNoEnrollmentsModel->id,
            'student_id' => $studentNoEnrollment->id,
        ], [
            'relationship_type' => RelationshipType::MOTHER->value,
            'is_primary_contact' => true,
        ]);

        // Issue #321 and #322: Create documents with different verification statuses
        $this->createDocumentTestData();

        // Issue #318-320: Create payments for dashboard statistics
        $this->createPaymentTestData();
    }

    /**
     * Create documents with different verification statuses for dashboard statistics
     * Issues #321 and #322
     */
    private function createDocumentTestData(): void
    {
        // Get some students to attach documents to
        $students = Student::limit(5)->get();

        if ($students->isEmpty()) {
            return;
        }

        // Get or create a registrar user for document verification
        $registrarUser = User::role(['registrar', 'administrator', 'super_admin'])->first();

        if (! $registrarUser) {
            $registrarUser = User::firstOrCreate(
                ['email' => 'registrar@cbhlc.edu'],
                [
                    'name' => 'Test Registrar',
                    'email_verified_at' => now(),
                    'password' => bcrypt('password'),
                ]
            );
            $registrarUser->assignRole('registrar');
        }

        // Issue #321: Documents pending verification (at least 3)
        foreach ($students->take(3) as $index => $student) {
            \App\Models\Document::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'document_type' => \App\Enums\DocumentType::BIRTH_CERTIFICATE,
                ],
                [
                    'original_filename' => "birth_cert_{$student->student_id}.pdf",
                    'stored_filename' => "documents/{$student->student_id}/birth_cert_".time().'.pdf',
                    'file_path' => "documents/{$student->student_id}/birth_cert_".time().'.pdf',
                    'file_size' => rand(100000, 500000),
                    'mime_type' => 'application/pdf',
                    'upload_date' => now()->subDays(rand(1, 7)),
                    'verification_status' => \App\Enums\VerificationStatus::PENDING,
                ]
            );
        }

        // Issue #322: Documents with different verification statuses
        $documentTypes = [
            \App\Enums\DocumentType::FORM_138,
            \App\Enums\DocumentType::GOOD_MORAL,
            \App\Enums\DocumentType::REPORT_CARD,
        ];

        foreach ($students->take(4) as $index => $student) {
            $docType = $documentTypes[$index % count($documentTypes)];

            // Create verified document
            \App\Models\Document::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'document_type' => $docType,
                    'verification_status' => \App\Enums\VerificationStatus::VERIFIED,
                ],
                [
                    'original_filename' => strtolower($docType->value)."_{$student->student_id}.pdf",
                    'stored_filename' => "documents/{$student->student_id}/".strtolower($docType->value).'_'.time().'.pdf',
                    'file_path' => "documents/{$student->student_id}/".strtolower($docType->value).'_'.time().'.pdf',
                    'file_size' => rand(100000, 500000),
                    'mime_type' => 'application/pdf',
                    'upload_date' => now()->subDays(rand(10, 30)),
                    'verified_by' => $registrarUser->id,
                    'verified_at' => now()->subDays(rand(5, 20)),
                ]
            );

            // Create rejected document
            if ($index < 2) {
                \App\Models\Document::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'document_type' => \App\Enums\DocumentType::OTHER,
                        'verification_status' => \App\Enums\VerificationStatus::REJECTED,
                    ],
                    [
                        'original_filename' => "other_doc_{$student->student_id}.pdf",
                        'stored_filename' => "documents/{$student->student_id}/other_".time().'.pdf',
                        'file_path' => "documents/{$student->student_id}/other_".time().'.pdf',
                        'file_size' => rand(100000, 500000),
                        'mime_type' => 'application/pdf',
                        'upload_date' => now()->subDays(rand(15, 45)),
                        'verification_status' => \App\Enums\VerificationStatus::REJECTED,
                        'verified_by' => $registrarUser->id,
                        'verified_at' => now()->subDays(rand(10, 40)),
                        'rejection_reason' => 'Document is unclear or incomplete. Please re-upload a clearer copy.',
                    ]
                );
            }
        }
    }

    /**
     * Create payment test data for dashboard statistics
     * Issues #318-320
     */
    private function createPaymentTestData(): void
    {
        // Get or create registrar/cashier user for payment processing
        $registrarUser = User::role(['registrar', 'administrator', 'super_admin'])->first();

        if (! $registrarUser) {
            $registrarUser = User::firstOrCreate(
                ['email' => 'registrar@cbhlc.edu'],
                [
                    'name' => 'Test Registrar',
                    'email_verified_at' => now(),
                    'password' => bcrypt('password'),
                ]
            );
            $registrarUser->assignRole('registrar');
        }

        // Get enrollments with balance for payment creation
        $enrollmentsWithBalance = Enrollment::where('balance_cents', '>', 0)->get();

        // Issue #319: Create fully paid enrollments (2-3 enrollments)
        $enrollmentsForFullPayment = $enrollmentsWithBalance->take(2);

        foreach ($enrollmentsForFullPayment as $enrollment) {
            $totalAmountPeso = $enrollment->net_amount_cents / 100;

            // Create invoice for this enrollment
            $invoice = \App\Models\Invoice::firstOrCreate(
                ['enrollment_id' => $enrollment->id],
                [
                    'invoice_number' => 'INV-'.str_pad((string) $enrollment->id, 6, '0', STR_PAD_LEFT),
                    'invoice_date' => now()->subDays(rand(30, 60)),
                    'due_date' => now()->addDays(30),
                    'total_amount' => $totalAmountPeso,
                    'paid_amount' => $totalAmountPeso,
                    'status' => \App\Enums\InvoiceStatus::PAID,
                    'paid_at' => now()->subDays(rand(5, 30)),
                ]
            );

            // Create full payment
            \App\Models\Payment::firstOrCreate(
                [
                    'invoice_id' => $invoice->id,
                    'payment_method' => \App\Enums\PaymentMethod::CASH,
                ],
                [
                    'amount' => $totalAmountPeso,
                    'payment_date' => now()->subDays(rand(5, 30)),
                    'reference_number' => 'PAY-'.strtoupper(uniqid()),
                    'notes' => 'Full payment for tuition and miscellaneous fees',
                    'processed_by' => $registrarUser->id,
                ]
            );

            // Update enrollment to reflect full payment
            $enrollment->update([
                'amount_paid_cents' => $enrollment->net_amount_cents,
                'balance_cents' => 0,
                'payment_status' => PaymentStatus::PAID,
            ]);
        }

        // Issue #320: Create partial payments (2-3 enrollments)
        $enrollmentsForPartialPayment = $enrollmentsWithBalance->skip(2)->take(3);

        foreach ($enrollmentsForPartialPayment as $enrollment) {
            $totalAmountPeso = $enrollment->net_amount_cents / 100;

            // Calculate partial payment (50-70% of total)
            $partialPercentage = rand(50, 70) / 100;
            $partialAmountPeso = round($totalAmountPeso * $partialPercentage, 2);
            $partialAmountCents = (int) ($partialAmountPeso * 100);

            // Create invoice for this enrollment
            $invoice = \App\Models\Invoice::firstOrCreate(
                ['enrollment_id' => $enrollment->id],
                [
                    'invoice_number' => 'INV-'.str_pad((string) $enrollment->id, 6, '0', STR_PAD_LEFT),
                    'invoice_date' => now()->subDays(rand(30, 60)),
                    'due_date' => now()->addDays(30),
                    'total_amount' => $totalAmountPeso,
                    'paid_amount' => $partialAmountPeso,
                    'status' => \App\Enums\InvoiceStatus::PARTIALLY_PAID,
                ]
            );

            // Create partial payment
            \App\Models\Payment::firstOrCreate(
                [
                    'invoice_id' => $invoice->id,
                    'payment_method' => \App\Enums\PaymentMethod::CASH,
                ],
                [
                    'amount' => $partialAmountPeso,
                    'payment_date' => now()->subDays(rand(10, 45)),
                    'reference_number' => 'PAY-'.strtoupper(uniqid()),
                    'notes' => 'Partial payment - '.($partialPercentage * 100).'% of total',
                    'processed_by' => $registrarUser->id,
                ]
            );

            // Update enrollment to reflect partial payment
            $enrollment->update([
                'amount_paid_cents' => $partialAmountCents,
                'balance_cents' => $enrollment->net_amount_cents - $partialAmountCents,
                'payment_status' => PaymentStatus::PARTIAL,
            ]);
        }

        // Issue #318: Active enrollments already exist (ENROLLED status)
        // The seeder already creates enrollments with EnrollmentStatus::ENROLLED
        // at lines 487 and 667, so this requirement is already met
    }
}

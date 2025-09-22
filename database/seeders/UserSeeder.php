<?php

namespace Database\Seeders;

use App\Enums\GradeLevel;
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
                'age' => 12,
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
                'age' => 9,
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
    }
}

<?php

namespace Database\Seeders;

use App\Enums\GradeLevel;
use App\Models\ParentStudent;
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
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super.admin@cbhlc.edu',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);
        $superAdmin->assignRole('super_admin');

        // Administrator
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@cbhlc.edu',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('administrator');

        // Registrar
        $registrar = User::create([
            'name' => 'Registrar User',
            'email' => 'registrar@cbhlc.edu',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);
        $registrar->assignRole('registrar');

        // Parent with children
        $parent = User::create([
            'name' => 'Maria Santos',
            'email' => 'maria.santos@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);
        $parent->assignRole('parent');

        // Create students linked to the parent
        $this->createStudentsForParent($parent);
    }

    /**
     * Create student records for a parent
     */
    private function createStudentsForParent(User $parent): void
    {
        // Create first child with login account
        $studentUser1 = User::create([
            'name' => 'Juan Santos',
            'email' => 'juan.santos@student.cbhlc.edu',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);
        $studentUser1->assignRole('student');

        $student1 = Student::create([
            'student_id' => $this->generateStudentId(),
            'first_name' => 'Juan',
            'middle_name' => 'Garcia',
            'last_name' => 'Santos',
            'birthdate' => '2012-03-15',
            'gender' => 'male',
            'grade_level' => GradeLevel::GRADE_6,
            'address' => '123 Rizal Street, Pasig City',
            'phone' => '+63912345678',
            'user_id' => $studentUser1->id,
        ]);

        // Link student to parent
        ParentStudent::create([
            'parent_id' => $parent->id,
            'student_id' => $student1->id,
            'relationship_type' => 'mother',
            'is_primary_contact' => true,
        ]);

        // Create second child without login account
        $student2 = Student::create([
            'student_id' => $this->generateStudentId(),
            'first_name' => 'Ana',
            'middle_name' => 'Garcia',
            'last_name' => 'Santos',
            'birthdate' => '2015-08-22',
            'gender' => 'female',
            'grade_level' => GradeLevel::GRADE_3,
            'address' => '123 Rizal Street, Pasig City',
            'phone' => '+63912345678',
            'user_id' => null,
        ]);

        // Link second student to parent
        ParentStudent::create([
            'parent_id' => $parent->id,
            'student_id' => $student2->id,
            'relationship_type' => 'mother',
            'is_primary_contact' => false,
        ]);
    }

    /**
     * Generate a unique student ID
     */
    private function generateStudentId(): string
    {
        do {
            $year = date('Y');
            $number = str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $studentId = $year . '-' . $number;
        } while (Student::where('student_id', $studentId)->exists());

        return $studentId;
    }
}

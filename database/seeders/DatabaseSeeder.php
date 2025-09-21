<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call(RolesAndPermissionsSeeder::class);

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

        // Parent
        $parent = User::create([
            'name' => 'Parent User',
            'email' => 'parent@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);
        $parent->assignRole('parent');

        // Student
        $student = User::create([
            'name' => 'Student User',
            'email' => 'student@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);
        $student->assignRole('student');
    }
}

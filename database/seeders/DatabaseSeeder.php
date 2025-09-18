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

        // Create test users for each role

        // Super Admin
        User::factory()->superAdmin()->create([
            'name' => 'Super Admin',
            'email' => 'super.admin@cbhlc.edu',
        ]);

        // Administrator
        User::factory()->administrator()->create([
            'name' => 'Administrator',
            'email' => 'admin@cbhlc.edu',
        ]);

        // Registrar
        User::factory()->registrar()->create([
            'name' => 'Registrar User',
            'email' => 'registrar@cbhlc.edu',
        ]);

        // Parent
        User::factory()->parent()->create([
            'name' => 'Parent User',
            'email' => 'parent@example.com',
        ]);

        // Student
        User::factory()->student()->create([
            'name' => 'Student User',
            'email' => 'student@example.com',
        ]);

        // Additional test parent user
        User::factory()->parent()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}

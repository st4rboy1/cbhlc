<?php

use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => RolesAndPermissionsSeeder::class]);
});

describe('View Enrollments Functionality', function () {

    test('super admin can view student enrollments without 404 error', function () {
        // Create super admin user
        $admin = User::factory()->superAdmin()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        // Create a student
        $student = Student::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Enrollee',
        ]);

        // Login as super admin
        visit('/login')
            ->type('email', $admin->email)
            ->type('password', 'password')
            ->press('Log in')
            ->waitForText('Dashboard');

        // Navigate to students list
        visit('/super-admin/students')
            ->waitForText('Students Index')
            ->assertSee('John Enrollee');

        // Test backend - visit enrollments page
        $this->actingAs($admin);
        $response = $this->get("/super-admin/students/{$student->id}/enrollments");

        // Should not get 404
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('super-admin/students/enrollments')
            ->has('student')
            ->where('student.id', $student->id)
            ->has('enrollments')
        );

        // Visit the page via browser
        visit("/super-admin/students/{$student->id}/enrollments")
            ->waitForText('Enrollments for')
            ->assertSee('John Enrollee')
            ->assertSee('No enrollments found for this student')
            ->assertDontSee('404')
            ->assertDontSee('NOT FOUND');

    })->group('view-enrollments', 'critical');

    test('super admin can view student with enrollments', function () {
        $admin = User::factory()->superAdmin()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        // Create a student with enrollments
        $student = Student::factory()
            ->hasEnrollments(2, [
                'type' => 'new',
                'payment_plan' => 'annual',
            ])
            ->create([
                'first_name' => 'Jane',
                'last_name' => 'HasEnrollments',
            ]);

        $this->actingAs($admin);

        visit("/super-admin/students/{$student->id}/enrollments")
            ->waitForText('Enrollments for')
            ->assertSee('Jane HasEnrollments')
            ->assertSee('Enrollment History')
            // Should see the enrollment IDs in the table
            ->assertSee('ENR-');

    })->group('view-enrollments', 'critical');
});

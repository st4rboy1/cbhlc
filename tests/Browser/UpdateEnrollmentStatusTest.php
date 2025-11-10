<?php

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

describe('Update Enrollment Status Functionality', function () {

    test('super admin can update enrollment status to enrolled', function () {
        // Create super admin user
        $admin = User::factory()->superAdmin()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        // Create a student with a pending enrollment
        $student = Student::factory()->create([
            'first_name' => 'John',
            'last_name' => 'StatusTest',
        ]);

        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'status' => EnrollmentStatus::PENDING,
        ]);

        // Verify initial status
        expect($enrollment->status)->toBe(EnrollmentStatus::PENDING);

        // Login and navigate to edit enrollment page
        visit('/login')
            ->type('email', $admin->email)
            ->type('password', 'password')
            ->press('Log in')
            ->waitForText('Dashboard');

        // Navigate to enrollments list
        visit('/super-admin/enrollments')
            ->waitForText('Enrollments')
            ->assertSee($student->first_name);

        // Test backend directly - update enrollment status to enrolled
        $this->actingAs($admin);

        // Get fresh enrollment data
        $enrollment->refresh();

        $response = $this->put("/super-admin/enrollments/{$enrollment->id}", [
            'student_id' => $enrollment->student_id,
            'guardian_id' => $enrollment->guardian_id,
            'grade_level' => $enrollment->grade_level->value,
            'quarter' => $enrollment->quarter->value,
            'school_year_id' => $enrollment->school_year_id,
            'status' => EnrollmentStatus::ENROLLED->value,
            'type' => $enrollment->type,
            'payment_plan' => $enrollment->payment_plan->value,
        ]);

        $response->assertRedirect('/super-admin/enrollments');
        $response->assertSessionHas('success', 'Enrollment updated successfully.');

        // Verify status was actually updated in database
        $enrollment->refresh();
        expect($enrollment->status)->toBe(EnrollmentStatus::ENROLLED);

        // Visit enrollments page and verify status is displayed as enrolled
        visit('/super-admin/enrollments')
            ->waitForText('Enrollments')
            ->assertSee('enrolled'); // Status badge should show 'enrolled'

    })->group('update-enrollment', 'critical');

    test('super admin can update enrollment status to ready for payment', function () {
        $admin = User::factory()->superAdmin()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $student = Student::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'status' => EnrollmentStatus::PENDING,
            'type' => 'new',
            'payment_plan' => 'annual',
        ]);

        $this->actingAs($admin);

        $response = $this->put("/super-admin/enrollments/{$enrollment->id}", [
            'student_id' => $enrollment->student_id,
            'guardian_id' => $enrollment->guardian_id,
            'grade_level' => $enrollment->grade_level->value,
            'quarter' => $enrollment->quarter->value,
            'school_year_id' => $enrollment->school_year_id,
            'status' => EnrollmentStatus::READY_FOR_PAYMENT->value,
            'type' => $enrollment->type,
            'payment_plan' => $enrollment->payment_plan->value,
        ]);

        $response->assertRedirect('/super-admin/enrollments');

        $enrollment->refresh();
        expect($enrollment->status)->toBe(EnrollmentStatus::READY_FOR_PAYMENT);

    })->group('update-enrollment', 'critical');

    test('super admin can update enrollment status to paid', function () {
        $admin = User::factory()->superAdmin()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $student = Student::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'status' => EnrollmentStatus::READY_FOR_PAYMENT,
            'type' => 'new',
            'payment_plan' => 'annual',
        ]);

        $this->actingAs($admin);

        $response = $this->put("/super-admin/enrollments/{$enrollment->id}", [
            'student_id' => $enrollment->student_id,
            'guardian_id' => $enrollment->guardian_id,
            'grade_level' => $enrollment->grade_level->value,
            'quarter' => $enrollment->quarter->value,
            'school_year_id' => $enrollment->school_year_id,
            'status' => EnrollmentStatus::PAID->value,
            'type' => $enrollment->type,
            'payment_plan' => $enrollment->payment_plan->value,
        ]);

        $response->assertRedirect('/super-admin/enrollments');

        $enrollment->refresh();
        expect($enrollment->status)->toBe(EnrollmentStatus::PAID);

    })->group('update-enrollment', 'critical');

    test('super admin can update enrollment status to completed', function () {
        $admin = User::factory()->superAdmin()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $student = Student::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'status' => EnrollmentStatus::ENROLLED,
            'type' => 'new',
            'payment_plan' => 'annual',
        ]);

        $this->actingAs($admin);

        $response = $this->put("/super-admin/enrollments/{$enrollment->id}", [
            'student_id' => $enrollment->student_id,
            'guardian_id' => $enrollment->guardian_id,
            'grade_level' => $enrollment->grade_level->value,
            'quarter' => $enrollment->quarter->value,
            'school_year_id' => $enrollment->school_year_id,
            'status' => EnrollmentStatus::COMPLETED->value,
            'type' => $enrollment->type,
            'payment_plan' => $enrollment->payment_plan->value,
        ]);

        $response->assertRedirect('/super-admin/enrollments');

        $enrollment->refresh();
        expect($enrollment->status)->toBe(EnrollmentStatus::COMPLETED);

    })->group('update-enrollment', 'critical');
});

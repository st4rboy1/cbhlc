<?php

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

describe('Super Admin Enrollment Update', function () {

    test('super admin can successfully update enrollment status', function () {
        // Create super admin
        $admin = User::factory()->superAdmin()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        // Create student and guardian
        $student = Student::factory()->create();
        $guardian = Guardian::factory()->create();

        // Create school year
        $schoolYear = SchoolYear::factory()->create([
            'start_year' => 2025,
            'end_year' => 2026,
            'status' => 'active',
        ]);

        // Create enrollment with pending status
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'school_year_id' => $schoolYear->id,
            'status' => EnrollmentStatus::PENDING,
            'grade_level' => 'Grade 1',
            'quarter' => 'First',
            'type' => 'new',
            'payment_plan' => 'monthly',
        ]);

        $this->actingAs($admin);

        // Update enrollment status to completed
        $response = $this->put(route('super-admin.enrollments.update', $enrollment), [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'grade_level' => 'Grade 2',  // Change grade level
            'school_year_id' => $schoolYear->id,
            'quarter' => 'Second',  // Change quarter
            'type' => 'continuing',  // Change type
            'payment_plan' => 'annual',  // Change payment plan
            'status' => EnrollmentStatus::COMPLETED->value,  // Change status
        ]);

        // Should redirect successfully
        $response->assertStatus(302);
        $response->assertRedirect(route('super-admin.enrollments.index'));
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success', 'Enrollment updated successfully.');

        // Verify enrollment was updated
        $enrollment->refresh();
        expect($enrollment->status->value)->toBe(EnrollmentStatus::COMPLETED->value);
        expect($enrollment->grade_level->value)->toBe('Grade 2');
        expect($enrollment->quarter->value)->toBe('Second');
        expect($enrollment->type)->toBe('continuing');
        expect($enrollment->payment_plan)->toBe('annual');
    })->group('super-admin', 'enrollment', 'critical');

    test('super admin can update enrollment without changing status', function () {
        $admin = User::factory()->superAdmin()->create();
        $student = Student::factory()->create();
        $guardian = Guardian::factory()->create();
        $schoolYear = SchoolYear::factory()->create(['status' => 'active']);

        // Create enrollment with approved status
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'school_year_id' => $schoolYear->id,
            'status' => EnrollmentStatus::APPROVED,
            'grade_level' => 'Grade 1',
            'quarter' => 'First',
            'type' => 'new',
            'payment_plan' => 'monthly',
        ]);

        $this->actingAs($admin);

        // Update enrollment but keep same status
        $response = $this->put(route('super-admin.enrollments.update', $enrollment), [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'grade_level' => 'Grade 2',  // Only change grade
            'school_year_id' => $schoolYear->id,
            'quarter' => 'First',
            'type' => 'new',
            'payment_plan' => 'monthly',
            'status' => EnrollmentStatus::APPROVED->value,  // Same status
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('super-admin.enrollments.index'));
        $response->assertSessionHasNoErrors();

        // Verify enrollment was updated
        $enrollment->refresh();
        expect($enrollment->status->value)->toBe(EnrollmentStatus::APPROVED->value);  // Status unchanged
        expect($enrollment->grade_level->value)->toBe('Grade 2');  // Grade changed
    })->group('super-admin', 'enrollment', 'critical');

    test('validation fails when updating enrollment with invalid data', function () {
        $admin = User::factory()->superAdmin()->create();
        $student = Student::factory()->create();
        $guardian = Guardian::factory()->create();
        $schoolYear = SchoolYear::factory()->create(['status' => 'active']);

        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'school_year_id' => $schoolYear->id,
        ]);

        $this->actingAs($admin);

        // Try to update with invalid data
        $response = $this->put(route('super-admin.enrollments.update', $enrollment), [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'grade_level' => 'Invalid Grade',  // Invalid grade
            'school_year_id' => 99999,  // Non-existent school year
            'quarter' => 'Invalid',  // Invalid quarter
            'type' => 'invalid_type',  // Invalid type
            'payment_plan' => 'invalid_plan',  // Invalid payment plan
            'status' => 'invalid_status',  // Invalid status
        ]);

        // Should have validation errors
        $response->assertSessionHasErrors([
            'grade_level',
            'school_year_id',
            'quarter',
            'type',
            'payment_plan',
            'status',
        ]);
    })->group('super-admin', 'enrollment', 'validation');
});

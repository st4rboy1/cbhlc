<?php

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->admin = User::factory()->superAdmin()->create();
});

describe('Student Enrollments Feature', function () {

    test('super admin can view student enrollments', function () {
        $student = Student::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get("/super-admin/students/{$student->id}/enrollments");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('super-admin/students/enrollments')
            ->has('student')
            ->has('enrollments')
        );
    });

    test('enrollments are properly formatted with all required fields', function () {
        $student = Student::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'type' => 'new',
            'payment_plan' => 'annual',
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/super-admin/students/{$student->id}/enrollments");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('super-admin/students/enrollments')
            ->where('student.id', $student->id)
            ->has('enrollments', 1)
            ->where('enrollments.0.id', $enrollment->id)
            ->where('enrollments.0.enrollment_id', $enrollment->enrollment_id)
            ->has('enrollments.0.status')
            ->has('enrollments.0.grade_level')
            ->has('enrollments.0.quarter')
            ->has('enrollments.0.school_year')
            ->has('enrollments.0.guardian')
            ->has('enrollments.0.created_at')
        );
    });

    test('shows empty array when student has no enrollments', function () {
        $student = Student::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get("/super-admin/students/{$student->id}/enrollments");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('super-admin/students/enrollments')
            ->where('student.id', $student->id)
            ->has('enrollments', 0)
        );
    });

    test('unauthorized users cannot view student enrollments', function () {
        $student = Student::factory()->create();
        $user = User::factory()->create(); // Regular user without permissions

        $response = $this->actingAs($user)
            ->get("/super-admin/students/{$student->id}/enrollments");

        $response->assertForbidden();
    });
});

<?php

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\Quarter;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\GuardianStudent;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

describe('enrollment controller', function () {
    test('admin can view list of all enrollments at /enrollments', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Create some enrollments
        Student::factory()->count(3)->create()->each(function ($student) {
            Enrollment::factory()->create(['student_id' => $student->id]);
        });

        $response = $this->actingAs($admin)->get(route('enrollments.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('enrollments/index')
            ->has('enrollments.data', 3)
        );
    });

    test('guardian can only view their children enrollments at /enrollments', function () {
        $guardian = User::factory()->create();
        $guardian->assignRole('guardian');

        // Create guardian's children with enrollments
        $ownStudent = Student::factory()->create();
        \App\Models\GuardianStudent::create([
            'guardian_id' => $guardian->id,
            'student_id' => $ownStudent->id,
            'relationship_type' => 'father',
            'is_primary_contact' => true,
        ]);
        Enrollment::factory()->create([
            'student_id' => $ownStudent->id,
            'guardian_id' => $guardian->id,
        ]);

        // Create other student's enrollment
        $otherStudent = Student::factory()->create();
        Enrollment::factory()->create(['student_id' => $otherStudent->id]);

        $response = $this->actingAs($guardian)->get(route('enrollments.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('enrollments/index')
            ->has('enrollments.data', 1) // Only guardian's child enrollment
        );
    });

    test('users can access enrollment creation form at /enrollments/create', function () {
        $user = User::factory()->create();
        $user->assignRole('guardian');

        $response = $this->actingAs($user)->get(route('enrollments.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('enrollments/create')
        );
    });

    test('can store new enrollment', function () {
        $guardian = User::factory()->create();
        $guardian->assignRole('guardian');

        $student = Student::factory()->create();

        $enrollmentData = [
            'student_id' => $student->id,
            'school_year' => '2024-2025',
            'quarter' => 'First',
        ];

        $response = $this->actingAs($guardian)->post(route('enrollments.store'), $enrollmentData);

        $response->assertRedirect(route('enrollments.index'));
        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'school_year' => '2024-2025',
        ]);
    });

    test('can view single enrollment details at /enrollments/{enrollment}', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $enrollment = Enrollment::factory()->create();

        $response = $this->actingAs($admin)->get(route('enrollments.show', $enrollment));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('enrollments/show')
            ->has('enrollment')
        );
    });

    test('old /enrollment route no longer exists', function () {
        $user = User::factory()->create();
        $user->assignRole('guardian');

        $response = $this->actingAs($user)->get('/enrollment');

        $response->assertStatus(404);
    });

    test('prevents duplicate enrollment for same student and school year', function () {
        $guardian = User::factory()->create();
        $guardian->assignRole('guardian');

        $guardianModel = Guardian::create([
            'user_id' => $guardian->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'contact_number' => '09123456789',
            'address' => '456 Test Ave',
        ]);

        $student = Student::factory()->create();

        GuardianStudent::create([
            'guardian_id' => $guardian->id,
            'student_id' => $student->id,
            'relationship_type' => 'mother',
            'is_primary_contact' => true,
        ]);

        // Create first enrollment
        $firstEnrollment = Enrollment::create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'school_year' => '2024-2025',
            'quarter' => Quarter::FIRST,
            'status' => EnrollmentStatus::PENDING,
            'tuition_fee_cents' => 0,
            'miscellaneous_fee_cents' => 0,
            'laboratory_fee_cents' => 0,
            'total_amount_cents' => 0,
            'net_amount_cents' => 0,
            'amount_paid_cents' => 0,
            'balance_cents' => 0,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        // Attempt to create duplicate enrollment for same student and school year
        $response = $this->actingAs($guardian)->post(route('enrollments.store'), [
            'student_id' => $student->id,
            'school_year' => '2024-2025',
            'quarter' => Quarter::SECOND->value,
        ]);

        $response->assertSessionHasErrors(['student_id']);
        $response->assertRedirect();

        // Verify only one enrollment exists for this student and school year
        $enrollmentCount = Enrollment::where('student_id', $student->id)
            ->where('school_year', '2024-2025')
            ->count();

        expect($enrollmentCount)->toBe(1);
    });

    test('allows enrollment for same student in different school year', function () {
        $guardian = User::factory()->create();
        $guardian->assignRole('guardian');

        $guardianModel = Guardian::create([
            'user_id' => $guardian->id,
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'contact_number' => '09123456789',
            'address' => '789 Test Blvd',
        ]);

        $student = Student::factory()->create();

        GuardianStudent::create([
            'guardian_id' => $guardian->id,
            'student_id' => $student->id,
            'relationship_type' => 'father',
            'is_primary_contact' => true,
        ]);

        // Create first enrollment for 2024-2025
        Enrollment::create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'school_year' => '2024-2025',
            'quarter' => Quarter::FIRST,
            'status' => EnrollmentStatus::PENDING,
            'tuition_fee_cents' => 0,
            'miscellaneous_fee_cents' => 0,
            'laboratory_fee_cents' => 0,
            'total_amount_cents' => 0,
            'net_amount_cents' => 0,
            'amount_paid_cents' => 0,
            'balance_cents' => 0,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        // Create enrollment for different school year - should succeed
        $response = $this->actingAs($guardian)->post(route('enrollments.store'), [
            'student_id' => $student->id,
            'school_year' => '2025-2026',
            'quarter' => Quarter::FIRST->value,
        ]);

        $response->assertRedirect(route('enrollments.index'));
        $response->assertSessionHas('success');

        // Verify both enrollments exist
        $enrollmentCount = Enrollment::where('student_id', $student->id)->count();
        expect($enrollmentCount)->toBe(2);
    });
});

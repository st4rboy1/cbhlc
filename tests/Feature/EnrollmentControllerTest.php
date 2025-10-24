<?php

use App\Enums\EnrollmentPeriodStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\Quarter;
use App\Models\Enrollment;
use App\Models\EnrollmentPeriod;
use App\Models\Guardian;
use App\Models\GuardianStudent;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    // Create school years using factory for parallel test support
    $this->sy2024 = \App\Models\SchoolYear::factory()->create([
        'start_year' => 2024,
        'end_year' => 2025,
        'start_date' => '2024-06-01',
        'end_date' => '2025-05-31',
        'status' => 'active',
    ]);

    $this->sy2023 = \App\Models\SchoolYear::factory()->create([
        'start_year' => 2023,
        'end_year' => 2024,
        'start_date' => '2023-06-01',
        'end_date' => '2024-05-31',
        'status' => 'completed',
    ]);

    $this->sy2025 = \App\Models\SchoolYear::factory()->create([
        'start_year' => 2025,
        'end_year' => 2026,
        'start_date' => '2025-06-01',
        'end_date' => '2026-05-31',
        'status' => 'upcoming',
    ]);

    // Create an active enrollment period for all tests
    EnrollmentPeriod::create([
        'school_year_id' => $this->sy2024->id,
        'start_date' => now()->subDays(5),
        'end_date' => now()->addMonths(3),
        'early_registration_deadline' => now()->addDays(10),
        'regular_registration_deadline' => now()->addMonth(),
        'late_registration_deadline' => now()->addMonths(2),
        'status' => EnrollmentPeriodStatus::ACTIVE->value,
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    // Create enrollment period for 2025-2026 for tests that need it
    EnrollmentPeriod::create([
        'school_year_id' => $this->sy2025->id,
        'start_date' => now()->addMonths(6),
        'end_date' => now()->addMonths(9),
        'early_registration_deadline' => now()->addMonths(6)->addDays(10),
        'regular_registration_deadline' => now()->addMonths(7),
        'late_registration_deadline' => now()->addMonths(8),
        'status' => EnrollmentPeriodStatus::UPCOMING->value,
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);
});

describe('enrollment controller', function () {
    test('admin can view list of all enrollments at registrar/enrollments', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Create some enrollments
        Student::factory()->count(3)->create()->each(function ($student) {
            Enrollment::factory()->create(['student_id' => $student->id]);
        });

        $response = $this->actingAs($admin)->get(route('registrar.enrollments.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('registrar/enrollments/index')
            ->has('enrollments.data', 3)
        );
    });

    test('guardian can only view their children enrollments at /enrollments', function () {
        $guardianUser = User::factory()->create();
        $guardianUser->assignRole('guardian');

        $guardian = Guardian::create([
            'user_id' => $guardianUser->id,
            'first_name' => 'Test',
            'last_name' => 'Guardian',
            'contact_number' => '09123456789',
            'address' => '123 Test St',
        ]);

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

        $response = $this->actingAs($guardianUser)->get(route('enrollments.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/enrollments/index')
            ->has('enrollments.data', 1) // Only guardian's child enrollment
        );
    });

    test('users can access enrollment creation form at /enrollments/create', function () {
        $user = User::factory()->create();
        $user->assignRole('guardian');

        // Create Guardian model for the user
        Guardian::create([
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'Guardian',
            'contact_number' => '09123456789',
            'address' => '123 Test St',
        ]);

        $response = $this->actingAs($user)->get(route('enrollments.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/enrollments/create')
        );
    });

    test('can store new enrollment', function () {
        $guardianUser = User::factory()->create();
        $guardianUser->assignRole('guardian');

        // Create Guardian model
        $guardianModel = Guardian::create([
            'user_id' => $guardianUser->id,
            'first_name' => 'Test',
            'last_name' => 'Guardian',
            'contact_number' => '09123456789',
            'address' => '123 Test St',
        ]);

        $student = Student::factory()->create();

        $enrollmentData = [
            'student_id' => $student->id,
            'school_year_id' => $this->sy2024->id,
            'quarter' => 'First',
            'grade_level' => 'Grade 1',
        ];

        // Need to create guardian-student relationship first
        GuardianStudent::create([
            'guardian_id' => $guardianModel->id,
            'student_id' => $student->id,
            'relationship_type' => 'father',
            'is_primary_contact' => true,
        ]);

        $response = $this->actingAs($guardianUser)->post(route('enrollments.store'), $enrollmentData);

        $response->assertRedirect(route('guardian.enrollments.index'));
        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'school_year_id' => $this->sy2024->id,
            'grade_level' => 'Grade 1',
        ]);
    });

    test('can view single enrollment details at registrar/enrollments/{enrollment}', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $enrollment = Enrollment::factory()->create();

        $response = $this->actingAs($admin)->get(route('registrar.enrollments.show', $enrollment));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('registrar/enrollments/show')
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
        $guardianUser = User::factory()->create();
        $guardianUser->assignRole('guardian');

        $guardianModel = Guardian::create([
            'user_id' => $guardianUser->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'contact_number' => '09123456789',
            'address' => '456 Test Ave',
        ]);

        $student = Student::factory()->create();

        GuardianStudent::create([
            'guardian_id' => $guardianModel->id,
            'student_id' => $student->id,
            'relationship_type' => 'mother',
            'is_primary_contact' => true,
        ]);

        // Create first enrollment
        $firstEnrollment = Enrollment::create([
            'student_id' => $student->id,
            'guardian_id' => $guardianModel->id,
            'school_year_id' => $this->sy2024->id,
            'quarter' => Quarter::FIRST,
            'grade_level' => 'Grade 1',
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
        $response = $this->actingAs($guardianUser)->post(route('enrollments.store'), [
            'student_id' => $student->id,
            'school_year_id' => $this->sy2024->id,
            'quarter' => Quarter::SECOND->value,
            'grade_level' => 'Grade 2',
        ]);

        $response->assertSessionHasErrors(['student_id']);
        $response->assertRedirect();

        // Verify only one enrollment exists for this student and school year
        $enrollmentCount = Enrollment::where('student_id', $student->id)
            ->where('school_year_id', $this->sy2024->id)
            ->count();

        expect($enrollmentCount)->toBe(1);
    });

    test('allows enrollment for same student in different school year', function () {
        $guardianUser = User::factory()->create();
        $guardianUser->assignRole('guardian');

        $guardianModel = Guardian::create([
            'user_id' => $guardianUser->id,
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'contact_number' => '09123456789',
            'address' => '789 Test Blvd',
        ]);

        $student = Student::factory()->create();

        GuardianStudent::create([
            'guardian_id' => $guardianModel->id,
            'student_id' => $student->id,
            'relationship_type' => 'father',
            'is_primary_contact' => true,
        ]);

        // Create first enrollment for 2024-2025
        Enrollment::create([
            'student_id' => $student->id,
            'guardian_id' => $guardianModel->id,
            'school_year_id' => $this->sy2024->id,
            'quarter' => Quarter::FIRST,
            'grade_level' => 'Kinder',
            'status' => EnrollmentStatus::APPROVED, // Make sure they passed
            'tuition_fee_cents' => 0,
            'miscellaneous_fee_cents' => 0,
            'laboratory_fee_cents' => 0,
            'total_amount_cents' => 0,
            'net_amount_cents' => 0,
            'amount_paid_cents' => 0,
            'balance_cents' => 0,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        // Close the 2024-2025 period and activate the 2025-2026 enrollment period
        EnrollmentPeriod::where('school_year_id', $this->sy2024->id)->update([
            'status' => EnrollmentPeriodStatus::CLOSED->value,
        ]);

        EnrollmentPeriod::where('school_year_id', $this->sy2025->id)->update([
            'status' => EnrollmentPeriodStatus::ACTIVE->value,
            'start_date' => now()->subDays(1),
            'late_registration_deadline' => now()->addMonths(2),
        ]);

        // Create enrollment for different school year - should succeed (progression from Kinder to Grade 1)
        $response = $this->actingAs($guardianUser)->post(route('enrollments.store'), [
            'student_id' => $student->id,
            'school_year_id' => $this->sy2025->id,
            'quarter' => Quarter::FIRST->value,
            'grade_level' => 'Grade 1',
        ]);

        $response->assertRedirect(route('guardian.enrollments.index'));
        $response->assertSessionHas('success');

        // Verify both enrollments exist
        $enrollmentCount = Enrollment::where('student_id', $student->id)->count();
        expect($enrollmentCount)->toBe(2);
    });

    describe('quarter selection business rules', function () {
        test('new students can select any quarter', function () {
            $guardianUser = User::factory()->create();
            $guardianUser->assignRole('guardian');

            // Create Guardian model
            $guardianModel = Guardian::create([
                'user_id' => $guardianUser->id,
                'first_name' => 'Test',
                'last_name' => 'Guardian',
                'contact_number' => '09123456789',
                'address' => '123 Test St',
            ]);

            $student = Student::factory()->create();

            GuardianStudent::create([
                'guardian_id' => $guardianModel->id,
                'student_id' => $student->id,
                'relationship_type' => 'father',
                'is_primary_contact' => true,
            ]);

            // New student should be able to select any quarter
            $response = $this->actingAs($guardianUser)->post(route('enrollments.store'), [
                'student_id' => $student->id,
                'school_year_id' => $this->sy2024->id,
                'quarter' => Quarter::SECOND->value,
                'grade_level' => 'Kinder',
            ]);

            $response->assertRedirect(route('guardian.enrollments.index'));
            $this->assertDatabaseHas('enrollments', [
                'student_id' => $student->id,
                'quarter' => Quarter::SECOND,
            ]);
        });

        test('existing students are automatically enrolled in first quarter', function () {
            $guardianUser = User::factory()->create();
            $guardianUser->assignRole('guardian');

            // Create Guardian model
            $guardianModel = Guardian::create([
                'user_id' => $guardianUser->id,
                'first_name' => 'Test',
                'last_name' => 'Guardian',
                'contact_number' => '09123456789',
                'address' => '123 Test St',
            ]);

            $student = Student::factory()->create();

            GuardianStudent::create([
                'guardian_id' => $guardianModel->id,
                'student_id' => $student->id,
                'relationship_type' => 'mother',
                'is_primary_contact' => true,
            ]);

            // Create previous enrollment to make student "existing"
            Enrollment::create([
                'student_id' => $student->id,
                'guardian_id' => $guardianModel->id,
                'school_year_id' => $this->sy2023->id,
                'quarter' => Quarter::FIRST,
                'grade_level' => 'Kinder',
                'status' => EnrollmentStatus::APPROVED,
                'tuition_fee_cents' => 0,
                'miscellaneous_fee_cents' => 0,
                'laboratory_fee_cents' => 0,
                'total_amount_cents' => 0,
                'net_amount_cents' => 0,
                'amount_paid_cents' => 0,
                'balance_cents' => 0,
                'payment_status' => PaymentStatus::PENDING,
            ]);

            // Try to enroll in second quarter - should be overridden to first quarter
            $response = $this->actingAs($guardianUser)->post(route('enrollments.store'), [
                'student_id' => $student->id,
                'school_year_id' => $this->sy2024->id,
                'quarter' => Quarter::SECOND->value,
                'grade_level' => 'Grade 1',
            ]);

            $response->assertRedirect(route('guardian.enrollments.index'));
            $this->assertDatabaseHas('enrollments', [
                'student_id' => $student->id,
                'school_year_id' => $this->sy2024->id,
                'quarter' => Quarter::FIRST, // Should be overridden to First
            ]);
        });
    });

    describe('grade progression business rules', function () {
        test('new students can enroll in any grade level', function () {
            $guardianUser = User::factory()->create();
            $guardianUser->assignRole('guardian');

            // Create Guardian model
            $guardianModel = Guardian::create([
                'user_id' => $guardianUser->id,
                'first_name' => 'Test',
                'last_name' => 'Guardian',
                'contact_number' => '09123456789',
                'address' => '123 Test St',
            ]);

            $student = Student::factory()->create();

            GuardianStudent::create([
                'guardian_id' => $guardianModel->id,
                'student_id' => $student->id,
                'relationship_type' => 'father',
                'is_primary_contact' => true,
            ]);

            // New student should be able to enroll in any grade
            $response = $this->actingAs($guardianUser)->post(route('enrollments.store'), [
                'student_id' => $student->id,
                'school_year_id' => $this->sy2024->id,
                'quarter' => Quarter::FIRST->value,
                'grade_level' => 'Grade 3',
            ]);

            $response->assertRedirect(route('guardian.enrollments.index'));
            $this->assertDatabaseHas('enrollments', [
                'student_id' => $student->id,
                'grade_level' => 'Grade 3',
            ]);
        });

        test('existing students cannot apply to grades lower than current grade', function () {
            $guardianUser = User::factory()->create();
            $guardianUser->assignRole('guardian');

            // Create Guardian model
            $guardianModel = Guardian::create([
                'user_id' => $guardianUser->id,
                'first_name' => 'Test',
                'last_name' => 'Guardian',
                'contact_number' => '09123456789',
                'address' => '123 Test St',
            ]);

            $student = Student::factory()->create([
                'grade_level' => 'Grade 3',
            ]);

            GuardianStudent::create([
                'guardian_id' => $guardianModel->id,
                'student_id' => $student->id,
                'relationship_type' => 'mother',
                'is_primary_contact' => true,
            ]);

            // Create previous enrollment to establish current grade
            Enrollment::create([
                'student_id' => $student->id,
                'guardian_id' => $guardianModel->id,
                'school_year_id' => $this->sy2023->id,
                'quarter' => Quarter::FIRST,
                'grade_level' => 'Grade 3',
                'status' => EnrollmentStatus::APPROVED,
                'tuition_fee_cents' => 0,
                'miscellaneous_fee_cents' => 0,
                'laboratory_fee_cents' => 0,
                'total_amount_cents' => 0,
                'net_amount_cents' => 0,
                'amount_paid_cents' => 0,
                'balance_cents' => 0,
                'payment_status' => PaymentStatus::PENDING,
            ]);

            // Try to enroll in lower grade - should fail
            $response = $this->actingAs($guardianUser)->post(route('enrollments.store'), [
                'student_id' => $student->id,
                'school_year_id' => $this->sy2024->id,
                'quarter' => Quarter::FIRST->value,
                'grade_level' => 'Grade 2', // Lower than current Grade 3
            ]);

            $response->assertSessionHasErrors(['grade_level']);
            $response->assertRedirect();
        });

        test('students can progress to same or higher grade', function () {
            $guardianUser = User::factory()->create();
            $guardianUser->assignRole('guardian');

            // Create Guardian model
            $guardianModel = Guardian::create([
                'user_id' => $guardianUser->id,
                'first_name' => 'Test',
                'last_name' => 'Guardian',
                'contact_number' => '09123456789',
                'address' => '123 Test St',
            ]);

            $student = Student::factory()->create([
                'grade_level' => 'Grade 2',
            ]);

            GuardianStudent::create([
                'guardian_id' => $guardianModel->id,
                'student_id' => $student->id,
                'relationship_type' => 'father',
                'is_primary_contact' => true,
            ]);

            // Create previous enrollment to establish current grade
            Enrollment::create([
                'student_id' => $student->id,
                'guardian_id' => $guardianModel->id,
                'school_year_id' => $this->sy2023->id,
                'quarter' => Quarter::FIRST,
                'grade_level' => 'Grade 2',
                'status' => EnrollmentStatus::APPROVED,
                'tuition_fee_cents' => 0,
                'miscellaneous_fee_cents' => 0,
                'laboratory_fee_cents' => 0,
                'total_amount_cents' => 0,
                'net_amount_cents' => 0,
                'amount_paid_cents' => 0,
                'balance_cents' => 0,
                'payment_status' => PaymentStatus::PENDING,
            ]);

            // Enroll in next grade level - should succeed
            $response = $this->actingAs($guardianUser)->post(route('enrollments.store'), [
                'student_id' => $student->id,
                'school_year_id' => $this->sy2024->id,
                'quarter' => Quarter::FIRST->value,
                'grade_level' => 'Grade 3',
            ]);

            $response->assertRedirect(route('guardian.enrollments.index'));
            $this->assertDatabaseHas('enrollments', [
                'student_id' => $student->id,
                'school_year_id' => $this->sy2024->id,
                'grade_level' => 'Grade 3',
            ]);
        });

        test('students can apply for accelerated progression beyond next grade', function () {
            $guardianUser = User::factory()->create();
            $guardianUser->assignRole('guardian');

            // Create Guardian model
            $guardianModel = Guardian::create([
                'user_id' => $guardianUser->id,
                'first_name' => 'Test',
                'last_name' => 'Guardian',
                'contact_number' => '09123456789',
                'address' => '123 Test St',
            ]);

            $student = Student::factory()->create([
                'grade_level' => 'Grade 1',
            ]);

            GuardianStudent::create([
                'guardian_id' => $guardianModel->id,
                'student_id' => $student->id,
                'relationship_type' => 'mother',
                'is_primary_contact' => true,
            ]);

            // Create previous enrollment to establish current grade
            Enrollment::create([
                'student_id' => $student->id,
                'guardian_id' => $guardianModel->id,
                'school_year_id' => $this->sy2023->id,
                'quarter' => Quarter::FIRST,
                'grade_level' => 'Grade 1',
                'status' => EnrollmentStatus::APPROVED,
                'tuition_fee_cents' => 0,
                'miscellaneous_fee_cents' => 0,
                'laboratory_fee_cents' => 0,
                'total_amount_cents' => 0,
                'net_amount_cents' => 0,
                'amount_paid_cents' => 0,
                'balance_cents' => 0,
                'payment_status' => PaymentStatus::PENDING,
            ]);

            // Enroll in grade level beyond next (accelerated) - should succeed
            $response = $this->actingAs($guardianUser)->post(route('enrollments.store'), [
                'student_id' => $student->id,
                'school_year_id' => $this->sy2024->id,
                'quarter' => Quarter::FIRST->value,
                'grade_level' => 'Grade 4', // Skipping Grade 2 and 3
            ]);

            $response->assertRedirect(route('guardian.enrollments.index'));
            $this->assertDatabaseHas('enrollments', [
                'student_id' => $student->id,
                'school_year_id' => $this->sy2024->id,
                'grade_level' => 'Grade 4',
            ]);
        });
    });
});

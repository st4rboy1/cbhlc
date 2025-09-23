<?php

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\Quarter;
use App\Models\Enrollment;
use App\Models\GuardianStudent;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

describe('enrollment pending constraint', function () {
    test('student can have only one pending enrollment', function () {
        $guardian = User::factory()->create();
        $guardian->assignRole('guardian');

        $student = Student::factory()->create();

        GuardianStudent::create([
            'guardian_id' => $guardian->id,
            'student_id' => $student->id,
            'relationship_type' => 'father',
            'is_primary_contact' => true,
        ]);

        // Create first pending enrollment
        Enrollment::create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'school_year' => '2024-2025',
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

        // Try to create second pending enrollment for different school year
        $response = $this->actingAs($guardian)->post(route('guardian.enrollments.store'), [
            'student_id' => $student->id,
            'school_year' => '2025-2026',
            'quarter' => Quarter::FIRST->value,
            'grade_level' => 'Grade 2',
        ]);

        $response->assertSessionHasErrors(['student_id']);

        // Verify only one pending enrollment exists
        $pendingCount = Enrollment::where('student_id', $student->id)
            ->where('status', EnrollmentStatus::PENDING)
            ->count();

        expect($pendingCount)->toBe(1);
    });

    test('student can have pending enrollment if previous ones are completed', function () {
        $guardian = User::factory()->create();
        $guardian->assignRole('guardian');

        // Create student with specific grade level
        $student = Student::factory()->create([
            'grade_level' => 'Kinder',
        ]);

        GuardianStudent::create([
            'guardian_id' => $guardian->id,
            'student_id' => $student->id,
            'relationship_type' => 'mother',
            'is_primary_contact' => true,
        ]);

        // Create completed enrollment for previous year
        Enrollment::create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'school_year' => '2023-2024',
            'quarter' => Quarter::FIRST,
            'grade_level' => 'Kinder',
            'status' => EnrollmentStatus::COMPLETED,
            'tuition_fee_cents' => 0,
            'miscellaneous_fee_cents' => 0,
            'laboratory_fee_cents' => 0,
            'total_amount_cents' => 0,
            'net_amount_cents' => 0,
            'amount_paid_cents' => 0,
            'balance_cents' => 0,
            'payment_status' => PaymentStatus::PAID,
        ]);

        // Create new pending enrollment - should succeed
        $response = $this->actingAs($guardian)->post(route('guardian.enrollments.store'), [
            'student_id' => $student->id,
            'school_year' => '2024-2025',
            'quarter' => Quarter::FIRST->value,
            'grade_level' => 'Grade 1',
        ]);

        $response->assertRedirect(route('guardian.enrollments.index'));
        $response->assertSessionHas('success');
    });

    test('student can have approved/enrolled status alongside pending for different years', function () {
        $guardian = User::factory()->create();
        $guardian->assignRole('guardian');

        $student = Student::factory()->create();

        GuardianStudent::create([
            'guardian_id' => $guardian->id,
            'student_id' => $student->id,
            'relationship_type' => 'father',
            'is_primary_contact' => true,
        ]);

        // Create enrolled enrollment for current year
        Enrollment::create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'school_year' => '2024-2025',
            'quarter' => Quarter::FIRST,
            'grade_level' => 'Grade 1',
            'status' => EnrollmentStatus::ENROLLED,
            'tuition_fee_cents' => 0,
            'miscellaneous_fee_cents' => 0,
            'laboratory_fee_cents' => 0,
            'total_amount_cents' => 0,
            'net_amount_cents' => 0,
            'amount_paid_cents' => 0,
            'balance_cents' => 0,
            'payment_status' => PaymentStatus::PARTIAL,
        ]);

        // Try to create pending enrollment for next year - should fail due to ongoing enrollment
        $response = $this->actingAs($guardian)->post(route('guardian.enrollments.store'), [
            'student_id' => $student->id,
            'school_year' => '2025-2026',
            'quarter' => Quarter::FIRST->value,
            'grade_level' => 'Grade 2',
        ]);

        $response->assertSessionHasErrors(['student_id']);
    });

    test('cannot create enrollment for same school year regardless of status', function () {
        $guardian = User::factory()->create();
        $guardian->assignRole('guardian');

        $student = Student::factory()->create();

        GuardianStudent::create([
            'guardian_id' => $guardian->id,
            'student_id' => $student->id,
            'relationship_type' => 'mother',
            'is_primary_contact' => true,
        ]);

        // Create rejected enrollment
        Enrollment::create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'school_year' => '2024-2025',
            'quarter' => Quarter::FIRST,
            'grade_level' => 'Grade 1',
            'status' => EnrollmentStatus::REJECTED,
            'tuition_fee_cents' => 0,
            'miscellaneous_fee_cents' => 0,
            'laboratory_fee_cents' => 0,
            'total_amount_cents' => 0,
            'net_amount_cents' => 0,
            'amount_paid_cents' => 0,
            'balance_cents' => 0,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        // Try to create another enrollment for same school year
        $response = $this->actingAs($guardian)->post(route('guardian.enrollments.store'), [
            'student_id' => $student->id,
            'school_year' => '2024-2025',
            'quarter' => Quarter::SECOND->value,
            'grade_level' => 'Grade 1',
        ]);

        $response->assertSessionHasErrors(['student_id']);
    });
});

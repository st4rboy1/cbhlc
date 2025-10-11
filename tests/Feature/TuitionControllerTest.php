<?php

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\PaymentStatus;
use App\Enums\Quarter;
use App\Models\Enrollment;
use App\Models\GradeLevelFee;
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

describe('tuition controller', function () {
    test('admin can view all enrollments with tuition fees', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $students = Student::factory()->count(3)->create();
        $enrollments = [];

        foreach ($students as $student) {
            $enrollments[] = Enrollment::create([
                'enrollment_id' => 'ENR-'.str_pad($student->id, 4, '0', STR_PAD_LEFT),
                'student_id' => $student->id,
                'guardian_id' => Guardian::factory()->create()->id,
                'school_year' => '2024-2025',
                'quarter' => Quarter::FIRST,
                'grade_level' => GradeLevel::GRADE_1,
                'status' => EnrollmentStatus::APPROVED,
                'tuition_fee_cents' => 2000000,
                'miscellaneous_fee_cents' => 500000,
                'laboratory_fee_cents' => 200000,
                'total_amount_cents' => 27000,
                'net_amount_cents' => 27000,
                'amount_paid_cents' => 0,
                'balance_cents' => 27000,
                'payment_status' => PaymentStatus::PENDING,
            ]);
        }

        // Create grade level fees for current school year
        $currentYear = date('Y');
        $nextYear = $currentYear + 1;
        $schoolYear = "{$currentYear}-{$nextYear}";

        GradeLevelFee::create([
            'grade_level' => GradeLevel::GRADE_1,
            'school_year' => $schoolYear,
            'tuition_fee_cents' => 2000000,  // 20000 * 100
            'miscellaneous_fee_cents' => 500000,  // 5000 * 100
            'laboratory_fee_cents' => 200000,  // 2000 * 100
            'library_fee_cents' => 100000,  // 1000 * 100
            'sports_fee_cents' => 50000,  // 500 * 100
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('tuition'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('shared/tuition')
            ->has('enrollments.data', 3)
            ->has('gradeLevelFees')
            ->where('gradeLevelFees.'.GradeLevel::GRADE_1->value.'.tuition', 20000)
            ->where('gradeLevelFees.'.GradeLevel::GRADE_1->value.'.miscellaneous', 5000)
        );
    });

    test('guardian can only view their children enrollments', function () {
        $guardian = User::factory()->create();
        $guardian->assignRole('guardian');

        $guardianModel = Guardian::create([
            'user_id' => $guardian->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'contact_number' => '09123456789',
            'address' => '123 Test St',
        ]);

        // Create guardian's children
        $ownChildren = Student::factory()->count(2)->create();
        foreach ($ownChildren as $child) {
            GuardianStudent::create([
                'guardian_id' => $guardian->id,  // Use user id, not guardian model id
                'student_id' => $child->id,
                'relationship_type' => 'father',
                'is_primary_contact' => true,
            ]);

            Enrollment::create([
                'enrollment_id' => 'ENR-'.str_pad($child->id, 4, '0', STR_PAD_LEFT),
                'student_id' => $child->id,
                'guardian_id' => $guardianModel->id,
                'school_year' => '2024-2025',
                'quarter' => Quarter::FIRST,
                'grade_level' => GradeLevel::GRADE_1,
                'status' => EnrollmentStatus::APPROVED,
                'tuition_fee_cents' => 2000000,
                'miscellaneous_fee_cents' => 500000,
                'total_amount_cents' => 25000,
                'net_amount_cents' => 25000,
                'amount_paid_cents' => 0,
                'balance_cents' => 25000,
                'payment_status' => PaymentStatus::PENDING,
            ]);
        }

        // Create other student not related to guardian
        $otherStudent = Student::factory()->create();
        Enrollment::create([
            'enrollment_id' => 'ENR-9999',
            'student_id' => $otherStudent->id,
            'guardian_id' => Guardian::factory()->create()->id,
            'school_year' => '2024-2025',
            'quarter' => Quarter::FIRST,
            'grade_level' => GradeLevel::GRADE_2,
            'status' => EnrollmentStatus::APPROVED,
            'tuition_fee_cents' => 2500000,
            'miscellaneous_fee_cents' => 500000,
            'total_amount_cents' => 3000000,
            'net_amount_cents' => 3000000,
            'amount_paid_cents' => 0,
            'balance_cents' => 3000000,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($guardian)->get(route('tuition'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('shared/tuition')
            ->has('enrollments.data', 2) // Only guardian's children
        );
    });

    test('registrar can view all enrollments', function () {
        $registrar = User::factory()->create();
        $registrar->assignRole('registrar');

        Student::factory()->count(5)->create()->each(function ($student) {
            Enrollment::create([
                'enrollment_id' => 'ENR-'.str_pad($student->id, 4, '0', STR_PAD_LEFT),
                'student_id' => $student->id,
                'guardian_id' => Guardian::factory()->create()->id,
                'school_year' => '2024-2025',
                'quarter' => Quarter::FIRST,
                'grade_level' => GradeLevel::GRADE_1,
                'status' => EnrollmentStatus::APPROVED,
                'tuition_fee_cents' => 2000000,
                'miscellaneous_fee_cents' => 500000,
                'total_amount_cents' => 2500000,
                'net_amount_cents' => 2500000,
                'amount_paid_cents' => 0,
                'balance_cents' => 2500000,
                'payment_status' => PaymentStatus::PENDING,
            ]);
        });

        $response = $this->actingAs($registrar)->get(route('tuition'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('shared/tuition')
            ->has('enrollments.data', 5)
        );
    });
});

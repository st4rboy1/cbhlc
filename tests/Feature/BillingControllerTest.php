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

describe('tuition method', function () {
    test('admin can view all enrollments with tuition fees', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $students = Student::factory()->count(3)->create();
        $enrollments = [];

        foreach ($students as $student) {
            $enrollments[] = Enrollment::create([
                'enrollment_id' => 'ENR-' . str_pad($student->id, 4, '0', STR_PAD_LEFT),
                'student_id' => $student->id,
                'guardian_id' => Guardian::factory()->create()->id,
                'school_year' => '2024-2025',
                'quarter' => Quarter::FIRST,
                'grade_level' => GradeLevel::GRADE_1,
                'enrollment_status' => EnrollmentStatus::APPROVED,
                'tuition_fee' => 20000,
                'miscellaneous_fee' => 5000,
                'laboratory_fee' => 2000,
                'total_amount' => 27000,
                'net_amount' => 27000,
                'amount_paid' => 0,
                'balance' => 27000,
                'payment_status' => PaymentStatus::PENDING,
            ]);
        }

        // Create grade level fees
        GradeLevelFee::create([
            'grade_level' => GradeLevel::GRADE_1,
            'school_year' => '2024-2025',
            'tuition_fee' => 20000,
            'miscellaneous_fee' => 5000,
            'laboratory_fee' => 2000,
            'library_fee' => 1000,
            'sports_fee' => 500,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/billing/tuition');

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('tuition')
            ->has('enrollments.data', 3)
            ->has('gradeLevelFees')
            ->where('gradeLevelFees.' . GradeLevel::GRADE_1->value . '.tuition', 20000.0)
            ->where('gradeLevelFees.' . GradeLevel::GRADE_1->value . '.miscellaneous', 5000.0)
        );
    });

    test('guardian can only view their children enrollments', function () {
        $guardian = User::factory()->create();
        $guardian->assignRole('guardian');

        $guardianModel = Guardian::create([
            'user_id' => $guardian->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '09123456789',
            'address' => '123 Test St',
        ]);

        // Create guardian's children
        $ownChildren = Student::factory()->count(2)->create();
        foreach ($ownChildren as $child) {
            GuardianStudent::create([
                'guardian_id' => $guardianModel->id,
                'student_id' => $child->id,
                'relationship_type' => 'father',
                'is_primary_contact' => true,
            ]);

            Enrollment::create([
                'enrollment_id' => 'ENR-' . str_pad($child->id, 4, '0', STR_PAD_LEFT),
                'student_id' => $child->id,
                'guardian_id' => $guardianModel->id,
                'school_year' => '2024-2025',
                'quarter' => Quarter::FIRST,
                'grade_level' => GradeLevel::GRADE_1,
                'enrollment_status' => EnrollmentStatus::APPROVED,
                'tuition_fee' => 20000,
                'miscellaneous_fee' => 5000,
                'total_amount' => 25000,
                'net_amount' => 25000,
                'amount_paid' => 0,
                'balance' => 25000,
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
            'enrollment_status' => EnrollmentStatus::APPROVED,
            'tuition_fee' => 25000,
            'miscellaneous_fee' => 5000,
            'total_amount' => 30000,
            'net_amount' => 30000,
            'amount_paid' => 0,
            'balance' => 30000,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($guardian)->get('/billing/tuition');

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('tuition')
            ->has('enrollments.data', 2) // Only guardian's children
        );
    });

    test('registrar can view all enrollments', function () {
        $registrar = User::factory()->create();
        $registrar->assignRole('registrar');

        Student::factory()->count(5)->create()->each(function ($student) {
            Enrollment::create([
                'enrollment_id' => 'ENR-' . str_pad($student->id, 4, '0', STR_PAD_LEFT),
                'student_id' => $student->id,
                'guardian_id' => Guardian::factory()->create()->id,
                'school_year' => '2024-2025',
                'quarter' => Quarter::FIRST,
                'grade_level' => GradeLevel::GRADE_3,
                'enrollment_status' => EnrollmentStatus::APPROVED,
                'tuition_fee' => 22000,
                'miscellaneous_fee' => 5500,
                'total_amount' => 27500,
                'net_amount' => 27500,
                'amount_paid' => 0,
                'balance' => 27500,
                'payment_status' => PaymentStatus::PENDING,
            ]);
        });

        $response = $this->actingAs($registrar)->get('/billing/tuition');

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('tuition')
            ->has('enrollments.data', 5)
        );
    });
});

describe('invoice method', function () {
    test('admin can view any enrollment invoice', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $student = Student::factory()->create();
        $enrollment = Enrollment::create([
            'enrollment_id' => 'ENR-0001',
            'student_id' => $student->id,
            'guardian_id' => Guardian::factory()->create()->id,
            'school_year' => '2024-2025',
            'quarter' => Quarter::FIRST,
            'grade_level' => GradeLevel::GRADE_4,
            'enrollment_status' => EnrollmentStatus::APPROVED,
            'tuition_fee' => 23000,
            'miscellaneous_fee' => 6000,
            'total_amount' => 29000,
            'net_amount' => 29000,
            'amount_paid' => 0,
            'balance' => 29000,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($admin)->get('/billing/invoice/' . $enrollment->id);

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('invoice')
            ->has('enrollment')
            ->where('enrollment.id', $enrollment->id)
            ->where('invoiceNumber', 'ENR-0001')
            ->has('currentDate')
        );
    });

    test('guardian can only view their children invoice', function () {
        $guardian = User::factory()->create();
        $guardian->assignRole('guardian');

        $guardianModel = Guardian::create([
            'user_id' => $guardian->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'phone' => '09123456789',
            'address' => '456 Test Ave',
        ]);

        // Create guardian's child
        $ownChild = Student::factory()->create();
        GuardianStudent::create([
            'guardian_id' => $guardianModel->id,
            'student_id' => $ownChild->id,
            'relationship_type' => 'mother',
            'is_primary_contact' => true,
        ]);

        $ownEnrollment = Enrollment::create([
            'enrollment_id' => 'ENR-0002',
            'student_id' => $ownChild->id,
            'guardian_id' => $guardianModel->id,
            'school_year' => '2024-2025',
            'quarter' => Quarter::SECOND,
            'grade_level' => GradeLevel::GRADE_5,
            'enrollment_status' => EnrollmentStatus::APPROVED,
            'tuition_fee' => 24000,
            'miscellaneous_fee' => 6500,
            'total_amount' => 30500,
            'net_amount' => 30500,
            'amount_paid' => 10000,
            'balance' => 20500,
            'payment_status' => PaymentStatus::PARTIAL,
        ]);

        // Create other student
        $otherStudent = Student::factory()->create();
        $otherEnrollment = Enrollment::create([
            'enrollment_id' => 'ENR-0003',
            'student_id' => $otherStudent->id,
            'guardian_id' => Guardian::factory()->create()->id,
            'school_year' => '2024-2025',
            'quarter' => Quarter::THIRD,
            'grade_level' => GradeLevel::GRADE_6,
            'enrollment_status' => EnrollmentStatus::APPROVED,
            'tuition_fee' => 25000,
            'miscellaneous_fee' => 7000,
            'total_amount' => 32000,
            'net_amount' => 32000,
            'amount_paid' => 0,
            'balance' => 32000,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        // Guardian can view own child's invoice
        $response = $this->actingAs($guardian)->get('/billing/invoice/' . $ownEnrollment->id);
        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('invoice')
            ->where('enrollment.id', $ownEnrollment->id)
        );

        // Guardian cannot view other child's invoice
        $response = $this->actingAs($guardian)->get('/billing/invoice/' . $otherEnrollment->id);
        $response->assertStatus(404);
    });

    test('guardian gets latest enrollment when no id specified', function () {
        $guardian = User::factory()->create();
        $guardian->assignRole('guardian');

        $guardianModel = Guardian::create([
            'user_id' => $guardian->id,
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'phone' => '09123456789',
            'address' => '789 Test Blvd',
        ]);

        $child = Student::factory()->create();
        GuardianStudent::create([
            'guardian_id' => $guardianModel->id,
            'student_id' => $child->id,
            'relationship_type' => 'father',
            'is_primary_contact' => true,
        ]);

        // Create multiple enrollments
        $oldEnrollment = Enrollment::create([
            'enrollment_id' => 'ENR-0004',
            'student_id' => $child->id,
            'guardian_id' => $guardianModel->id,
            'school_year' => '2023-2024',
            'quarter' => Quarter::FOURTH,
            'grade_level' => GradeLevel::KINDER,
            'enrollment_status' => EnrollmentStatus::APPROVED,
            'tuition_fee' => 18000,
            'miscellaneous_fee' => 4000,
            'total_amount' => 22000,
            'net_amount' => 22000,
            'amount_paid' => 22000,
            'balance' => 0,
            'payment_status' => PaymentStatus::PAID,
            'created_at' => now()->subYear(),
        ]);

        $latestEnrollment = Enrollment::create([
            'enrollment_id' => 'ENR-0005',
            'student_id' => $child->id,
            'guardian_id' => $guardianModel->id,
            'school_year' => '2024-2025',
            'quarter' => Quarter::FIRST,
            'grade_level' => GradeLevel::GRADE_1,
            'enrollment_status' => EnrollmentStatus::APPROVED,
            'tuition_fee' => 20000,
            'miscellaneous_fee' => 5000,
            'total_amount' => 25000,
            'net_amount' => 25000,
            'amount_paid' => 0,
            'balance' => 25000,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($guardian)->get('/billing/invoice');

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('invoice')
            ->where('enrollment.id', $latestEnrollment->id)
            ->where('invoiceNumber', 'ENR-0005')
        );
    });

    test('returns 404 when enrollment not found', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->get('/billing/invoice/99999');
        $response->assertStatus(404);
    });
});

describe('updatePayment method', function () {
    test('admin can update payment status', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $enrollment = Enrollment::create([
            'enrollment_id' => 'ENR-0006',
            'student_id' => Student::factory()->create()->id,
            'guardian_id' => Guardian::factory()->create()->id,
            'school_year' => '2024-2025',
            'quarter' => Quarter::FIRST,
            'grade_level' => GradeLevel::GRADE_2,
            'enrollment_status' => EnrollmentStatus::APPROVED,
            'tuition_fee' => 21000,
            'miscellaneous_fee' => 5500,
            'total_amount' => 26500,
            'net_amount' => 26500,
            'amount_paid' => 0,
            'balance' => 26500,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($admin)->put('/billing/payment/' . $enrollment->id, [
            'amount_paid' => 10000,
            'payment_status' => PaymentStatus::PARTIAL->value,
            'remarks' => 'First payment received',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Payment status updated successfully.');

        $enrollment->refresh();
        expect($enrollment->amount_paid)->toBe(10000.0);
        expect($enrollment->payment_status)->toBe(PaymentStatus::PARTIAL);
        expect($enrollment->balance)->toBe(16500.0);
        expect($enrollment->remarks)->toBe('First payment received');
    });

    test('registrar can update payment status', function () {
        $registrar = User::factory()->create();
        $registrar->assignRole('registrar');

        $enrollment = Enrollment::create([
            'enrollment_id' => 'ENR-0007',
            'student_id' => Student::factory()->create()->id,
            'guardian_id' => Guardian::factory()->create()->id,
            'school_year' => '2024-2025',
            'quarter' => Quarter::SECOND,
            'grade_level' => GradeLevel::GRADE_3,
            'enrollment_status' => EnrollmentStatus::APPROVED,
            'tuition_fee' => 22000,
            'miscellaneous_fee' => 5500,
            'total_amount' => 27500,
            'net_amount' => 27500,
            'amount_paid' => 0,
            'balance' => 27500,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($registrar)->put('/billing/payment/' . $enrollment->id, [
            'amount_paid' => 27500,
            'payment_status' => PaymentStatus::PAID->value,
        ]);

        $response->assertRedirect();
        $enrollment->refresh();
        expect($enrollment->amount_paid)->toBe(27500.0);
        expect($enrollment->payment_status)->toBe(PaymentStatus::PAID);
        expect($enrollment->balance)->toBe(0.0);
    });

    test('guardian cannot update payment status', function () {
        $guardian = User::factory()->create();
        $guardian->assignRole('guardian');

        $enrollment = Enrollment::create([
            'enrollment_id' => 'ENR-0008',
            'student_id' => Student::factory()->create()->id,
            'guardian_id' => Guardian::factory()->create()->id,
            'school_year' => '2024-2025',
            'quarter' => Quarter::THIRD,
            'grade_level' => GradeLevel::GRADE_4,
            'enrollment_status' => EnrollmentStatus::APPROVED,
            'tuition_fee' => 23000,
            'miscellaneous_fee' => 6000,
            'total_amount' => 29000,
            'net_amount' => 29000,
            'amount_paid' => 0,
            'balance' => 29000,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($guardian)->put('/billing/payment/' . $enrollment->id, [
            'amount_paid' => 15000,
            'payment_status' => PaymentStatus::PARTIAL->value,
        ]);

        $response->assertStatus(403);

        // Verify payment was not updated
        $enrollment->refresh();
        expect($enrollment->amount_paid)->toBe(0.0);
        expect($enrollment->payment_status)->toBe(PaymentStatus::PENDING);
    });

    test('validates payment update data', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $enrollment = Enrollment::create([
            'enrollment_id' => 'ENR-0009',
            'student_id' => Student::factory()->create()->id,
            'guardian_id' => Guardian::factory()->create()->id,
            'school_year' => '2024-2025',
            'quarter' => Quarter::FOURTH,
            'grade_level' => GradeLevel::GRADE_5,
            'enrollment_status' => EnrollmentStatus::APPROVED,
            'tuition_fee' => 24000,
            'miscellaneous_fee' => 6500,
            'total_amount' => 30500,
            'net_amount' => 30500,
            'amount_paid' => 0,
            'balance' => 30500,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        // Test negative amount
        $response = $this->actingAs($admin)->put('/billing/payment/' . $enrollment->id, [
            'amount_paid' => -100,
            'payment_status' => PaymentStatus::PARTIAL->value,
        ]);
        $response->assertSessionHasErrors('amount_paid');

        // Test invalid payment status
        $response = $this->actingAs($admin)->put('/billing/payment/' . $enrollment->id, [
            'amount_paid' => 10000,
            'payment_status' => 'invalid_status',
        ]);
        $response->assertSessionHasErrors('payment_status');

        // Test missing required fields
        $response = $this->actingAs($admin)->put('/billing/payment/' . $enrollment->id, []);
        $response->assertSessionHasErrors(['amount_paid', 'payment_status']);

        // Test remarks max length
        $response = $this->actingAs($admin)->put('/billing/payment/' . $enrollment->id, [
            'amount_paid' => 10000,
            'payment_status' => PaymentStatus::PARTIAL->value,
            'remarks' => str_repeat('a', 501),
        ]);
        $response->assertSessionHasErrors('remarks');
    });

    test('returns 404 when enrollment not found for payment update', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->put('/billing/payment/99999', [
            'amount_paid' => 10000,
            'payment_status' => PaymentStatus::PARTIAL->value,
        ]);

        $response->assertStatus(404);
    });

    test('super admin can update payment status', function () {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $enrollment = Enrollment::create([
            'enrollment_id' => 'ENR-0010',
            'student_id' => Student::factory()->create()->id,
            'guardian_id' => Guardian::factory()->create()->id,
            'school_year' => '2024-2025',
            'quarter' => Quarter::FIRST,
            'grade_level' => GradeLevel::GRADE_6,
            'enrollment_status' => EnrollmentStatus::APPROVED,
            'tuition_fee' => 25000,
            'miscellaneous_fee' => 7000,
            'total_amount' => 32000,
            'net_amount' => 32000,
            'amount_paid' => 16000,
            'balance' => 16000,
            'payment_status' => PaymentStatus::PARTIAL,
        ]);

        $response = $this->actingAs($superAdmin)->put('/billing/payment/' . $enrollment->id, [
            'amount_paid' => 32000,
            'payment_status' => PaymentStatus::PAID->value,
            'remarks' => 'Full payment completed',
        ]);

        $response->assertRedirect();
        $enrollment->refresh();
        expect($enrollment->amount_paid)->toBe(32000.0);
        expect($enrollment->payment_status)->toBe(PaymentStatus::PAID);
        expect($enrollment->balance)->toBe(0.0);
    });
});
<?php

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
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

    // Create school year
    $this->sy2024 = \App\Models\SchoolYear::firstOrCreate([
        'name' => '2024-2025',
        'start_year' => 2024,
        'end_year' => 2025,
        'start_date' => '2024-06-01',
        'end_date' => '2025-05-31',
        'status' => 'active',
    ]);
});

describe('invoice controller', function () {
    test('admin can view any enrollment invoice', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $student = Student::factory()->create();
        $enrollment = Enrollment::create([
            'enrollment_id' => 'ENR-0001',
            'student_id' => $student->id,
            'guardian_id' => Guardian::factory()->create()->id,
            'school_year_id' => $this->sy2024->id,
            'quarter' => Quarter::FIRST,
            'grade_level' => GradeLevel::GRADE_4,
            'status' => EnrollmentStatus::APPROVED,
            'tuition_fee_cents' => 2300000,
            'miscellaneous_fee_cents' => 600000,
            'total_amount_cents' => 2900000,
            'net_amount_cents' => 2900000,
            'amount_paid_cents' => 0,
            'balance_cents' => 2900000,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($admin)->get(route('invoices.show', $enrollment));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('shared/invoice')
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
            'contact_number' => '09123456789',
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
            'school_year_id' => $this->sy2024->id,
            'quarter' => Quarter::SECOND,
            'grade_level' => GradeLevel::GRADE_5,
            'status' => EnrollmentStatus::APPROVED,
            'tuition_fee_cents' => 2400000,
            'miscellaneous_fee_cents' => 650000,
            'total_amount_cents' => 3050000,
            'net_amount_cents' => 3050000,
            'amount_paid_cents' => 1000000,
            'balance_cents' => 2050000,
            'payment_status' => PaymentStatus::PARTIAL,
        ]);

        // Create other student
        $otherStudent = Student::factory()->create();
        $otherEnrollment = Enrollment::create([
            'enrollment_id' => 'ENR-0003',
            'student_id' => $otherStudent->id,
            'guardian_id' => Guardian::factory()->create()->id,
            'school_year_id' => $this->sy2024->id,
            'quarter' => Quarter::THIRD,
            'grade_level' => GradeLevel::GRADE_6,
            'status' => EnrollmentStatus::APPROVED,
            'tuition_fee_cents' => 2500000,
            'miscellaneous_fee_cents' => 700000,
            'total_amount_cents' => 3200000,
            'net_amount_cents' => 3200000,
            'amount_paid_cents' => 0,
            'balance_cents' => 3200000,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        // Guardian can view own child's invoice
        $response = $this->actingAs($guardian)->get(route('invoices.show', $ownEnrollment));
        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('shared/invoice')
            ->where('enrollment.id', $ownEnrollment->id)
        );

        // Guardian cannot view other child's invoice
        $response = $this->actingAs($guardian)->get(route('invoices.show', $otherEnrollment));
        $response->assertStatus(404);
    });

    test('guardian gets latest enrollment when no id specified', function () {
        $guardian = User::factory()->create();
        $guardian->assignRole('guardian');

        $guardianModel = Guardian::create([
            'user_id' => $guardian->id,
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'contact_number' => '09123456789',
            'address' => '789 Test Blvd',
        ]);

        $child = Student::factory()->create();
        GuardianStudent::create([
            'guardian_id' => $guardianModel->id,
            'student_id' => $child->id,
            'relationship_type' => 'father',
            'is_primary_contact' => true,
        ]);

        // Create multiple enrollments - old one first
        $oldEnrollment = Enrollment::create([
            'enrollment_id' => 'ENR-0004',
            'student_id' => $child->id,
            'guardian_id' => $guardianModel->id,
            'school_year_id' => \App\Models\SchoolYear::firstOrCreate(['name' => '2023-2024', 'start_year' => 2023, 'end_year' => 2024, 'start_date' => '2023-06-01', 'end_date' => '2024-05-31', 'status' => 'completed'])->id,
            'quarter' => Quarter::FOURTH,
            'grade_level' => GradeLevel::KINDER,
            'status' => EnrollmentStatus::APPROVED,
            'tuition_fee_cents' => 1800000,
            'miscellaneous_fee_cents' => 400000,
            'total_amount_cents' => 2200000,
            'net_amount_cents' => 2200000,
            'amount_paid_cents' => 2200000,
            'balance_cents' => 0,
            'payment_status' => PaymentStatus::PAID,
        ]);

        // Force the old enrollment to have an earlier timestamp
        $oldEnrollment->created_at = now()->subYear();
        $oldEnrollment->save();

        // Add a small delay to ensure different timestamps
        sleep(1);

        $latestEnrollment = Enrollment::create([
            'enrollment_id' => 'ENR-0005',
            'student_id' => $child->id,
            'guardian_id' => $guardianModel->id,
            'school_year_id' => $this->sy2024->id,
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

        $response = $this->actingAs($guardian)->get(route('invoices.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('shared/invoice')
            ->has('enrollment')
            ->where('invoiceNumber', 'ENR-0005')
        );
    });

    test('returns 404 when enrollment not found', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->get(route('invoices.show', 99999));
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
            'school_year_id' => $this->sy2024->id,
            'quarter' => Quarter::FIRST,
            'grade_level' => GradeLevel::GRADE_2,
            'status' => EnrollmentStatus::APPROVED,
            'tuition_fee_cents' => 2100000,
            'miscellaneous_fee_cents' => 550000,
            'total_amount_cents' => 2650000,
            'net_amount_cents' => 2650000,
            'amount_paid_cents' => 0,
            'balance_cents' => 2650000,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($admin)->put(route('registrar.enrollments.update-payment-status', $enrollment->id), [
            'amount_paid' => 10000,
            'payment_status' => PaymentStatus::PARTIAL->value,
            'remarks' => 'First payment received',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Payment status updated successfully.');

        $enrollment->refresh();
        expect($enrollment->amount_paid)->toBe(100.0);  // 10000 cents = 100.0 via accessor
        expect($enrollment->payment_status)->toBe(PaymentStatus::PARTIAL);
        expect($enrollment->balance)->toBe(26400.0);  // 2640000 cents = 26400.0 via accessor
        expect($enrollment->remarks)->toBe('First payment received');
    });

    test('registrar can update payment status', function () {
        $registrar = User::factory()->create();
        $registrar->assignRole('registrar');

        $enrollment = Enrollment::create([
            'enrollment_id' => 'ENR-0007',
            'student_id' => Student::factory()->create()->id,
            'guardian_id' => Guardian::factory()->create()->id,
            'school_year_id' => $this->sy2024->id,
            'quarter' => Quarter::SECOND,
            'grade_level' => GradeLevel::GRADE_3,
            'status' => EnrollmentStatus::APPROVED,
            'tuition_fee_cents' => 2200000,
            'miscellaneous_fee_cents' => 550000,
            'total_amount_cents' => 2750000,
            'net_amount_cents' => 2750000,
            'amount_paid_cents' => 0,
            'balance_cents' => 2750000,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($registrar)->put(route('registrar.enrollments.update-payment-status', $enrollment->id), [
            'amount_paid' => 2750000,  // Pay full amount in cents
            'payment_status' => PaymentStatus::PAID->value,
        ]);

        $response->assertRedirect();
        $enrollment->refresh();
        expect($enrollment->amount_paid)->toBe(27500.0);  // 2750000 cents = 27500.0 via accessor
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
            'school_year_id' => $this->sy2024->id,
            'quarter' => Quarter::THIRD,
            'grade_level' => GradeLevel::GRADE_4,
            'status' => EnrollmentStatus::APPROVED,
            'tuition_fee_cents' => 2300000,
            'miscellaneous_fee_cents' => 600000,
            'total_amount_cents' => 2900000,
            'net_amount_cents' => 2900000,
            'amount_paid_cents' => 0,
            'balance_cents' => 2900000,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($guardian)->put(route('registrar.enrollments.update-payment-status', $enrollment->id), [
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
            'school_year_id' => $this->sy2024->id,
            'quarter' => Quarter::FOURTH,
            'grade_level' => GradeLevel::GRADE_5,
            'status' => EnrollmentStatus::APPROVED,
            'tuition_fee_cents' => 2400000,
            'miscellaneous_fee_cents' => 650000,
            'total_amount_cents' => 3050000,
            'net_amount_cents' => 3050000,
            'amount_paid_cents' => 0,
            'balance_cents' => 3050000,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        // Test negative amount
        $response = $this->actingAs($admin)->put(route('registrar.enrollments.update-payment-status', $enrollment->id), [
            'amount_paid' => -100,
            'payment_status' => PaymentStatus::PARTIAL->value,
        ]);
        $response->assertSessionHasErrors('amount_paid');

        // Test invalid payment status
        $response = $this->actingAs($admin)->put(route('registrar.enrollments.update-payment-status', $enrollment->id), [
            'amount_paid' => 10000,
            'payment_status' => 'invalid_status',
        ]);
        $response->assertSessionHasErrors('payment_status');

        // Test missing required fields
        $response = $this->actingAs($admin)->put(route('registrar.enrollments.update-payment-status', $enrollment->id), []);
        $response->assertSessionHasErrors(['amount_paid', 'payment_status']);

        // Test remarks max length
        $response = $this->actingAs($admin)->put(route('registrar.enrollments.update-payment-status', $enrollment->id), [
            'amount_paid' => 10000,
            'payment_status' => PaymentStatus::PARTIAL->value,
            'remarks' => str_repeat('a', 501),
        ]);
        $response->assertSessionHasErrors('remarks');
    });

    test('returns 404 when enrollment not found for payment update', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->put(route('registrar.enrollments.update-payment-status', 99999), [
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
            'school_year_id' => $this->sy2024->id,
            'quarter' => Quarter::FIRST,
            'grade_level' => GradeLevel::GRADE_6,
            'status' => EnrollmentStatus::APPROVED,
            'tuition_fee_cents' => 2500000,
            'miscellaneous_fee_cents' => 700000,
            'total_amount_cents' => 3200000,
            'net_amount_cents' => 3200000,
            'amount_paid_cents' => 1600000,
            'balance_cents' => 1600000,
            'payment_status' => PaymentStatus::PARTIAL,
        ]);

        $response = $this->actingAs($superAdmin)->put(route('registrar.enrollments.update-payment-status', $enrollment->id), [
            'amount_paid' => 3200000,  // Pay full amount in cents
            'payment_status' => PaymentStatus::PAID->value,
            'remarks' => 'Full payment completed',
        ]);

        $response->assertRedirect();
        $enrollment->refresh();
        expect($enrollment->amount_paid)->toBe(32000.0);  // 3200000 cents = 32000.0 via accessor
        expect($enrollment->payment_status)->toBe(PaymentStatus::PAID);
        expect($enrollment->balance)->toBe(0.0);
    });
});

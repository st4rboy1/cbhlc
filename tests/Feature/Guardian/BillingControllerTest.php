<?php

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\PaymentStatus;
use App\Models\Enrollment;
use App\Models\GradeLevelFee;
use App\Models\Guardian;
use App\Models\GuardianStudent;
use App\Models\Student;
use App\Models\User;
use App\Services\CurrencyService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    // Create currency service
    $this->currencyService = new CurrencyService;

    // Create guardian user
    $this->guardian = User::factory()->create();
    $this->guardian->assignRole('guardian');

    // Create guardian model
    $this->guardianModel = Guardian::create([
        'user_id' => $this->guardian->id,
        'first_name' => 'Test',
        'last_name' => 'Guardian',
        'contact_number' => '09123456789',
        'address' => '123 Test St',
    ]);

    // Create student
    $this->student = Student::factory()->create([
        'first_name' => 'John',
        'middle_name' => 'Michael',
        'last_name' => 'Doe',
        'student_id' => 'STU-001',
    ]);

    // Link guardian to student
    GuardianStudent::create([
        'guardian_id' => $this->guardian->id,
        'student_id' => $this->student->id,
        'relationship_type' => 'mother',
        'is_primary_contact' => true,
    ]);
});

describe('Guardian BillingController', function () {
    test('guardian can view billing index', function () {
        // Create grade level fee
        GradeLevelFee::create([
            'grade_level' => GradeLevel::GRADE_1->value,
            'school_year' => '2024-2025',
            'tuition_fee_cents' => 2000000,  // 20000 * 100
            'miscellaneous_fee_cents' => 500000,  // 5000 * 100
        ]);

        // Create enrollment
        Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'school_year' => '2024-2025',
            'grade_level' => GradeLevel::GRADE_1,
            'status' => EnrollmentStatus::ENROLLED,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.billing.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/billing/index')
            ->has('enrollments')
            ->has('summary')
            ->has('paymentPlans')
        );
    });

    test('billing index shows correct enrollment data', function () {
        // Create grade level fee
        GradeLevelFee::create([
            'grade_level' => GradeLevel::GRADE_2->value,
            'school_year' => '2024-2025',
            'tuition_fee_cents' => 2200000,  // 22000 * 100
            'miscellaneous_fee_cents' => 550000,  // 5500 * 100
        ]);

        // Create enrollment
        Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'school_year' => '2024-2025',
            'grade_level' => GradeLevel::GRADE_2,
            'status' => EnrollmentStatus::ENROLLED,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.billing.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/billing/index')
            ->has('enrollments.0', fn ($enrollment) => $enrollment
                ->where('student_name', 'John Michael Doe')
                ->where('student_id', 'STU-001')
                ->where('school_year', '2024-2025')
                ->where('grade_level', GradeLevel::GRADE_2->value)
                ->where('tuition_fee', '₱22,000.00')
                ->where('miscellaneous_fee', '₱5,500.00')
                ->where('total_amount', '₱27,500.00')
                ->etc()
            )
        );
    });

    test('billing index excludes rejected enrollments', function () {
        // Create enrollments
        Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'status' => EnrollmentStatus::ENROLLED,
        ]);

        Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'status' => EnrollmentStatus::REJECTED,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.billing.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/billing/index')
            ->has('enrollments', 1) // Should only have 1 enrollment (not rejected)
        );
    });

    test('billing index calculates summary correctly', function () {
        // Create grade level fees
        GradeLevelFee::create([
            'grade_level' => GradeLevel::GRADE_1->value,
            'school_year' => '2024-2025',
            'tuition_fee_cents' => 2000000,  // 20000 * 100
            'miscellaneous_fee_cents' => 500000,  // 5000 * 100
        ]);

        // Create multiple enrollments with different payment statuses
        Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'school_year' => '2024-2025',
            'grade_level' => GradeLevel::GRADE_1,
            'status' => EnrollmentStatus::ENROLLED,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        // Create another student for the same guardian
        $student2 = Student::factory()->create();
        GuardianStudent::create([
            'guardian_id' => $this->guardian->id,
            'student_id' => $student2->id,
            'relationship_type' => 'mother',
        ]);

        Enrollment::factory()->create([
            'student_id' => $student2->id,
            'guardian_id' => $this->guardian->id,
            'school_year' => '2024-2025',
            'grade_level' => GradeLevel::GRADE_1,
            'status' => EnrollmentStatus::ENROLLED,
            'payment_status' => PaymentStatus::PAID,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.billing.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/billing/index')
            ->where('summary.total_due', '₱25,000.00') // 1 pending enrollment
            ->where('summary.total_paid', '₱25,000.00') // 1 paid enrollment
            ->where('summary.pending_count', 1)
            ->where('summary.overdue_count', 0)
        );
    });

    test('billing index includes payment plans', function () {
        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.billing.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/billing/index')
            ->has('paymentPlans', 4) // Annual, Semestral, Quarterly, Monthly
            ->has('paymentPlans.0', fn ($plan) => $plan
                ->where('name', 'Annual')
                ->where('discount', '5%')
                ->etc()
            )
        );
    });

    test('guardian can view billing details for enrollment', function () {
        // Create grade level fee
        GradeLevelFee::create([
            'grade_level' => GradeLevel::GRADE_3->value,
            'school_year' => '2024-2025',
            'tuition_fee_cents' => 2400000,  // 24000 * 100
            'miscellaneous_fee_cents' => 600000,  // 6000 * 100
        ]);

        $enrollment = Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'school_year' => '2024-2025',
            'grade_level' => GradeLevel::GRADE_3,
            'status' => EnrollmentStatus::ENROLLED,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.billing.show', $enrollment));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/billing/show')
            ->has('enrollment')
            ->has('billing')
            ->has('paymentInstructions')
        );
    });

    test('billing show displays correct data', function () {
        // Create grade level fee
        GradeLevelFee::create([
            'grade_level' => GradeLevel::GRADE_4->value,
            'school_year' => '2024-2025',
            'tuition_fee_cents' => 2600000,  // 26000 * 100
            'miscellaneous_fee_cents' => 650000,  // 6500 * 100
        ]);

        $enrollment = Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'school_year' => '2024-2025',
            'grade_level' => GradeLevel::GRADE_4,
            'status' => EnrollmentStatus::ENROLLED,
            'payment_status' => PaymentStatus::PARTIAL,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.billing.show', $enrollment));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/billing/show')
            ->where('enrollment.student_name', 'John Michael Doe')
            ->where('enrollment.student_id', 'STU-001')
            ->where('enrollment.school_year', '2024-2025')
            ->where('enrollment.grade_level', GradeLevel::GRADE_4->value)
            ->where('enrollment.payment_status', PaymentStatus::PARTIAL->value)
            ->where('billing.tuition_fee', '₱26,000.00')
            ->where('billing.miscellaneous_fee', '₱6,500.00')
            ->where('billing.total_amount', '₱32,500.00')
        );
    });

    test('billing show generates quarterly payment schedule', function () {
        // Create grade level fee
        GradeLevelFee::create([
            'grade_level' => GradeLevel::GRADE_5->value,
            'school_year' => '2024-2025',
            'tuition_fee_cents' => 2800000,  // 28000 * 100
            'miscellaneous_fee_cents' => 700000,  // 7000 * 100
        ]);

        $enrollment = Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'school_year' => '2024-2025',
            'grade_level' => GradeLevel::GRADE_5,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.billing.show', $enrollment));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/billing/show')
            ->has('billing.payment_schedule', 4) // 4 quarters
            ->has('billing.payment_schedule.0', fn ($schedule) => $schedule
                ->where('period', 'First Quarter')
                ->where('amount', '₱8,750.00') // 35000 / 4
                ->where('status', 'pending')
                ->etc()
            )
        );
    });

    test('billing show includes payment instructions', function () {
        $enrollment = Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'status' => EnrollmentStatus::PENDING->value,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.billing.show', $enrollment));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/billing/show')
            ->has('paymentInstructions', fn ($instructions) => $instructions
                ->where('bank_name', 'Bank of the Philippine Islands')
                ->where('account_name', 'Christian Bible Heritage Learning Center')
                ->has('account_number')
                ->has('notes')
            )
        );
    });

    test('guardian cannot view billing for other students', function () {
        $otherStudent = Student::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'student_id' => $otherStudent->id,
            'guardian_id' => Guardian::factory()->create()->id,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.billing.show', $enrollment));

        $response->assertStatus(403);
    });

    test('billing handles missing grade level fees', function () {
        // Create enrollment without corresponding grade level fee
        $enrollment = Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'school_year' => '2024-2025',
            'grade_level' => GradeLevel::GRADE_6,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.billing.show', $enrollment));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/billing/show')
            ->where('billing.tuition_fee', '₱0.00')
            ->where('billing.miscellaneous_fee', '₱0.00')
            ->where('billing.total_amount', '₱0.00')
        );
    });

    test('guardian only sees their children enrollments', function () {
        // Create enrollment for guardian's child with explicit non-rejected status
        Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'status' => EnrollmentStatus::PENDING->value,
        ]);

        // Create another student and enrollment for different guardian
        $otherGuardian = User::factory()->create();
        $otherGuardian->assignRole('guardian');
        $otherStudent = Student::factory()->create();

        GuardianStudent::create([
            'guardian_id' => $otherGuardian->id,
            'student_id' => $otherStudent->id,
            'relationship_type' => 'father',
        ]);

        Enrollment::factory()->create([
            'student_id' => $otherStudent->id,
            'guardian_id' => $otherGuardian->id,
            'status' => EnrollmentStatus::PENDING->value,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.billing.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/billing/index')
            ->has('enrollments', 1) // Should only see their own child's enrollment
        );
    });

    test('billing show handles student without middle name', function () {
        // Create student without middle name
        $studentNoMiddle = Student::factory()->create([
            'first_name' => 'Jane',
            'middle_name' => null,
            'last_name' => 'Smith',
        ]);

        GuardianStudent::create([
            'guardian_id' => $this->guardian->id,
            'student_id' => $studentNoMiddle->id,
            'relationship_type' => 'mother',
        ]);

        $enrollment = Enrollment::factory()->create([
            'student_id' => $studentNoMiddle->id,
            'guardian_id' => $this->guardian->id,
            'status' => EnrollmentStatus::PENDING->value,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.billing.show', $enrollment));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/billing/show')
            ->where('enrollment.student_name', 'Jane Smith') // No extra space for middle name
        );
    });

    test('billing payment schedule uses correct school years', function () {
        $enrollment = Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardian->id,
            'school_year' => '2024-2025',
            'status' => EnrollmentStatus::PENDING->value,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.billing.show', $enrollment));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/billing/show')
            ->where('billing.payment_schedule.0.due_date', 'August 15, 2024')
            ->where('billing.payment_schedule.2.due_date', 'January 15, 2025') // Third quarter in next year
        );
    });
});

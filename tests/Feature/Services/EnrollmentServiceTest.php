<?php

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Enrollment;
use App\Models\Student;
use App\Services\EnrollmentService;
use App\Services\InvoiceService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles and permissions for each test
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->invoiceServiceMock = Mockery::mock(InvoiceService::class);

    // Ensure the mock is set up before the service is instantiated where it might use the mock
    $this->invoiceServiceMock->shouldReceive('createInvoiceFromEnrollment')
        ->andReturnUsing(function () {
            return \App\Models\Invoice::factory()->create([
                'total_amount' => 1000,
                'due_date' => now()->addDays(30),
            ]);
        });

    $this->service = new EnrollmentService(new Enrollment, $this->invoiceServiceMock);

    // Create school year for regular tests
    $this->sy2024 = \App\Models\SchoolYear::firstOrCreate([
        'name' => '2024-2025',
        'start_year' => 2024,
        'end_year' => 2025,
        'start_date' => '2024-06-01',
        'end_date' => '2025-05-31',
        'status' => 'active',
    ]);

    // Create current year school year for statistics tests
    $currentYear = date('Y');
    $this->syCurrentYear = \App\Models\SchoolYear::firstOrCreate([
        'name' => $currentYear.'-'.($currentYear + 1),
        'start_year' => $currentYear,
        'end_year' => $currentYear + 1,
        'start_date' => $currentYear.'-06-01',
        'end_date' => ($currentYear + 1).'-05-31',
        'status' => 'active',
    ]);
});

test('getPaginatedEnrollments returns paginated results with relationships', function () {
    Enrollment::factory()->count(15)->create([
        'school_year_id' => $this->sy2024->id,
    ]);

    $result = $this->service->getPaginatedEnrollments([], 10);

    expect($result)->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class);
    expect($result->count())->toBe(10);
    expect($result->total())->toBe(15);
});

test('getPaginatedEnrollments applies status filter', function () {
    Enrollment::factory()->create([
        'status' => EnrollmentStatus::PENDING,
        'school_year_id' => $this->sy2024->id,
    ]);
    Enrollment::factory()->create([
        'status' => EnrollmentStatus::APPROVED,
        'school_year_id' => $this->sy2024->id,
    ]);
    Enrollment::factory()->create([
        'status' => EnrollmentStatus::REJECTED,
        'school_year_id' => $this->sy2024->id,
    ]);

    $result = $this->service->getPaginatedEnrollments(['status' => EnrollmentStatus::PENDING->value], 10);

    expect($result->count())->toBe(1);
    expect($result->first()->status)->toBe(EnrollmentStatus::PENDING);
});

test('getPaginatedEnrollments applies student filter', function () {
    $student1 = Student::factory()->create();
    $student2 = Student::factory()->create();

    Enrollment::factory()->create([
        'student_id' => $student1->id,
        'school_year_id' => $this->sy2024->id,
    ]);
    Enrollment::factory()->create([
        'student_id' => $student2->id,
        'school_year_id' => $this->sy2024->id,
    ]);

    $result = $this->service->getPaginatedEnrollments(['student_id' => $student1->id], 10);

    expect($result->count())->toBe(1);
    expect($result->first()->student_id)->toBe($student1->id);
});

test('getPaginatedEnrollments applies date range filter', function () {
    Enrollment::factory()->create([
        'created_at' => now()->subDays(5),
        'school_year_id' => $this->sy2024->id,
    ]);
    Enrollment::factory()->create([
        'created_at' => now()->subDays(2),
        'school_year_id' => $this->sy2024->id,
    ]);
    Enrollment::factory()->create([
        'created_at' => now(),
        'school_year_id' => $this->sy2024->id,
    ]);

    $result = $this->service->getPaginatedEnrollments([
        'date_from' => now()->subDays(3)->toDateString(),
        'date_to' => now()->toDateString(),
    ], 10);

    expect($result->count())->toBe(2);
});

test('findWithRelations returns enrollment with relationships', function () {
    $enrollment = Enrollment::factory()->create([
        'school_year_id' => $this->sy2024->id,
    ]);

    $result = $this->service->findWithRelations($enrollment->id);

    expect($result)->toBeInstanceOf(Enrollment::class);
    expect($result->relationLoaded('student'))->toBe(true);
    expect($result->relationLoaded('invoices'))->toBe(true);
    expect($result->relationLoaded('payments'))->toBe(true);
});

test('createEnrollment creates new enrollment with pending status', function () {
    $guardianUser = \App\Models\User::factory()->create();
    $guardian = \App\Models\Guardian::create([
        'user_id' => $guardianUser->id,
        'first_name' => 'Test',
        'last_name' => 'Guardian',
        'contact_number' => '09123456789',
        'address' => '123 Test St',
    ]);
    $student = Student::factory()->create();

    $data = [
        'student_id' => $student->id,
        'guardian_id' => $guardian->id,
        'grade_level' => 'Grade 1',
        'school_year_id' => $this->sy2024->id,
        'enrollment_date' => now()->toDateString(),
    ];

    $result = $this->service->createEnrollment($data);

    expect($result)->toBeInstanceOf(Enrollment::class);
    expect($result->status)->toBe(EnrollmentStatus::PENDING);
    expect($result->payment_status)->toBe(PaymentStatus::PENDING);
    expect($result->student_id)->toBe($student->id);
    $this->assertDatabaseHas('enrollments', [
        'student_id' => $student->id,
        'status' => EnrollmentStatus::PENDING,
    ]);
});

test('createEnrollment generates reference number', function () {
    $guardianUser = \App\Models\User::factory()->create();
    $guardian = \App\Models\Guardian::create([
        'user_id' => $guardianUser->id,
        'first_name' => 'Test',
        'last_name' => 'Guardian',
        'contact_number' => '09123456789',
        'address' => '123 Test St',
    ]);
    $student = Student::factory()->create();

    $data = [
        'student_id' => $student->id,
        'guardian_id' => $guardian->id,
        'grade_level' => 'Grade 1',
        'school_year_id' => $this->sy2024->id,
        'enrollment_date' => now()->toDateString(),
    ];

    $result = $this->service->createEnrollment($data);

    expect($result->enrollment_id)->toStartWith('ENR-');
    expect(strlen($result->enrollment_id))->toBe(8); // ENR- + 4 digits
});

test('approveEnrollment updates status to approved', function () {
    $enrollment = Enrollment::factory()->create([
        'status' => EnrollmentStatus::PENDING,
        'school_year_id' => $this->sy2024->id,
    ]);

    $this->invoiceServiceMock->shouldReceive('createInvoiceFromEnrollment')
        ->andReturnUsing(function () use ($enrollment) {
            return \App\Models\Invoice::factory()->create([
                'enrollment_id' => $enrollment->id,
                'total_amount' => 1000,
                'due_date' => now()->addDays(30),
            ]);
        });

    $result = $this->service->approveEnrollment($enrollment);

    expect($result->status)->toBe(EnrollmentStatus::READY_FOR_PAYMENT);
    expect($result->approved_at)->not->toBeNull();
    $this->assertDatabaseHas('enrollments', [
        'id' => $enrollment->id,
        'status' => EnrollmentStatus::READY_FOR_PAYMENT,
    ]);
});

test('approveEnrollment throws exception for non-pending enrollment', function () {
    $enrollment = Enrollment::factory()->create([
        'status' => EnrollmentStatus::ENROLLED,
        'school_year_id' => $this->sy2024->id,
    ]);

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Only pending enrollments can be approved');

    $this->service->approveEnrollment($enrollment);
});

test('rejectEnrollment updates status with reason', function () {
    $enrollment = Enrollment::factory()->create([
        'status' => EnrollmentStatus::PENDING,
        'school_year_id' => $this->sy2024->id,
    ]);
    $reason = 'Incomplete documents';

    $result = $this->service->rejectEnrollment($enrollment, $reason);

    expect($result->status)->toBe(EnrollmentStatus::REJECTED);
    expect($result->remarks)->toBe($reason);
    expect($result->rejected_at)->not->toBeNull();
});

test('rejectEnrollment throws exception for non-pending enrollment', function () {
    $enrollment = Enrollment::factory()->create([
        'status' => EnrollmentStatus::APPROVED,
        'school_year_id' => $this->sy2024->id,
    ]);

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Only pending enrollments can be rejected');

    $this->service->rejectEnrollment($enrollment);
});

test('bulkApproveEnrollments approves multiple pending enrollments', function () {
    $pending1 = Enrollment::factory()->create([
        'status' => EnrollmentStatus::PENDING,
        'school_year_id' => $this->sy2024->id,
    ]);
    $pending2 = Enrollment::factory()->create([
        'status' => EnrollmentStatus::PENDING,
        'school_year_id' => $this->sy2024->id,
    ]);
    $approved = Enrollment::factory()->create([
        'status' => EnrollmentStatus::APPROVED,
        'school_year_id' => $this->sy2024->id,
    ]);

    $count = $this->service->bulkApproveEnrollments([
        $pending1->id,
        $pending2->id,
        $approved->id,
    ]);

    expect($count)->toBe(2);
    $this->assertDatabaseHas('enrollments', [
        'id' => $pending1->id,
        'status' => EnrollmentStatus::ENROLLED,
    ]);
    $this->assertDatabaseHas('enrollments', [
        'id' => $pending2->id,
        'status' => EnrollmentStatus::ENROLLED,
    ]);
});

test('bulkApproveEnrollments uses database transaction', function () {
    DB::shouldReceive('transaction')
        ->once()
        ->andReturnUsing(function ($callback) {
            return $callback();
        });

    $pending = Enrollment::factory()->create([
        'status' => EnrollmentStatus::PENDING,
        'school_year_id' => $this->sy2024->id,
    ]);

    $this->service->bulkApproveEnrollments([$pending->id]);
});

test('updatePaymentStatus updates enrollment payment status', function () {
    $enrollment = Enrollment::factory()->create([
        'payment_status' => PaymentStatus::PENDING,
        'school_year_id' => $this->sy2024->id,
    ]);

    $result = $this->service->updatePaymentStatus($enrollment, PaymentStatus::PAID);

    expect($result->payment_status)->toBe(PaymentStatus::PAID);
    $this->assertDatabaseHas('enrollments', [
        'id' => $enrollment->id,
        'payment_status' => PaymentStatus::PAID,
    ]);
});

test('updatePaymentStatus logs activity', function () {
    Log::spy();

    $enrollment = Enrollment::factory()->create([
        'payment_status' => PaymentStatus::PENDING,
        'school_year_id' => $this->sy2024->id,
    ]);

    $this->service->updatePaymentStatus($enrollment, PaymentStatus::PAID);

    Log::shouldHaveReceived('info')
        ->once()
        ->with('Service action: updatePaymentStatus', \Mockery::any());
});

test('calculateFees returns fee breakdown for enrollment', function () {
    // Create a grade level fee for testing
    \App\Models\GradeLevelFee::factory()->create([
        'grade_level' => 'Grade 1',
        'tuition_fee_cents' => 5000000, // 50000 * 100
        'registration_fee_cents' => 500000, // 5000 * 100
        'miscellaneous_fee_cents' => 1000000, // 10000 * 100
        'laboratory_fee_cents' => 0,
        'library_fee_cents' => 0,
        'sports_fee_cents' => 0,
        'other_fees_cents' => 0,
    ]);

    $result = $this->service->calculateFees('Grade 1');

    expect($result)->toHaveKeys(['tuition', 'registration', 'miscellaneous', 'total']);
    expect((float) $result['tuition'])->toBe(50000.0);
    expect((float) $result['registration'])->toBe(5000.0);
    expect((float) $result['miscellaneous'])->toBe(10000.0);
    expect((float) $result['total'])->toBe(65000.0);
});

test('canEnroll returns true when student has no pending enrollment', function () {
    $student = Student::factory()->create();
    $sy2023 = \App\Models\SchoolYear::firstOrCreate(['name' => '2023-2024', 'start_year' => 2023, 'end_year' => 2024, 'start_date' => '2023-06-01', 'end_date' => '2024-05-31', 'status' => 'completed']);
    Enrollment::factory()->create([
        'student_id' => $student->id,
        'status' => EnrollmentStatus::APPROVED,
        'school_year_id' => $sy2023->id,
    ]);

    $result = $this->service->canEnroll($student, '2024-2025');

    expect($result)->toBe(true);
});

test('canEnroll returns false when student has pending enrollment', function () {
    $student = Student::factory()->create();
    Enrollment::factory()->create([
        'student_id' => $student->id,
        'status' => EnrollmentStatus::PENDING,
        'school_year_id' => $this->sy2024->id,
    ]);

    $result = $this->service->canEnroll($student, '2024-2025');

    expect($result)->toBe(false);
});

test('canEnroll returns false when student has approved enrollment for same year', function () {
    $student = Student::factory()->create();
    Enrollment::factory()->create([
        'student_id' => $student->id,
        'status' => EnrollmentStatus::APPROVED,
        'school_year_id' => $this->sy2024->id,
    ]);

    $result = $this->service->canEnroll($student, '2024-2025');

    expect($result)->toBe(false);
});

test('getStatistics returns enrollment counts by status', function () {
    Enrollment::factory()->count(5)->create([
        'status' => EnrollmentStatus::PENDING,
        'school_year_id' => $this->syCurrentYear->id,
    ]);
    Enrollment::factory()->count(10)->create([
        'status' => EnrollmentStatus::ENROLLED,
        'school_year_id' => $this->syCurrentYear->id,
    ]);
    Enrollment::factory()->count(2)->create([
        'status' => EnrollmentStatus::REJECTED,
        'school_year_id' => $this->syCurrentYear->id,
    ]);

    $result = $this->service->getStatistics();

    expect($result['total'])->toBe(17);
    expect($result['pending'])->toBe(5);
    expect($result['approved'])->toBe(10);
    expect($result['rejected'])->toBe(2);
});

test('getStatistics filters by current school year', function () {
    // Create enrollments for previous year (should not be counted)
    $sy2023 = \App\Models\SchoolYear::firstOrCreate(['name' => '2023-2024', 'start_year' => 2023, 'end_year' => 2024, 'start_date' => '2023-06-01', 'end_date' => '2024-05-31', 'status' => 'completed']);
    Enrollment::factory()->count(3)->create([
        'status' => EnrollmentStatus::ENROLLED,
        'school_year_id' => $sy2023->id,
    ]);
    // Create enrollments for current year (should be counted)
    Enrollment::factory()->count(5)->create([
        'status' => EnrollmentStatus::ENROLLED,
        'school_year_id' => $this->syCurrentYear->id,
    ]);

    $result = $this->service->getStatistics();

    expect($result['total'])->toBe(5);
    expect($result['approved'])->toBe(5);
});

test('getStatistics calculates payment statistics', function () {
    Enrollment::factory()->count(3)->create([
        'payment_status' => PaymentStatus::PAID,
        'school_year_id' => $this->syCurrentYear->id,
    ]);
    Enrollment::factory()->count(2)->create([
        'payment_status' => PaymentStatus::PARTIAL,
        'school_year_id' => $this->syCurrentYear->id,
    ]);
    Enrollment::factory()->count(5)->create([
        'payment_status' => PaymentStatus::PENDING,
        'school_year_id' => $this->syCurrentYear->id,
    ]);

    $result = $this->service->getStatistics();

    expect($result['paid'])->toBe(3);
    expect($result['partial'])->toBe(2);
    // Note: The service doesn't return 'unpaid', it only tracks paid and partial
});

test('logActivity is called for main operations', function () {
    Log::spy();

    $enrollment = Enrollment::factory()->create([
        'status' => EnrollmentStatus::PENDING,
        'school_year_id' => $this->sy2024->id,
    ]);
    $student = Student::factory()->create();

    $this->invoiceServiceMock->shouldReceive('createInvoiceFromEnrollment')->andReturn(new \App\Models\Invoice(['id' => 1]));

    $this->service->getPaginatedEnrollments();
    $this->service->approveEnrollment($enrollment);
    $this->service->getEnrollmentsByStudent($student->id);

    Log::shouldHaveReceived('info')->times(3);
});

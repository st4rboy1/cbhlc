<?php

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Enrollment;
use App\Models\GradeLevel;
use App\Models\Student;
use App\Services\EnrollmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new EnrollmentService(new Enrollment);
});

test('getPaginatedEnrollments returns paginated results with relationships', function () {
    Enrollment::factory()->count(15)->create();

    $result = $this->service->getPaginatedEnrollments([], 10);

    expect($result)->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class);
    expect($result->count())->toBe(10);
    expect($result->total())->toBe(15);
});

test('getPaginatedEnrollments applies status filter', function () {
    Enrollment::factory()->create(['status' => EnrollmentStatus::PENDING]);
    Enrollment::factory()->create(['status' => EnrollmentStatus::APPROVED]);
    Enrollment::factory()->create(['status' => EnrollmentStatus::REJECTED]);

    $result = $this->service->getPaginatedEnrollments(['status' => EnrollmentStatus::PENDING->value], 10);

    expect($result->count())->toBe(1);
    expect($result->first()->status)->toBe(EnrollmentStatus::PENDING);
});

test('getPaginatedEnrollments applies student filter', function () {
    $student1 = Student::factory()->create();
    $student2 = Student::factory()->create();

    Enrollment::factory()->create(['student_id' => $student1->id]);
    Enrollment::factory()->create(['student_id' => $student2->id]);

    $result = $this->service->getPaginatedEnrollments(['student_id' => $student1->id], 10);

    expect($result->count())->toBe(1);
    expect($result->first()->student_id)->toBe($student1->id);
});

test('getPaginatedEnrollments applies date range filter', function () {
    Enrollment::factory()->create(['created_at' => now()->subDays(5)]);
    Enrollment::factory()->create(['created_at' => now()->subDays(2)]);
    Enrollment::factory()->create(['created_at' => now()]);

    $result = $this->service->getPaginatedEnrollments([
        'date_from' => now()->subDays(3)->toDateString(),
        'date_to' => now()->toDateString(),
    ], 10);

    expect($result->count())->toBe(2);
});

test('findWithRelations returns enrollment with relationships', function () {
    $enrollment = Enrollment::factory()->create();

    $result = $this->service->findWithRelations($enrollment->id);

    expect($result)->toBeInstanceOf(Enrollment::class);
    expect($result->relationLoaded('student'))->toBe(true);
    expect($result->relationLoaded('invoices'))->toBe(true);
    expect($result->relationLoaded('payments'))->toBe(true);
});

test('createEnrollment creates new enrollment with pending status', function () {
    $student = Student::factory()->create();
    $gradeLevel = GradeLevel::factory()->create();

    $data = [
        'student_id' => $student->id,
        'grade_level_id' => $gradeLevel->id,
        'school_year' => '2024-2025',
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
    $student = Student::factory()->create();
    $gradeLevel = GradeLevel::factory()->create();

    $data = [
        'student_id' => $student->id,
        'grade_level_id' => $gradeLevel->id,
        'school_year' => '2024-2025',
        'enrollment_date' => now()->toDateString(),
    ];

    $result = $this->service->createEnrollment($data);

    expect($result->reference_number)->toStartWith('ENR-');
    expect(strlen($result->reference_number))->toBe(14); // ENR- + 10 digits
});

test('approveEnrollment updates status to approved', function () {
    $enrollment = Enrollment::factory()->create(['status' => EnrollmentStatus::PENDING]);

    $result = $this->service->approveEnrollment($enrollment);

    expect($result->status)->toBe(EnrollmentStatus::APPROVED);
    expect($result->approved_at)->not->toBeNull();
    $this->assertDatabaseHas('enrollments', [
        'id' => $enrollment->id,
        'status' => EnrollmentStatus::APPROVED,
    ]);
});

test('approveEnrollment throws exception for non-pending enrollment', function () {
    $enrollment = Enrollment::factory()->create(['status' => EnrollmentStatus::APPROVED]);

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Only pending enrollments can be approved');

    $this->service->approveEnrollment($enrollment);
});

test('rejectEnrollment updates status with reason', function () {
    $enrollment = Enrollment::factory()->create(['status' => EnrollmentStatus::PENDING]);
    $reason = 'Incomplete documents';

    $result = $this->service->rejectEnrollment($enrollment, $reason);

    expect($result->status)->toBe(EnrollmentStatus::REJECTED);
    expect($result->rejection_reason)->toBe($reason);
    expect($result->rejected_at)->not->toBeNull();
});

test('rejectEnrollment throws exception for non-pending enrollment', function () {
    $enrollment = Enrollment::factory()->create(['status' => EnrollmentStatus::APPROVED]);

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Only pending enrollments can be rejected');

    $this->service->rejectEnrollment($enrollment);
});

test('bulkApproveEnrollments approves multiple pending enrollments', function () {
    $pending1 = Enrollment::factory()->create(['status' => EnrollmentStatus::PENDING]);
    $pending2 = Enrollment::factory()->create(['status' => EnrollmentStatus::PENDING]);
    $approved = Enrollment::factory()->create(['status' => EnrollmentStatus::APPROVED]);

    $count = $this->service->bulkApproveEnrollments([
        $pending1->id,
        $pending2->id,
        $approved->id,
    ]);

    expect($count)->toBe(2);
    $this->assertDatabaseHas('enrollments', [
        'id' => $pending1->id,
        'status' => EnrollmentStatus::APPROVED,
    ]);
    $this->assertDatabaseHas('enrollments', [
        'id' => $pending2->id,
        'status' => EnrollmentStatus::APPROVED,
    ]);
});

test('bulkApproveEnrollments uses database transaction', function () {
    DB::shouldReceive('transaction')
        ->once()
        ->andReturnUsing(function ($callback) {
            return $callback();
        });

    $pending = Enrollment::factory()->create(['status' => EnrollmentStatus::PENDING]);

    $this->service->bulkApproveEnrollments([$pending->id]);
});

test('updatePaymentStatus updates enrollment payment status', function () {
    $enrollment = Enrollment::factory()->create(['payment_status' => PaymentStatus::PENDING]);

    $result = $this->service->updatePaymentStatus($enrollment, PaymentStatus::PAID);

    expect($result->payment_status)->toBe(PaymentStatus::PAID);
    $this->assertDatabaseHas('enrollments', [
        'id' => $enrollment->id,
        'payment_status' => PaymentStatus::PAID,
    ]);
});

test('updatePaymentStatus logs activity', function () {
    Log::spy();

    $enrollment = Enrollment::factory()->create(['payment_status' => PaymentStatus::PENDING]);

    $this->service->updatePaymentStatus($enrollment, PaymentStatus::PAID);

    Log::shouldHaveReceived('info')
        ->once()
        ->with('Payment status updated', \Mockery::any());
});

test('calculateFees returns fee breakdown for enrollment', function () {
    $gradeLevel = GradeLevel::factory()->create([
        'tuition_fee' => 50000,
        'registration_fee' => 5000,
        'miscellaneous_fee' => 10000,
    ]);

    $enrollment = Enrollment::factory()->create(['grade_level_id' => $gradeLevel->id]);

    $result = $this->service->calculateFees($enrollment);

    expect($result)->toHaveKeys(['tuition', 'registration', 'miscellaneous', 'total']);
    expect($result['tuition'])->toBe(50000.0);
    expect($result['registration'])->toBe(5000.0);
    expect($result['miscellaneous'])->toBe(10000.0);
    expect($result['total'])->toBe(65000.0);
});

test('canEnroll returns true when student has no pending enrollment', function () {
    $student = Student::factory()->create();
    Enrollment::factory()->create([
        'student_id' => $student->id,
        'status' => EnrollmentStatus::APPROVED,
        'school_year' => '2023-2024',
    ]);

    $result = $this->service->canEnroll($student, '2024-2025');

    expect($result)->toBe(true);
});

test('canEnroll returns false when student has pending enrollment', function () {
    $student = Student::factory()->create();
    Enrollment::factory()->create([
        'student_id' => $student->id,
        'status' => EnrollmentStatus::PENDING,
        'school_year' => '2024-2025',
    ]);

    $result = $this->service->canEnroll($student, '2024-2025');

    expect($result)->toBe(false);
});

test('canEnroll returns false when student has approved enrollment for same year', function () {
    $student = Student::factory()->create();
    Enrollment::factory()->create([
        'student_id' => $student->id,
        'status' => EnrollmentStatus::APPROVED,
        'school_year' => '2024-2025',
    ]);

    $result = $this->service->canEnroll($student, '2024-2025');

    expect($result)->toBe(false);
});

test('getStatistics returns enrollment counts by status', function () {
    Enrollment::factory()->count(5)->create(['status' => EnrollmentStatus::PENDING]);
    Enrollment::factory()->count(10)->create(['status' => EnrollmentStatus::APPROVED]);
    Enrollment::factory()->count(2)->create(['status' => EnrollmentStatus::REJECTED]);

    $result = $this->service->getStatistics();

    expect($result['total'])->toBe(17);
    expect($result['pending'])->toBe(5);
    expect($result['approved'])->toBe(10);
    expect($result['rejected'])->toBe(2);
});

test('getStatistics filters by school year', function () {
    Enrollment::factory()->count(3)->create([
        'status' => EnrollmentStatus::APPROVED,
        'school_year' => '2023-2024',
    ]);
    Enrollment::factory()->count(5)->create([
        'status' => EnrollmentStatus::APPROVED,
        'school_year' => '2024-2025',
    ]);

    $result = $this->service->getStatistics('2024-2025');

    expect($result['total'])->toBe(5);
    expect($result['approved'])->toBe(5);
});

test('getStatistics calculates payment statistics', function () {
    Enrollment::factory()->count(3)->create(['payment_status' => PaymentStatus::PAID]);
    Enrollment::factory()->count(2)->create(['payment_status' => PaymentStatus::PARTIAL]);
    Enrollment::factory()->count(5)->create(['payment_status' => PaymentStatus::PENDING]);

    $result = $this->service->getStatistics();

    expect($result['paid'])->toBe(3);
    expect($result['partial'])->toBe(2);
    expect($result['unpaid'])->toBe(5);
});

test('generateReferenceNumber creates unique reference', function () {
    // Use reflection to test protected method
    $reflection = new ReflectionClass($this->service);
    $method = $reflection->getMethod('generateReferenceNumber');
    $method->setAccessible(true);

    $reference1 = $method->invoke($this->service);
    $reference2 = $method->invoke($this->service);

    expect($reference1)->toStartWith('ENR-');
    expect($reference2)->toStartWith('ENR-');
    expect($reference1)->not->toBe($reference2);
});

test('logActivity is called for main operations', function () {
    Log::spy();

    $enrollment = Enrollment::factory()->create(['status' => EnrollmentStatus::PENDING]);
    $student = Student::factory()->create();

    $this->service->getPaginatedEnrollments();
    $this->service->findWithRelations($enrollment->id);
    $this->service->approveEnrollment($enrollment);

    Log::shouldHaveReceived('info')->times(3);
});

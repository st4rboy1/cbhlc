<?php

use App\Enums\EnrollmentStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Services\DashboardService;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles and permissions for each test
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->service = new DashboardService;
});

test('getQuickStats returns admin statistics', function () {
    // Create test data
    Student::factory()->count(10)->create();
    Enrollment::factory()->count(5)->create(['status' => EnrollmentStatus::APPROVED]);
    Enrollment::factory()->count(3)->create(['status' => EnrollmentStatus::PENDING]);
    Invoice::factory()->count(8)->create(['total_amount' => 10000]);

    $result = $this->service->getQuickStats();

    expect($result)->toHaveKeys([
        'total_students',
        'active_enrollments',
        'pending_enrollments',
        'total_revenue',
        'recent_enrollments',
        'enrollment_trend',
        'revenue_chart',
        'grade_distribution',
    ]);
    expect($result['total_students'])->toBe(10);
    expect($result['active_enrollments'])->toBe(5);
    expect($result['pending_enrollments'])->toBe(3);
});

test('getRegistrarDashboardData returns registrar-specific data', function () {
    // Create enrollments with different statuses
    Enrollment::factory()->count(4)->create(['status' => EnrollmentStatus::PENDING]);
    Enrollment::factory()->count(2)->create(['status' => EnrollmentStatus::APPROVED, 'approved_at' => now()]);
    Enrollment::factory()->create(['status' => EnrollmentStatus::REJECTED, 'rejected_at' => now()]);

    $result = $this->service->getRegistrarDashboardData();

    expect($result)->toHaveKeys([
        'pending_applications',
        'today_processed',
        'week_processed',
        'approval_rate',
        'recent_applications',
        'processing_stats',
    ]);
    expect($result['pending_applications'])->toBe(4);
    expect($result['today_processed'])->toBe(3);
});

test('getParentDashboardData returns parent-specific data', function () {
    $student = Student::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'status' => EnrollmentStatus::APPROVED,
        'payment_status' => PaymentStatus::PARTIAL,
    ]);
    Invoice::factory()->create([
        'enrollment_id' => $enrollment->id,
        'total_amount' => 50000,
        'paid_amount' => 20000,
    ]);

    $result = $this->service->getParentDashboardData($student->guardian_id);

    expect($result)->toHaveKeys([
        'students',
        'enrollments',
        'pending_payments',
        'recent_activities',
        'upcoming_deadlines',
    ]);
    expect($result['students'])->toHaveCount(1);
    expect($result['pending_payments'])->toBe(30000);
});

test('getStudentDashboardData returns student-specific data', function () {
    $student = Student::factory()->create(['grade_level' => 'Grade 5']);
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'status' => EnrollmentStatus::APPROVED,
        'school_year' => '2024-2025',
    ]);

    $result = $this->service->getStudentDashboardData($student->id);

    expect($result)->toHaveKeys([
        'profile',
        'enrollment_status',
        'current_grade',
        'school_year',
        'announcements',
    ]);
    expect($result['profile']['id'])->toBe($student->id);
    expect($result['enrollment_status'])->toBe(EnrollmentStatus::APPROVED->label());
    expect($result['current_grade'])->toBe('Grade 5');
    expect($result['school_year'])->toBe('2024-2025');
});

test('getEnrollmentTrend returns monthly enrollment data', function () {
    // Create enrollments over different months
    Carbon::setTestNow(now());

    Enrollment::factory()->count(5)->create([
        'created_at' => now()->subMonths(2),
        'status' => EnrollmentStatus::APPROVED,
    ]);
    Enrollment::factory()->count(8)->create([
        'created_at' => now()->subMonth(),
        'status' => EnrollmentStatus::APPROVED,
    ]);
    Enrollment::factory()->count(10)->create([
        'created_at' => now(),
        'status' => EnrollmentStatus::APPROVED,
    ]);

    $result = $this->service->getEnrollmentTrend(6);

    expect($result)->toHaveCount(6);
    expect($result[3]['count'])->toBe(5);
    expect($result[4]['count'])->toBe(8);
    expect($result[5]['count'])->toBe(10);
});

test('getRevenueChart returns monthly revenue data', function () {
    Carbon::setTestNow(now());

    // Create payments over different months
    Payment::factory()->count(3)->create([
        'amount' => 10000,
        'payment_date' => now()->subMonths(2),
    ]);
    Payment::factory()->count(5)->create([
        'amount' => 15000,
        'payment_date' => now()->subMonth(),
    ]);
    Payment::factory()->count(2)->create([
        'amount' => 20000,
        'payment_date' => now(),
    ]);

    $result = $this->service->getRevenueChart(3);

    expect($result)->toHaveCount(3);
    expect($result[0]['revenue'])->toBe(30000);
    expect($result[1]['revenue'])->toBe(75000);
    expect($result[2]['revenue'])->toBe(40000);
});

test('getGradeDistribution returns student count by grade level', function () {
    Student::factory()->count(10)->create(['grade_level' => 'Grade 1']);
    Student::factory()->count(8)->create(['grade_level' => 'Grade 2']);
    Student::factory()->count(5)->create(['grade_level' => 'Grade 3']);

    $result = $this->service->getGradeDistribution();

    expect($result)->toHaveCount(3);
    expect($result->firstWhere('grade', 'Grade 1')['count'])->toBe(10);
    expect($result->firstWhere('grade', 'Grade 2')['count'])->toBe(8);
    expect($result->firstWhere('grade', 'Grade 3')['count'])->toBe(5);
});

test('getRecentActivities returns latest system activities', function () {
    $enrollment1 = Enrollment::factory()->create([
        'created_at' => now()->subHours(1),
        'status' => EnrollmentStatus::APPROVED,
    ]);
    $enrollment2 = Enrollment::factory()->create([
        'created_at' => now()->subHours(2),
        'status' => EnrollmentStatus::PENDING,
    ]);
    $payment = Payment::factory()->create([
        'created_at' => now()->subMinutes(30),
        'amount' => 10000,
    ]);

    $result = $this->service->getRecentActivities();

    expect($result)->toHaveCount(3);
    expect($result[0]['type'])->toBe('payment');
    expect($result[1]['type'])->toBe('enrollment');
    expect($result[2]['type'])->toBe('enrollment');
});

test('getRecentActivities limits results', function () {
    Enrollment::factory()->count(15)->create();
    Payment::factory()->count(10)->create();

    $result = $this->service->getRecentActivities(5);

    expect($result)->toHaveCount(5);
});

test('getPendingTasks returns tasks requiring action', function () {
    Enrollment::factory()->count(3)->create(['status' => EnrollmentStatus::PENDING]);
    Invoice::factory()->count(2)->create([
        'due_date' => now()->subDays(2),
        'status' => InvoiceStatus::SENT,
    ]);

    $result = $this->service->getPendingTasks();

    expect($result)->toHaveKeys(['pending_enrollments', 'overdue_invoices']);
    expect($result['pending_enrollments'])->toBe(3);
    expect($result['overdue_invoices'])->toBe(2);
});

test('getQuickStats returns summary statistics', function () {
    Student::factory()->count(50)->create();
    Enrollment::factory()->count(45)->create(['status' => EnrollmentStatus::APPROVED]);
    Invoice::factory()->count(40)->create(['status' => InvoiceStatus::PAID, 'total_amount' => 10000]);

    $result = $this->service->getQuickStats();

    expect($result)->toHaveKeys([
        'total_students',
        'active_enrollments',
        'total_revenue',
        'collection_rate',
    ]);
    expect($result['total_students'])->toBe(50);
    expect($result['active_enrollments'])->toBe(45);
    expect($result['total_revenue'])->toBe(400000);
});

test('getPaymentMethodDistribution returns payment statistics by method', function () {
    Payment::factory()->count(10)->create([
        'payment_method' => PaymentMethod::CASH,
        'amount' => 5000,
    ]);
    Payment::factory()->count(5)->create([
        'payment_method' => PaymentMethod::BANK_TRANSFER,
        'amount' => 10000,
    ]);
    Payment::factory()->count(3)->create([
        'payment_method' => PaymentMethod::CHECK,
        'amount' => 15000,
    ]);

    $result = $this->service->getPaymentMethodDistribution();

    expect($result)->toHaveCount(3);
    expect($result->firstWhere('method', 'Cash')['count'])->toBe(10);
    expect($result->firstWhere('method', 'Cash')['total'])->toBe(50000);
    expect($result->firstWhere('method', 'Bank Transfer')['count'])->toBe(5);
    expect($result->firstWhere('method', 'Bank Transfer')['total'])->toBe(50000);
});

test('getEnrollmentStatusDistribution returns enrollment counts by status', function () {
    Enrollment::factory()->count(15)->create(['status' => EnrollmentStatus::APPROVED]);
    Enrollment::factory()->count(8)->create(['status' => EnrollmentStatus::PENDING]);
    Enrollment::factory()->count(3)->create(['status' => EnrollmentStatus::REJECTED]);

    $result = $this->service->getEnrollmentStatusDistribution();

    expect($result)->toHaveCount(3);
    expect($result->firstWhere('status', 'Approved')['count'])->toBe(15);
    expect($result->firstWhere('status', 'Pending')['count'])->toBe(8);
    expect($result->firstWhere('status', 'Rejected')['count'])->toBe(3);
});

test('getUpcomingDeadlines returns future important dates', function () {
    Invoice::factory()->create([
        'due_date' => now()->addDays(5),
        'status' => InvoiceStatus::SENT,
    ]);
    Invoice::factory()->create([
        'due_date' => now()->addDays(10),
        'status' => InvoiceStatus::SENT,
    ]);
    Invoice::factory()->create([
        'due_date' => now()->subDays(2),
        'status' => InvoiceStatus::SENT,
    ]);

    $result = $this->service->getUpcomingDeadlines(7);

    expect($result)->toHaveCount(1);
    expect(Carbon::parse($result[0]['date'])->isFuture())->toBe(true);
    expect(Carbon::parse($result[0]['date'])->diffInDays(now()))->toBeLessThanOrEqual(7);
});

test('getCacheKey generates unique cache keys', function () {
    // Use reflection to test protected method
    $reflection = new ReflectionClass($this->service);
    $method = $reflection->getMethod('getCacheKey');
    $method->setAccessible(true);

    $key1 = $method->invoke($this->service, 'admin', 'stats');
    $key2 = $method->invoke($this->service, 'registrar', 'stats');
    $key3 = $method->invoke($this->service, 'admin', 'charts');

    expect($key1)->toBe('dashboard.admin.stats');
    expect($key2)->toBe('dashboard.registrar.stats');
    expect($key3)->toBe('dashboard.admin.charts');
    expect($key1)->not->toBe($key2);
    expect($key1)->not->toBe($key3);
});

test('logActivity is called for data retrieval', function () {
    Log::spy();

    Student::factory()->count(5)->create();

    $this->service->getAdminDashboardData();
    $this->service->getRegistrarDashboardData();
    $this->service->getQuickStats();

    Log::shouldHaveReceived('info')->times(3);
});

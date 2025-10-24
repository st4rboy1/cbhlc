<?php

namespace Tests\Feature\Registrar;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\PaymentStatus;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class EnrollmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $registrar;
    protected User $admin;
    protected Student $student;
    protected Guardian $guardianModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        // Create school year
        $this->sy2024 = \App\Models\SchoolYear::create([
            'name' => '2024-2025',
            'start_year' => 2024,
            'end_year' => 2025,
            'start_date' => '2024-06-01',
            'end_date' => '2025-05-31',
            'status' => 'active',
        ]);

        // Create registrar user
        $this->registrar = User::factory()->create();
        $this->registrar->assignRole('registrar');

        // Create admin user
        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');

        // Create student
        $this->student = Student::factory()->create();

        // Create guardian
        $guardianUser = User::factory()->create();
        $this->guardianModel = Guardian::create([
            'user_id' => $guardianUser->id,
            'first_name' => 'Test',
            'last_name' => 'Guardian',
            'contact_number' => '09123456789',
            'address' => '123 Test St',
        ]);
    }

    /** @test */
    public function registrar_can_view_enrollments_index()
    {
        Enrollment::factory()->count(5)->create();

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.enrollments.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('registrar/enrollments/index')
            ->has('enrollments')
            ->has('filters')
            ->has('statuses')
            ->has('paymentStatuses')
        );
    }

    /** @test */
    public function registrar_can_filter_enrollments_by_status()
    {
        Enrollment::factory()->create(['status' => EnrollmentStatus::PENDING]);
        Enrollment::factory()->create(['status' => EnrollmentStatus::ENROLLED]);
        Enrollment::factory()->create(['status' => EnrollmentStatus::REJECTED]);

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.enrollments.index', ['status' => EnrollmentStatus::PENDING->value]));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('registrar/enrollments/index')
            ->where('filters.status', EnrollmentStatus::PENDING->value)
        );
    }

    /** @test */
    public function registrar_can_filter_enrollments_by_school_year()
    {
        Enrollment::factory()->create(['school_year' => '2023-2024']);
        Enrollment::factory()->create(['school_year' => '2024-2025']);

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.enrollments.index', ['school_year' => '2024-2025']));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('filters.school_year', '2024-2025')
        );
    }

    /** @test */
    public function registrar_can_filter_enrollments_by_grade_level()
    {
        Enrollment::factory()->create(['grade_level' => GradeLevel::GRADE_1]);
        Enrollment::factory()->create(['grade_level' => GradeLevel::GRADE_2]);

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.enrollments.index', ['grade_level' => GradeLevel::GRADE_1->value]));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('filters.grade_level', GradeLevel::GRADE_1->value)
        );
    }

    /** @test */
    public function registrar_can_filter_enrollments_by_payment_status()
    {
        Enrollment::factory()->create(['payment_status' => PaymentStatus::PENDING]);
        Enrollment::factory()->create(['payment_status' => PaymentStatus::PAID]);

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.enrollments.index', ['payment_status' => PaymentStatus::PENDING->value]));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('filters.payment_status', PaymentStatus::PENDING->value)
        );
    }

    /** @test */
    public function registrar_can_search_enrollments()
    {
        $student = Student::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'student_id' => 'STU-001',
        ]);
        Enrollment::factory()->create(['student_id' => $student->id]);

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.enrollments.index', ['search' => 'John']));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('filters.search', 'John')
        );
    }

    /** @test */
    public function registrar_can_view_enrollment_details()
    {
        $enrollment = Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardianModel->user_id,
        ]);

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.enrollments.show', $enrollment));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('registrar/enrollments/show')
            ->where('enrollment.id', $enrollment->id)
            ->has('statuses')
            ->has('paymentStatuses')
        );
    }

    /** @test */
    public function registrar_can_approve_pending_enrollment()
    {
        $enrollment = Enrollment::factory()->create([
            'status' => EnrollmentStatus::PENDING,
        ]);

        $response = $this->actingAs($this->registrar)
            ->post(route('registrar.enrollments.approve', $enrollment), [
                'remarks' => 'Approved for enrollment',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $enrollment->refresh();
        $this->assertEquals(EnrollmentStatus::ENROLLED, $enrollment->status);
        $this->assertEquals($this->registrar->id, $enrollment->approved_by);
        $this->assertNotNull($enrollment->approved_at);
    }

    /** @test */
    public function registrar_cannot_approve_non_pending_enrollment()
    {
        $enrollment = Enrollment::factory()->create([
            'status' => EnrollmentStatus::ENROLLED,
        ]);

        $response = $this->actingAs($this->registrar)
            ->post(route('registrar.enrollments.approve', $enrollment));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function registrar_can_reject_pending_enrollment()
    {
        $enrollment = Enrollment::factory()->create([
            'status' => EnrollmentStatus::PENDING,
        ]);

        $response = $this->actingAs($this->registrar)
            ->post(route('registrar.enrollments.reject', $enrollment), [
                'reason' => 'Missing documents',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $enrollment->refresh();
        $this->assertEquals(EnrollmentStatus::REJECTED, $enrollment->status);
        $this->assertEquals('Missing documents', $enrollment->remarks);
    }

    /** @test */
    public function rejection_requires_reason()
    {
        $enrollment = Enrollment::factory()->create([
            'status' => EnrollmentStatus::PENDING,
        ]);

        $response = $this->actingAs($this->registrar)
            ->post(route('registrar.enrollments.reject', $enrollment));

        $response->assertSessionHasErrors(['reason']);
    }

    /** @test */
    public function registrar_can_update_payment_status()
    {
        $enrollment = Enrollment::factory()->create([
            'total_amount_cents' => 2500000,
            'amount_paid_cents' => 0,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($this->registrar)
            ->put(route('registrar.enrollments.update-payment-status', $enrollment), [
                'amount_paid' => 1000000, // 10,000 in cents
                'payment_status' => PaymentStatus::PARTIAL->value,
                'remarks' => 'First payment received',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $enrollment->refresh();
        $this->assertEquals(1000000, $enrollment->amount_paid_cents);
        $this->assertEquals(PaymentStatus::PARTIAL, $enrollment->payment_status);
        $this->assertEquals(1500000, $enrollment->balance_cents);
    }

    /** @test */
    public function registrar_can_mark_enrollment_as_completed()
    {
        $enrollment = Enrollment::factory()->create([
            'status' => EnrollmentStatus::ENROLLED,
            'payment_status' => PaymentStatus::PAID,
        ]);

        $response = $this->actingAs($this->registrar)
            ->post(route('registrar.enrollments.complete', $enrollment));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $enrollment->refresh();
        $this->assertEquals(EnrollmentStatus::COMPLETED, $enrollment->status);
    }

    /** @test */
    public function cannot_complete_enrollment_with_unpaid_fees()
    {
        $enrollment = Enrollment::factory()->create([
            'status' => EnrollmentStatus::ENROLLED,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($this->registrar)
            ->post(route('registrar.enrollments.complete', $enrollment));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $enrollment->refresh();
        $this->assertNotEquals(EnrollmentStatus::COMPLETED, $enrollment->status);
    }

    /** @test */
    public function registrar_can_bulk_approve_enrollments()
    {
        $enrollments = Enrollment::factory()->count(3)->create([
            'status' => EnrollmentStatus::PENDING,
        ]);

        $response = $this->actingAs($this->registrar)
            ->post(route('registrar.enrollments.bulk-approve'), [
                'enrollment_ids' => $enrollments->pluck('id')->toArray(),
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        foreach ($enrollments as $enrollment) {
            $enrollment->refresh();
            $this->assertEquals(EnrollmentStatus::ENROLLED, $enrollment->status);
        }
    }

    /** @test */
    public function bulk_approve_only_affects_pending_enrollments()
    {
        $pending = Enrollment::factory()->create(['status' => EnrollmentStatus::PENDING]);
        $enrolled = Enrollment::factory()->create(['status' => EnrollmentStatus::ENROLLED]);

        $response = $this->actingAs($this->registrar)
            ->post(route('registrar.enrollments.bulk-approve'), [
                'enrollment_ids' => [$pending->id, $enrolled->id],
            ]);

        $response->assertRedirect();

        $pending->refresh();
        $enrolled->refresh();

        $this->assertEquals(EnrollmentStatus::ENROLLED, $pending->status);
        $this->assertEquals(EnrollmentStatus::ENROLLED, $enrolled->status); // Unchanged
    }

    /** @test */
    public function admin_can_access_registrar_enrollment_functions()
    {
        $enrollment = Enrollment::factory()->create([
            'status' => EnrollmentStatus::PENDING,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('registrar.enrollments.approve', $enrollment));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** @test */
    public function export_returns_coming_soon_message()
    {
        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.enrollments.export'));

        $response->assertRedirect();
        $response->assertSessionHas('info', 'Export functionality coming soon.');
    }

    /** @test */
    public function registrar_cannot_reject_non_pending_enrollment()
    {
        $enrollment = Enrollment::factory()->create([
            'status' => EnrollmentStatus::ENROLLED,
        ]);

        $response = $this->actingAs($this->registrar)
            ->post(route('registrar.enrollments.reject', $enrollment), [
                'reason' => 'Test rejection',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Only pending enrollments can be rejected.');

        $enrollment->refresh();
        $this->assertEquals(EnrollmentStatus::ENROLLED, $enrollment->status);
    }

    /** @test */
    public function registrar_cannot_complete_non_enrolled_enrollment()
    {
        $enrollment = Enrollment::factory()->create([
            'status' => EnrollmentStatus::PENDING,
            'payment_status' => PaymentStatus::PAID,
        ]);

        $response = $this->actingAs($this->registrar)
            ->post(route('registrar.enrollments.complete', $enrollment));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Only enrolled students can be marked as completed.');

        $enrollment->refresh();
        $this->assertEquals(EnrollmentStatus::PENDING, $enrollment->status);
    }

    /** @test */
    public function approve_enrollment_stores_remarks()
    {
        $enrollment = Enrollment::factory()->create([
            'status' => EnrollmentStatus::PENDING,
        ]);

        $response = $this->actingAs($this->registrar)
            ->post(route('registrar.enrollments.approve', $enrollment), [
                'remarks' => 'Documents verified, all requirements complete',
            ]);

        $response->assertRedirect();

        $enrollment->refresh();
        $this->assertEquals('Documents verified, all requirements complete', $enrollment->remarks);
    }

    /** @test */
    public function reject_enrollment_stores_reason_as_remarks()
    {
        $enrollment = Enrollment::factory()->create([
            'status' => EnrollmentStatus::PENDING,
        ]);

        $response = $this->actingAs($this->registrar)
            ->post(route('registrar.enrollments.reject', $enrollment), [
                'reason' => 'Incomplete documentation - missing Form 138',
            ]);

        $response->assertRedirect();

        $enrollment->refresh();
        $this->assertEquals('Incomplete documentation - missing Form 138', $enrollment->remarks);
        $this->assertEquals($this->registrar->id, $enrollment->approved_by);
        $this->assertNotNull($enrollment->approved_at);
    }

    /** @test */
    public function update_payment_status_calculates_balance_correctly()
    {
        $enrollment = Enrollment::factory()->create([
            'total_amount_cents' => 3000000, // 30,000 pesos
            'amount_paid_cents' => 0,
            'balance_cents' => 3000000,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($this->registrar)
            ->put(route('registrar.enrollments.update-payment-status', $enrollment), [
                'amount_paid' => 2000000, // 20,000 pesos
                'payment_status' => PaymentStatus::PARTIAL->value,
            ]);

        $response->assertRedirect();

        $enrollment->refresh();
        $this->assertEquals(2000000, $enrollment->amount_paid_cents);
        $this->assertEquals(1000000, $enrollment->balance_cents); // 10,000 balance
    }

    /** @test */
    public function update_payment_status_validates_amount()
    {
        $enrollment = Enrollment::factory()->create([
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($this->registrar)
            ->put(route('registrar.enrollments.update-payment-status', $enrollment), [
                'amount_paid' => -100, // Invalid negative amount
                'payment_status' => PaymentStatus::PARTIAL->value,
            ]);

        $response->assertSessionHasErrors(['amount_paid']);
    }

    /** @test */
    public function update_payment_status_validates_payment_status()
    {
        $enrollment = Enrollment::factory()->create([
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($this->registrar)
            ->put(route('registrar.enrollments.update-payment-status', $enrollment), [
                'amount_paid' => 100000,
                'payment_status' => 'invalid_status',
            ]);

        $response->assertSessionHasErrors(['payment_status']);
    }

    /** @test */
    public function bulk_approve_validates_enrollment_ids()
    {
        $response = $this->actingAs($this->registrar)
            ->post(route('registrar.enrollments.bulk-approve'), []);

        $response->assertSessionHasErrors(['enrollment_ids']);
    }

    /** @test */
    public function bulk_approve_validates_enrollment_exists()
    {
        $response = $this->actingAs($this->registrar)
            ->post(route('registrar.enrollments.bulk-approve'), [
                'enrollment_ids' => [99999], // Non-existent ID
            ]);

        $response->assertSessionHasErrors(['enrollment_ids.0']);
    }

    /** @test */
    public function enrollments_index_paginates_to_20_per_page()
    {
        Enrollment::factory()->count(25)->create();

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.enrollments.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('registrar/enrollments/index')
            ->has('enrollments.data', 20)
            ->has('enrollments.links')
        );
    }

    /** @test */
    public function search_enrollments_by_last_name()
    {
        $student = Student::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);
        Enrollment::factory()->create(['student_id' => $student->id]);

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.enrollments.index', ['search' => 'Smith']));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('filters.search', 'Smith')
        );
    }

    /** @test */
    public function search_enrollments_by_student_id()
    {
        $student = Student::factory()->create([
            'student_id' => 'CBHLC2024001',
        ]);
        Enrollment::factory()->create(['student_id' => $student->id]);

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.enrollments.index', ['search' => 'CBHLC2024001']));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('filters.search', 'CBHLC2024001')
        );
    }

    /** @test */
    public function enrollments_sorted_by_latest_created()
    {
        $old = Enrollment::factory()->create(['created_at' => now()->subDays(5)]);
        $new = Enrollment::factory()->create(['created_at' => now()]);
        $middle = Enrollment::factory()->create(['created_at' => now()->subDays(2)]);

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.enrollments.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('enrollments.data.0.id', $new->id)
            ->where('enrollments.data.1.id', $middle->id)
            ->where('enrollments.data.2.id', $old->id)
        );
    }

    /** @test */
    public function guardian_cannot_access_registrar_enrollments()
    {
        $guardian = User::factory()->create();
        $guardian->assignRole('guardian');

        $response = $this->actingAs($guardian)
            ->get(route('registrar.enrollments.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function student_cannot_access_registrar_enrollments()
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $response = $this->actingAs($student)
            ->get(route('registrar.enrollments.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function super_admin_can_access_registrar_enrollment_functions()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $enrollment = Enrollment::factory()->create([
            'status' => EnrollmentStatus::PENDING,
        ]);

        $response = $this->actingAs($superAdmin)
            ->post(route('registrar.enrollments.approve', $enrollment));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** @test */
    public function update_payment_status_preserves_existing_remarks_when_none_provided()
    {
        $enrollment = Enrollment::factory()->create([
            'total_amount_cents' => 2000000,
            'payment_status' => PaymentStatus::PENDING,
            'remarks' => 'Original remarks',
        ]);

        $response = $this->actingAs($this->registrar)
            ->put(route('registrar.enrollments.update-payment-status', $enrollment), [
                'amount_paid' => 1000000,
                'payment_status' => PaymentStatus::PARTIAL->value,
            ]);

        $enrollment->refresh();
        $this->assertEquals('Original remarks', $enrollment->remarks);
    }

    /** @test */
    public function update_payment_status_updates_remarks_when_provided()
    {
        $enrollment = Enrollment::factory()->create([
            'total_amount_cents' => 2000000,
            'payment_status' => PaymentStatus::PENDING,
            'remarks' => 'Original remarks',
        ]);

        $response = $this->actingAs($this->registrar)
            ->put(route('registrar.enrollments.update-payment-status', $enrollment), [
                'amount_paid' => 1000000,
                'payment_status' => PaymentStatus::PARTIAL->value,
                'remarks' => 'Payment received via bank transfer',
            ]);

        $enrollment->refresh();
        $this->assertEquals('Payment received via bank transfer', $enrollment->remarks);
    }

    /** @test */
    public function rejection_reason_cannot_exceed_500_characters()
    {
        $enrollment = Enrollment::factory()->create([
            'status' => EnrollmentStatus::PENDING,
        ]);

        $response = $this->actingAs($this->registrar)
            ->post(route('registrar.enrollments.reject', $enrollment), [
                'reason' => str_repeat('a', 501), // 501 characters
            ]);

        $response->assertSessionHasErrors(['reason']);
    }

    /** @test */
    public function can_filter_by_multiple_criteria_simultaneously()
    {
        $student = Student::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        Enrollment::factory()->create([
            'student_id' => $student->id,
            'status' => EnrollmentStatus::PENDING,
            'school_year_id' => $this->sy2024->id,
            'grade_level' => GradeLevel::GRADE_1,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.enrollments.index', [
                'status' => EnrollmentStatus::PENDING->value,
                'school_year_id' => $this->sy2024->id,
                'grade_level' => GradeLevel::GRADE_1->value,
                'payment_status' => PaymentStatus::PENDING->value,
                'search' => 'John',
            ]));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('filters.status', EnrollmentStatus::PENDING->value)
            ->where('filters.school_year', '2024-2025')
            ->where('filters.grade_level', GradeLevel::GRADE_1->value)
            ->where('filters.payment_status', PaymentStatus::PENDING->value)
            ->where('filters.search', 'John')
        );
    }
}

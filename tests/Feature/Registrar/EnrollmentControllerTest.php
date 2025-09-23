<?php

namespace Tests\Feature\Registrar;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\PaymentStatus;
use App\Enums\Quarter;
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
}
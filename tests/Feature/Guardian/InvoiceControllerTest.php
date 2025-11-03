<?php

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\InvoiceStatus;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\SchoolYear;
use App\Models\Setting;
use App\Models\Student;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles and permissions
    $this->seed(RolesAndPermissionsSeeder::class);

    // Create guardian user and associated Guardian model
    $guardianModel = Guardian::factory()->create();
    $this->guardian = $guardianModel->user;
    $this->guardian->assignRole('guardian');
    $this->guardianModel = $guardianModel;

    // Create school year
    $this->schoolYear = SchoolYear::factory()->create([
        'name' => '2024-2025',
        'start_date' => now()->subMonths(2),
        'end_date' => now()->addMonths(10),
    ]);

    // Create student
    $this->student = Student::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'grade_level' => GradeLevel::GRADE_1,
    ]);
    $this->guardianModel->children()->attach($this->student->id);

    // Create enrollment
    $this->enrollment = Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'school_year_id' => $this->schoolYear->id,
        'grade_level' => GradeLevel::GRADE_1,
        'status' => EnrollmentStatus::ENROLLED,
    ]);

    // Create settings
    Setting::create(['key' => 'school_name', 'value' => 'Christian Bible Heritage Learning Center']);
    Setting::create(['key' => 'school_address', 'value' => '123 Main St']);
    Setting::create(['key' => 'school_contact', 'value' => '555-1234']);
});

test('guardian can view invoices list', function () {
    $response = $this->actingAs($this->guardian)
        ->get(route('guardian.invoices.index'));

    $response->assertStatus(200)
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/invoices/index')
            ->has('invoices')
        );
});

test('invoices list shows guardian enrollments', function () {
    // Create invoice for the enrollment
    Invoice::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'invoice_number' => 'INV-2024-001',
        'total_amount' => 25000.00,
        'status' => InvoiceStatus::SENT,
    ]);

    $response = $this->actingAs($this->guardian)
        ->get(route('guardian.invoices.index'));

    $response->assertStatus(200)
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/invoices/index')
            ->has('invoices.data', 1)
        );
});

test('guardian can view specific invoice', function () {
    // Create invoice for the enrollment
    $invoice = Invoice::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'invoice_number' => 'INV-2024-001',
        'total_amount' => 25000.00,
        'status' => InvoiceStatus::SENT,
    ]);

    // Create invoice items
    InvoiceItem::factory()->create([
        'invoice_id' => $invoice->id,
        'description' => 'Tuition Fee',
        'amount' => 20000.00,
    ]);

    InvoiceItem::factory()->create([
        'invoice_id' => $invoice->id,
        'description' => 'Miscellaneous Fee',
        'amount' => 5000.00,
    ]);

    $response = $this->actingAs($this->guardian)
        ->get(route('guardian.invoices.show', $invoice));

    $response->assertStatus(200)
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('shared/invoice')
            ->has('invoice')
            ->where('invoiceNumber', 'INV-2024-001')
            ->has('settings')
        );
});

test('invoice shows correct total amount', function () {
    $invoice = Invoice::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'invoice_number' => 'INV-2024-001',
        'total_amount' => 30000.00,
        'status' => InvoiceStatus::SENT,
    ]);

    InvoiceItem::factory()->create([
        'invoice_id' => $invoice->id,
        'description' => 'Tuition Fee',
        'amount' => 25000.00,
    ]);

    InvoiceItem::factory()->create([
        'invoice_id' => $invoice->id,
        'description' => 'Books',
        'amount' => 5000.00,
    ]);

    $response = $this->actingAs($this->guardian)
        ->get(route('guardian.invoices.show', $invoice));

    $response->assertStatus(200)
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('invoice.total_amount', '30000.00')
        );
});

test('guardian cannot view invoice for other guardian student', function () {
    // Create another guardian and their student
    $otherGuardian = Guardian::factory()->create();
    $otherStudent = Student::factory()->create();
    $otherGuardian->children()->attach($otherStudent->id);

    // Create enrollment and invoice for other student
    $otherEnrollment = Enrollment::factory()->create([
        'student_id' => $otherStudent->id,
        'school_year_id' => $this->schoolYear->id,
    ]);

    $otherInvoice = Invoice::factory()->create([
        'enrollment_id' => $otherEnrollment->id,
        'invoice_number' => 'INV-2024-999',
    ]);

    $response = $this->actingAs($this->guardian)
        ->get(route('guardian.invoices.show', $otherInvoice));

    $response->assertStatus(404);
});

test('guardian can download invoice PDF', function () {
    $invoice = Invoice::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'invoice_number' => 'INV-2024-001',
        'total_amount' => 25000.00,
        'status' => InvoiceStatus::SENT,
    ]);

    InvoiceItem::factory()->create([
        'invoice_id' => $invoice->id,
        'description' => 'Tuition Fee',
        'amount' => 25000.00,
    ]);

    $response = $this->actingAs($this->guardian)
        ->get(route('guardian.invoices.download', $invoice));

    $response->assertStatus(200)
        ->assertHeader('content-type', 'application/pdf')
        ->assertDownload("invoice-{$invoice->invoice_number}.pdf");
});

test('guardian cannot download invoice PDF for other guardian student', function () {
    // Create another guardian and their student
    $otherGuardian = Guardian::factory()->create();
    $otherStudent = Student::factory()->create();
    $otherGuardian->children()->attach($otherStudent->id);

    // Create enrollment and invoice for other student
    $otherEnrollment = Enrollment::factory()->create([
        'student_id' => $otherStudent->id,
        'school_year_id' => $this->schoolYear->id,
    ]);

    $otherInvoice = Invoice::factory()->create([
        'enrollment_id' => $otherEnrollment->id,
        'invoice_number' => 'INV-2024-999',
    ]);

    $response = $this->actingAs($this->guardian)
        ->get(route('guardian.invoices.download', $otherInvoice));

    $response->assertStatus(404);
});

test('invoice shows payments when available', function () {
    $invoice = Invoice::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'invoice_number' => 'INV-2024-001',
        'total_amount' => 25000.00,
        'paid_amount' => 10000.00,
        'status' => InvoiceStatus::PARTIALLY_PAID,
    ]);

    // Create payment
    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 10000.00,
        'payment_date' => now(),
    ]);

    $response = $this->actingAs($this->guardian)
        ->get(route('guardian.invoices.show', $invoice));

    $response->assertStatus(200)
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('invoice.paid_amount', '10000.00')
            ->where('invoice.status', InvoiceStatus::PARTIALLY_PAID->value)
        );
});

test('invoices list only shows guardian own students', function () {
    // Create invoice for this guardian's enrollment
    Invoice::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'invoice_number' => 'INV-2024-001',
        'total_amount' => 25000.00,
        'status' => InvoiceStatus::SENT,
    ]);

    // Create another guardian with their student and enrollment
    $otherGuardian = Guardian::factory()->create();
    $otherStudent = Student::factory()->create(['first_name' => 'Other']);
    $otherGuardian->children()->attach($otherStudent->id);

    $otherEnrollment = Enrollment::factory()->create([
        'student_id' => $otherStudent->id,
        'school_year_id' => $this->schoolYear->id,
    ]);

    // Create invoice for other guardian's enrollment
    Invoice::factory()->create([
        'enrollment_id' => $otherEnrollment->id,
        'invoice_number' => 'INV-2024-002',
        'total_amount' => 30000.00,
        'status' => InvoiceStatus::SENT,
    ]);

    $response = $this->actingAs($this->guardian)
        ->get(route('guardian.invoices.index'));

    $response->assertStatus(200)
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('invoices.data', 1)
            ->where('invoices.data.0.enrollment.student.first_name', 'John')
        );
});

test('unauthenticated user cannot access invoices', function () {
    $response = $this->get(route('guardian.invoices.index'));

    $response->assertStatus(302)
        ->assertRedirect(route('login'));
});

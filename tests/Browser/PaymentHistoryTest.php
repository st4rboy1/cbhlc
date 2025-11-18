<?php

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentMethod;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\GuardianStudent;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

describe('Payment History Display', function () {

    test('payment history shows correct amount for single payment', function () {
        // Create guardian user
        $guardianUser = User::factory()->guardian()->create([
            'email' => 'parent@test.com',
            'password' => bcrypt('password'),
        ]);

        $guardian = Guardian::factory()->create([
            'user_id' => $guardianUser->id,
        ]);

        // Create enrollment
        $enrollment = Enrollment::factory()->create([
            'guardian_id' => $guardian->id,
            'status' => EnrollmentStatus::ENROLLED,
            'type' => 'new',
            'payment_plan' => 'annual',
        ]);

        // Link guardian to student
        GuardianStudent::create([
            'guardian_id' => $guardian->id,
            'student_id' => $enrollment->student_id,
            'relationship_type' => 'parent',
            'is_primary' => true,
        ]);

        // Create invoice and payment
        $invoice = Invoice::factory()->create([
            'enrollment_id' => $enrollment->id,
            'status' => 'sent',
        ]);

        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 5000.00, // ₱5,000.00 (stored as pesos in decimal)
            'payment_method' => PaymentMethod::CASH,
            'reference_number' => 'PAY-TEST-001',
        ]);

        // Test backend directly
        $this->actingAs($guardianUser);
        $response = $this->get("/guardian/enrollments/{$enrollment->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('guardian/enrollments/show')
            ->where('enrollment.id', $enrollment->id)
            ->has('payments', 1)
            ->where('payments.0.amount', 500000) // Should be in cents (5000 * 100)
            ->where('payments.0.payment_method', 'cash')
            ->where('payments.0.reference_number', 'PAY-TEST-001')
        );

        // Test UI display
        visit("/guardian/enrollments/{$enrollment->id}")
            ->waitForText('Payment History')
            ->assertSee('₱5,000.00')
            ->assertDontSee('₱0.00')
            ->assertSee('Cash')
            ->assertSee('PAY-TEST-001');

    })->group('payment-history', 'critical');

    test('payment history shows correct amounts for multiple payments', function () {
        $guardianUser = User::factory()->guardian()->create([
            'email' => 'parent@test.com',
            'password' => bcrypt('password'),
        ]);

        $guardian = Guardian::factory()->create([
            'user_id' => $guardianUser->id,
        ]);

        $enrollment = Enrollment::factory()->create([
            'guardian_id' => $guardian->id,
            'status' => EnrollmentStatus::ENROLLED,
            'type' => 'new',
            'payment_plan' => 'annual',
        ]);

        GuardianStudent::create([
            'guardian_id' => $guardian->id,
            'student_id' => $enrollment->student_id,
            'relationship_type' => 'parent',
            'is_primary' => true,
        ]);

        $invoice = Invoice::factory()->create([
            'enrollment_id' => $enrollment->id,
            'status' => 'sent',
        ]);

        // Create multiple payments with explicit dates
        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 2500.00, // ₱2,500.00 (stored as pesos)
            'payment_method' => PaymentMethod::CASH,
            'reference_number' => 'PAY-001',
            'payment_date' => now()->subDays(2),
        ]);

        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 1500.00, // ₱1,500.00 (stored as pesos)
            'payment_method' => PaymentMethod::BANK_TRANSFER,
            'reference_number' => 'PAY-002',
            'payment_date' => now()->subDay(),
        ]);

        $this->actingAs($guardianUser);
        $response = $this->get("/guardian/enrollments/{$enrollment->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('payments', 2)
            ->where('payments.0.amount', 150000) // Latest first (in cents: 1500 * 100)
            ->where('payments.1.amount', 250000) // In cents: 2500 * 100
        );

        visit("/guardian/enrollments/{$enrollment->id}")
            ->waitForText('Payment History')
            ->assertSee('₱2,500.00')
            ->assertSee('₱1,500.00')
            ->assertDontSee('₱0.00');

    })->group('payment-history', 'critical');

    test('enrollment without payments does not show payment history section', function () {
        $guardianUser = User::factory()->guardian()->create([
            'email' => 'parent@test.com',
            'password' => bcrypt('password'),
        ]);

        $guardian = Guardian::factory()->create([
            'user_id' => $guardianUser->id,
        ]);

        $enrollment = Enrollment::factory()->create([
            'guardian_id' => $guardian->id,
            'status' => EnrollmentStatus::PENDING,
            'type' => 'new',
            'payment_plan' => 'annual',
        ]);

        GuardianStudent::create([
            'guardian_id' => $guardian->id,
            'student_id' => $enrollment->student_id,
            'relationship_type' => 'parent',
            'is_primary' => true,
        ]);

        $this->actingAs($guardianUser);

        visit("/guardian/enrollments/{$enrollment->id}")
            ->waitForText('Enrollment Details')
            ->assertDontSee('Payment History');

    })->group('payment-history');
});

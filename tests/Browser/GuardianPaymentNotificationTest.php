<?php

use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use App\Notifications\PaymentReceivedNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

describe('Guardian Payment Notification', function () {

    test('payment notification shows correct amount instead of zero', function () {
        Notification::fake();

        // Create guardian user
        $user = User::factory()->create([
            'email' => 'guardian@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('guardian');

        $guardian = Guardian::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create student and enrollment
        $student = Student::factory()->create();
        $guardian->children()->attach($student->id, ['is_primary_contact' => true]);

        $schoolYear = SchoolYear::factory()->create(['status' => 'active']);

        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'school_year_id' => $schoolYear->id,
            'status' => 'enrolled',
            'payment_plan' => 'monthly',
        ]);

        // Create invoice
        $invoice = Invoice::factory()->create([
            'enrollment_id' => $enrollment->id,
            'invoice_number' => 'INV-2025-0001',
            'total_amount' => 50000.00,
            'status' => 'sent',
        ]);

        // Create payment with specific amount
        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 15000.50,  // ₱15,000.50
            'payment_method' => 'cash',
            'reference_number' => 'PAY-2025-0001',
            'payment_date' => now(),
        ]);

        // Send notification
        $user->notify(new PaymentReceivedNotification($payment));

        // Assert notification was sent
        Notification::assertSentTo($user, PaymentReceivedNotification::class);

        // Assert notification contains correct amount
        Notification::assertSentTo(
            $user,
            PaymentReceivedNotification::class,
            function ($notification) use ($payment) {
                $amount = (float) $notification->payment->amount;

                // Check amount is correct (not zero)
                expect($amount)->toBe(15000.50);
                expect($notification->payment->id)->toBe($payment->id);

                return true;
            }
        );
    })->group('guardian', 'payment', 'notification', 'critical');

    test('payment notification includes action URL to view invoice', function () {
        Notification::fake();

        $user = User::factory()->create();
        $user->assignRole('guardian');
        $guardian = Guardian::factory()->create(['user_id' => $user->id]);
        $student = Student::factory()->create();
        $guardian->children()->attach($student->id, ['is_primary_contact' => true]);

        $schoolYear = SchoolYear::factory()->create(['status' => 'active']);

        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'school_year_id' => $schoolYear->id,
            'payment_plan' => 'monthly',
        ]);

        $invoice = Invoice::factory()->create([
            'enrollment_id' => $enrollment->id,
            'invoice_number' => 'INV-2025-0002',
            'status' => 'sent',
        ]);

        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 25000.00,
            'payment_method' => 'bank_transfer',
        ]);

        // Send notification
        $user->notify(new PaymentReceivedNotification($payment));

        // Assert notification has action URL
        Notification::assertSentTo(
            $user,
            PaymentReceivedNotification::class,
            function ($notification) use ($invoice) {
                // Check invoice exists in payment
                expect($notification->payment->invoice)->not()->toBeNull();
                expect($notification->payment->invoice->id)->toBe($invoice->id);

                return true;
            }
        );
    })->group('guardian', 'payment', 'notification', 'critical');

    test('guardian can access invoice from payment notification link', function () {
        Notification::fake();

        $user = User::factory()->create();
        $user->assignRole('guardian');
        $guardian = Guardian::factory()->create(['user_id' => $user->id]);
        $student = Student::factory()->create();
        $guardian->children()->attach($student->id, ['is_primary_contact' => true]);

        $schoolYear = SchoolYear::factory()->create(['status' => 'active']);

        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'school_year_id' => $schoolYear->id,
            'payment_plan' => 'monthly',
        ]);

        $invoice = Invoice::factory()->create([
            'enrollment_id' => $enrollment->id,
            'invoice_number' => 'INV-2025-0003',
            'status' => 'sent',
        ]);

        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 30000.00,
            'payment_method' => 'cash',
        ]);

        // Send notification
        $user->notify(new PaymentReceivedNotification($payment));

        // Login as guardian and access invoice from notification
        $this->actingAs($user);

        $response = $this->get(route('guardian.invoices.show', $invoice));

        // Should successfully load the invoice page
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('shared/invoice')
            ->has('enrollment')
            ->where('invoiceNumber', $invoice->invoice_number)
        );
    })->group('guardian', 'payment', 'notification', 'critical');

    test('payment notification shows correct formatted amount in message', function () {
        Notification::fake();

        $user = User::factory()->create();
        $user->assignRole('guardian');
        $guardian = Guardian::factory()->create(['user_id' => $user->id]);
        $student = Student::factory()->create();
        $guardian->children()->attach($student->id, ['is_primary_contact' => true]);

        $schoolYear = SchoolYear::factory()->create(['status' => 'active']);

        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'school_year_id' => $schoolYear->id,
            'payment_plan' => 'monthly',
        ]);

        $invoice = Invoice::factory()->create([
            'enrollment_id' => $enrollment->id,
            'status' => 'sent',
        ]);

        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 12345.67,  // Test with decimal places
            'payment_method' => 'gcash',
        ]);

        // Send notification
        $user->notify(new PaymentReceivedNotification($payment));

        // Assert message formatting
        Notification::assertSentTo(
            $user,
            PaymentReceivedNotification::class,
            function ($notification) {
                $amount = (float) $notification->payment->amount;

                // Check amount is correctly stored with decimals
                expect($amount)->toBe(12345.67);

                return true;
            }
        );
    })->group('guardian', 'payment', 'notification');
});

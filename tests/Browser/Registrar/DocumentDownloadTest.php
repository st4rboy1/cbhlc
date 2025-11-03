<?php

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    // Create registrar user
    $this->registrar = User::factory()->create();
    $this->registrar->assignRole('registrar');

    // Create enrolled student with complete data
    $guardian = Guardian::factory()->create();
    $this->student = Student::factory()->create();

    $this->enrollment = Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'guardian_id' => $guardian->id,
        'status' => EnrollmentStatus::ENROLLED,
    ]);

    // Create invoice for the enrollment
    $this->invoice = Invoice::factory()->create([
        'enrollment_id' => $this->enrollment->id,
    ]);

    // Create payment with receipt
    $this->payment = Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
        'receipt_number' => 'OR-202501-0001',
    ]);
});

describe('Registrar Document Downloads', function () {
    test('registrar can download enrollment certificate', function () {
        browse(function ($browser) {
            $browser->loginAs($this->registrar)
                ->visit("/registrar/enrollments/{$this->enrollment->id}")
                ->waitForText($this->student->full_name)
                ->assertSee('Download Certificate')
                ->click('@download-certificate')
                ->pause(2000); // Allow download to initiate

            // Verify the download URL is correct
            $expectedUrl = route('registrar.enrollments.certificate', $this->enrollment->id);
            $browser->assertUrlIs($expectedUrl);
        });
    })->group('registrar-downloads', 'browser', 'issue-553');

    test('registrar can download invoice PDF', function () {
        browse(function ($browser) {
            $browser->loginAs($this->registrar)
                ->visit("/registrar/invoices/{$this->invoice->id}")
                ->waitForText('Invoice')
                ->assertSee('Download PDF')
                ->click('@download-invoice')
                ->pause(2000);

            $expectedUrl = route('registrar.invoices.download', $this->invoice->id);
            $browser->assertUrlIs($expectedUrl);
        });
    })->group('registrar-downloads', 'browser', 'issue-553');

    test('registrar can download payment history', function () {
        browse(function ($browser) {
            $browser->loginAs($this->registrar)
                ->visit("/registrar/enrollments/{$this->enrollment->id}")
                ->waitForText($this->student->full_name)
                ->assertSee('Payment History')
                ->click('@download-payment-history')
                ->pause(2000);

            $expectedUrl = route('registrar.enrollments.payment-history', $this->enrollment->id);
            $browser->assertUrlIs($expectedUrl);
        });
    })->group('registrar-downloads', 'browser', 'issue-553');

    test('registrar can download payment receipt', function () {
        browse(function ($browser) {
            $browser->loginAs($this->registrar)
                ->visit("/registrar/payments/{$this->payment->id}")
                ->waitForText('Receipt')
                ->assertSee('Download Receipt')
                ->click('@download-receipt')
                ->pause(2000);

            $expectedUrl = route('payments.receipt', $this->payment->id);
            $browser->assertUrlIs($expectedUrl);
        });
    })->group('registrar-downloads', 'browser', 'issue-553');

    test('registrar can access all downloads from student details page', function () {
        browse(function ($browser) {
            $browser->loginAs($this->registrar)
                ->visit("/registrar/students/{$this->student->id}")
                ->waitForText($this->student->full_name)

                // Verify all download buttons are present
                ->assertSee('Download Certificate')
                ->assertSee('Download Invoice')
                ->assertSee('Download Payment History')
                ->assertSee('Download Receipt')

                // Test certificate download
                ->click('@download-certificate')
                ->pause(1000)
                ->back()

                // Test invoice download
                ->click('@download-invoice')
                ->pause(1000)
                ->back()

                // Test payment history download
                ->click('@download-payment-history')
                ->pause(1000)
                ->back()

                // Test receipt download
                ->click('@download-receipt')
                ->pause(1000);
        });
    })->group('registrar-downloads', 'browser', 'issue-553', 'critical');
});

describe('Use Case: Parent at School Office', function () {
    test('registrar helps parent who cannot access their account', function () {
        // Simulate real-world scenario from issue #553
        browse(function ($browser) {
            $browser->loginAs($this->registrar)
                // Parent approaches registrar desk
                ->visit('/registrar/students')
                ->waitForText('Students')

                // Registrar searches for student
                ->type('@search-input', $this->student->last_name)
                ->press('Search')
                ->waitForText($this->student->full_name)

                // Opens student record
                ->click('@student-row-'.$this->student->id)
                ->waitForText('Student Details')

                // Downloads invoice for parent
                ->click('@download-invoice')
                ->pause(2000)
                ->back()

                // Downloads certificate for parent
                ->click('@download-certificate')
                ->pause(2000)

                // Verify both downloads completed successfully
                ->assertSee('Downloaded successfully');

            // This should take ~2 minutes as described in the issue
        });
    })->group('registrar-downloads', 'browser', 'issue-553', 'use-case');
});

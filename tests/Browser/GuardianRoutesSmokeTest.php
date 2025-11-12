<?php

use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    // Create guardian user with student
    $this->guardianUser = User::factory()->guardian()->create([
        'email' => 'guardian@test.com',
        'password' => bcrypt('password'),
    ]);

    $this->guardian = Guardian::factory()->create([
        'user_id' => $this->guardianUser->id,
    ]);

    $this->student = Student::factory()->create();

    $this->student->guardians()->attach($this->guardian->id);

    $this->enrollment = Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'guardian_id' => $this->guardian->id,
    ]);

    $this->invoice = Invoice::factory()->create([
        'enrollment_id' => $this->enrollment->id,
    ]);

    $this->payment = Payment::factory()->create([
        'invoice_id' => $this->invoice->id,
    ]);

    $this->receipt = Receipt::factory()->create([
        'payment_id' => $this->payment->id,
    ]);

    $this->actingAs($this->guardianUser);
});

describe('Guardian Routes Smoke Tests', function () {
    test('can access guardian dashboard', function () {
        $browser = visit('/guardian/dashboard')
            ->waitForText('Dashboard')
            ->assertSee('Dashboard');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'guardian');

    test('can access guardian enrollments index', function () {
        $browser = visit('/guardian/enrollments')
            ->waitForText('Enrollments')
            ->assertSee('Enrollments');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'guardian');

    test('can access guardian enrollments create', function () {
        $browser = visit('/guardian/enrollments/create')
            ->waitForText('New Enrollment')
            ->assertSee('New Enrollment');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'guardian');

    test('can access guardian enrollment show', function () {
        $browser = visit("/guardian/enrollments/{$this->enrollment->id}")
            ->waitForText('Enrollment')
            ->assertSee('Enrollment');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'guardian');

    test('can access guardian enrollment edit', function () {
        $browser = visit("/guardian/enrollments/{$this->enrollment->id}/edit")
            ->waitForText('Edit Enrollment')
            ->assertSee('Edit Enrollment');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'guardian');

    test('can access guardian students index', function () {
        $browser = visit('/guardian/students')
            ->waitForText('Students')
            ->assertSee('Students');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'guardian');

    test('can access guardian students create', function () {
        $browser = visit('/guardian/students/create')
            ->waitForText('Add New Student')
            ->assertSee('Add New Student');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'guardian');

    test('can access guardian student show', function () {
        $browser = visit("/guardian/students/{$this->student->id}")
            ->waitForText('Student')
            ->assertSee($this->student->first_name);

        assertNoConsoleErrors($browser);
    })->group('smoke', 'guardian');

    test('can access guardian student edit', function () {
        $browser = visit("/guardian/students/{$this->student->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'guardian');

    test('can access guardian billing index', function () {
        $browser = visit('/guardian/billing')
            ->waitForText('Billing')
            ->assertSee('Billing');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'guardian');

    test('can access guardian billing for enrollment', function () {
        $browser = visit("/guardian/billing/{$this->enrollment->id}")
            ->waitForText('Billing')
            ->assertSee('Billing');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'guardian');

    test('can access guardian invoices index', function () {
        $browser = visit('/guardian/invoices')
            ->waitForText('Invoices')
            ->assertSee('Invoices');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'guardian');

    test('can access guardian invoice show', function () {
        $browser = visit("/guardian/invoices/{$this->invoice->id}")
            ->waitForText('Invoice')
            ->assertSee('Invoice');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'guardian');

    test('can access guardian payments index', function () {
        $browser = visit('/guardian/payments')
            ->waitForText('Payments')
            ->assertSee('Payments');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'guardian');

    test('can access guardian receipts index', function () {
        $browser = visit('/guardian/receipts')
            ->waitForText('Receipts')
            ->assertSee('Receipts');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'guardian');

    test('can access guardian receipt show', function () {
        $browser = visit("/guardian/receipts/{$this->receipt->id}")
            ->waitForText('Receipt')
            ->assertSee('Receipt');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'guardian');

    test('can access settings profile', function () {
        $browser = visit('/settings/profile')
            ->waitForText('Profile')
            ->assertSee('Profile');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'guardian');

    test('can access settings password', function () {
        $browser = visit('/settings/password')
            ->waitForText('Password')
            ->assertSee('Password');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'guardian');

    test('can access settings appearance', function () {
        $browser = visit('/settings/appearance')
            ->waitForText('Appearance')
            ->assertSee('Appearance');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'guardian');

    test('can access settings notifications', function () {
        $browser = visit('/settings/notifications')
            ->waitForText('Notifications')
            ->assertSee('Notifications');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'guardian');

    test('can access notifications page', function () {
        $browser = visit('/notifications')
            ->waitForText('Notifications')
            ->assertSee('Notifications');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'guardian');
});

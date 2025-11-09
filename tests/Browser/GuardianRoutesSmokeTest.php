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
        visit('/guardian/dashboard')
            ->waitForText('Dashboard')
            ->assertSee('Dashboard');
    })->group('smoke', 'guardian');

    test('can access guardian enrollments index', function () {
        visit('/guardian/enrollments')
            ->waitForText('Enrollments')
            ->assertSee('Enrollments');
    })->group('smoke', 'guardian');

    test('can access guardian enrollments create', function () {
        visit('/guardian/enrollments/create')
            ->waitForText('Create Enrollment')
            ->assertSee('Create');
    })->group('smoke', 'guardian');

    test('can access guardian enrollment show', function () {
        visit("/guardian/enrollments/{$this->enrollment->id}")
            ->waitForText('Enrollment')
            ->assertSee('Enrollment');
    })->group('smoke', 'guardian');

    test('can access guardian enrollment edit', function () {
        visit("/guardian/enrollments/{$this->enrollment->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');
    })->group('smoke', 'guardian');

    test('can access guardian students index', function () {
        visit('/guardian/students')
            ->waitForText('Students')
            ->assertSee('Students');
    })->group('smoke', 'guardian');

    test('can access guardian students create', function () {
        visit('/guardian/students/create')
            ->waitForText('Create Student')
            ->assertSee('Create');
    })->group('smoke', 'guardian');

    test('can access guardian student show', function () {
        visit("/guardian/students/{$this->student->id}")
            ->waitForText('Student')
            ->assertSee($this->student->first_name);
    })->group('smoke', 'guardian');

    test('can access guardian student edit', function () {
        visit("/guardian/students/{$this->student->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');
    })->group('smoke', 'guardian');

    test('can access guardian billing index', function () {
        visit('/guardian/billing')
            ->waitForText('Billing')
            ->assertSee('Billing');
    })->group('smoke', 'guardian');

    test('can access guardian billing for enrollment', function () {
        visit("/guardian/billing/{$this->enrollment->id}")
            ->waitForText('Billing')
            ->assertSee('Billing');
    })->group('smoke', 'guardian');

    test('can access guardian invoices index', function () {
        visit('/guardian/invoices')
            ->waitForText('Invoices')
            ->assertSee('Invoices');
    })->group('smoke', 'guardian');

    test('can access guardian invoice show', function () {
        visit("/guardian/invoices/{$this->invoice->id}")
            ->waitForText('Invoice')
            ->assertSee('Invoice');
    })->group('smoke', 'guardian');

    test('can access guardian payments index', function () {
        visit('/guardian/payments')
            ->waitForText('Payments')
            ->assertSee('Payments');
    })->group('smoke', 'guardian');

    test('can access guardian receipts index', function () {
        visit('/guardian/receipts')
            ->waitForText('Receipts')
            ->assertSee('Receipts');
    })->group('smoke', 'guardian');

    test('can access guardian receipt show', function () {
        visit("/guardian/receipts/{$this->receipt->id}")
            ->waitForText('Receipt')
            ->assertSee('Receipt');
    })->group('smoke', 'guardian');

    test('can access settings profile', function () {
        visit('/settings/profile')
            ->waitForText('Profile')
            ->assertSee('Profile');
    })->group('smoke', 'guardian');

    test('can access settings password', function () {
        visit('/settings/password')
            ->waitForText('Password')
            ->assertSee('Password');
    })->group('smoke', 'guardian');

    test('can access settings appearance', function () {
        visit('/settings/appearance')
            ->waitForText('Appearance')
            ->assertSee('Appearance');
    })->group('smoke', 'guardian');

    test('can access settings notifications', function () {
        visit('/settings/notifications')
            ->waitForText('Notifications')
            ->assertSee('Notifications');
    })->group('smoke', 'guardian');

    test('can access notifications page', function () {
        visit('/notifications')
            ->waitForText('Notifications')
            ->assertSee('Notifications');
    })->group('smoke', 'guardian');
});

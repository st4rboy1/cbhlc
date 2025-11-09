<?php

use App\Models\Document;
use App\Models\Enrollment;
use App\Models\EnrollmentPeriod;
use App\Models\GradeLevelFee;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    // Create registrar user
    $this->registrar = User::factory()->registrar()->create([
        'email' => 'registrar@test.com',
        'password' => bcrypt('password'),
    ]);

    // Create necessary records
    $this->schoolYear = SchoolYear::factory()->create();
    $this->enrollmentPeriod = EnrollmentPeriod::factory()->create([
        'school_year_id' => $this->schoolYear->id,
    ]);
    $this->gradeLevelFee = GradeLevelFee::factory()->create([
        'school_year_id' => $this->schoolYear->id,
        'enrollment_period_id' => $this->enrollmentPeriod->id,
    ]);
    $this->guardian = Guardian::factory()->create();
    $this->student = Student::factory()->create();
    $this->enrollment = Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'guardian_id' => $this->guardian->id,
        'school_year_id' => $this->schoolYear->id,
    ]);
    $this->document = Document::factory()->create([
        'student_id' => $this->student->id,
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

    $this->actingAs($this->registrar);
});

describe('Registrar Routes Smoke Tests - Dashboard', function () {
    test('can access registrar dashboard', function () {
        visit('/registrar/dashboard')
            ->waitForText('Dashboard')
            ->assertSee('Dashboard');
    })->group('smoke', 'registrar');
});

describe('Registrar Routes Smoke Tests - Enrollments', function () {
    test('can access enrollments index', function () {
        visit('/registrar/enrollments')
            ->waitForText('Enrollments')
            ->assertSee('Enrollments');
    })->group('smoke', 'registrar');

    test('can access enrollment show', function () {
        visit("/registrar/enrollments/{$this->enrollment->id}")
            ->waitForText('Enrollment')
            ->assertSee('Enrollment');
    })->group('smoke', 'registrar');
});

describe('Registrar Routes Smoke Tests - Students', function () {
    test('can access students index', function () {
        visit('/registrar/students')
            ->waitForText('Students')
            ->assertSee('Students');
    })->group('smoke', 'registrar');

    test('can access students create', function () {
        visit('/registrar/students/create')
            ->waitForText('Create Student')
            ->assertSee('Create');
    })->group('smoke', 'registrar');

    test('can access student show', function () {
        visit("/registrar/students/{$this->student->id}")
            ->waitForText('Student')
            ->assertSee($this->student->first_name);
    })->group('smoke', 'registrar');

    test('can access student edit', function () {
        visit("/registrar/students/{$this->student->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');
    })->group('smoke', 'registrar');
});

describe('Registrar Routes Smoke Tests - Guardians', function () {
    test('can access guardians index', function () {
        visit('/registrar/guardians')
            ->waitForText('Guardians')
            ->assertSee('Guardians');
    })->group('smoke', 'registrar');

    test('can access guardians create', function () {
        visit('/registrar/guardians/create')
            ->waitForText('Create Guardian')
            ->assertSee('Create');
    })->group('smoke', 'registrar');

    test('can access guardian show', function () {
        visit("/registrar/guardians/{$this->guardian->id}")
            ->waitForText('Guardian')
            ->assertSee($this->guardian->first_name);
    })->group('smoke', 'registrar');

    test('can access guardian edit', function () {
        visit("/registrar/guardians/{$this->guardian->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');
    })->group('smoke', 'registrar');
});

describe('Registrar Routes Smoke Tests - Documents', function () {
    test('can access documents pending', function () {
        visit('/registrar/documents/pending')
            ->waitForText('Documents')
            ->assertSee('Documents');
    })->group('smoke', 'registrar');

    test('can access document show', function () {
        visit("/registrar/documents/{$this->document->id}")
            ->waitForText('Document')
            ->assertSee('Document');
    })->group('smoke', 'registrar');
});

describe('Registrar Routes Smoke Tests - Grade Level Fees', function () {
    test('can access grade level fees index', function () {
        visit('/registrar/grade-level-fees')
            ->waitForText('Grade Level Fees')
            ->assertSee('Fees');
    })->group('smoke', 'registrar');

    test('can access grade level fees create', function () {
        visit('/registrar/grade-level-fees/create')
            ->waitForText('Create')
            ->assertSee('Create');
    })->group('smoke', 'registrar');

    test('can access grade level fee show', function () {
        visit("/registrar/grade-level-fees/{$this->gradeLevelFee->id}")
            ->waitForText('Grade Level Fee')
            ->assertSee('Fee');
    })->group('smoke', 'registrar');

    test('can access grade level fee edit', function () {
        visit("/registrar/grade-level-fees/{$this->gradeLevelFee->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');
    })->group('smoke', 'registrar');
});

describe('Registrar Routes Smoke Tests - Enrollment Periods', function () {
    test('can access enrollment periods index', function () {
        visit('/registrar/enrollment-periods')
            ->waitForText('Enrollment Periods')
            ->assertSee('Periods');
    })->group('smoke', 'registrar');

    test('can access enrollment period show', function () {
        visit("/registrar/enrollment-periods/{$this->enrollmentPeriod->id}")
            ->waitForText('Enrollment Period')
            ->assertSee('Period');
    })->group('smoke', 'registrar');
});

describe('Registrar Routes Smoke Tests - School Years', function () {
    test('can access school years index', function () {
        visit('/registrar/school-years')
            ->waitForText('School Years')
            ->assertSee('School Years');
    })->group('smoke', 'registrar');

    test('can access school year show', function () {
        visit("/registrar/school-years/{$this->schoolYear->id}")
            ->waitForText('School Year')
            ->assertSee('School Year');
    })->group('smoke', 'registrar');
});

describe('Registrar Routes Smoke Tests - Invoices', function () {
    test('can access invoices index', function () {
        visit('/registrar/invoices')
            ->waitForText('Invoices')
            ->assertSee('Invoices');
    })->group('smoke', 'registrar');

    test('can access invoice show', function () {
        visit("/registrar/invoices/{$this->invoice->id}")
            ->waitForText('Invoice')
            ->assertSee('Invoice');
    })->group('smoke', 'registrar');
});

describe('Registrar Routes Smoke Tests - Payments', function () {
    test('can access payments index', function () {
        visit('/registrar/payments')
            ->waitForText('Payments')
            ->assertSee('Payments');
    })->group('smoke', 'registrar');

    test('can access payments create', function () {
        visit('/registrar/payments/create')
            ->waitForText('Record Payment')
            ->assertSee('Payment');
    })->group('smoke', 'registrar');

    test('can access payment show', function () {
        visit("/registrar/payments/{$this->payment->id}")
            ->waitForText('Payment')
            ->assertSee('Payment');
    })->group('smoke', 'registrar');
});

describe('Registrar Routes Smoke Tests - Receipts', function () {
    test('can access receipts index', function () {
        visit('/registrar/receipts')
            ->waitForText('Receipts')
            ->assertSee('Receipts');
    })->group('smoke', 'registrar');

    test('can access receipts create', function () {
        visit('/registrar/receipts/create')
            ->waitForText('Generate Receipt')
            ->assertSee('Receipt');
    })->group('smoke', 'registrar');

    test('can access receipt show', function () {
        visit("/registrar/receipts/{$this->receipt->id}")
            ->waitForText('Receipt')
            ->assertSee('Receipt');
    })->group('smoke', 'registrar');
});

describe('Registrar Routes Smoke Tests - Settings', function () {
    test('can access settings profile', function () {
        visit('/settings/profile')
            ->waitForText('Profile')
            ->assertSee('Profile');
    })->group('smoke', 'registrar');

    test('can access settings password', function () {
        visit('/settings/password')
            ->waitForText('Password')
            ->assertSee('Password');
    })->group('smoke', 'registrar');

    test('can access settings appearance', function () {
        visit('/settings/appearance')
            ->waitForText('Appearance')
            ->assertSee('Appearance');
    })->group('smoke', 'registrar');

    test('can access settings notifications', function () {
        visit('/settings/notifications')
            ->waitForText('Notifications')
            ->assertSee('Notifications');
    })->group('smoke', 'registrar');

    test('can access notifications page', function () {
        visit('/notifications')
            ->waitForText('Notifications')
            ->assertSee('Notifications');
    })->group('smoke', 'registrar');
});

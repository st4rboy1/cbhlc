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

beforeEach(function () {
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => RolesAndPermissionsSeeder::class]);
    dd(env('APP_ENV'));

    // Create super admin user
    $this->superAdmin = User::factory()->superAdmin()->create([
        'email' => 'superadmin@test.com',
        'password' => bcrypt('password'),
    ]);

    // Create necessary records
    $this->schoolYear = SchoolYear::factory()->create();
    $this->enrollmentPeriod = EnrollmentPeriod::factory()->create([
        'school_year_id' => $this->schoolYear->id,
    ]);
    $this->gradeLevelFee = GradeLevelFee::factory()->create([
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
    $this->user = User::factory()->create();

    $this->actingAs($this->superAdmin);
});

describe('Super Admin Routes Smoke Tests - Dashboard', function () {
    test('can access super admin dashboard', function () {
        $browser = visit('/super-admin/dashboard')
            ->waitForText('Dashboard')
            ->assertSee('Dashboard');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');
});

describe('Super Admin Routes Smoke Tests - Enrollments', function () {
    test('can access enrollments index', function () {
        $browser = visit('/super-admin/enrollments')
            ->waitForText('Enrollments')
            ->assertSee('Enrollments');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access enrollments create', function () {
        $browser = visit('/super-admin/enrollments/create')
            ->waitForText('Create Enrollment')
            ->assertSee('Create');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access enrollment show', function () {
        $browser = visit("/super-admin/enrollments/{$this->enrollment->id}")
            ->waitForText('Enrollment')
            ->assertSee('Enrollment');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access enrollment edit', function () {
        $browser = visit("/super-admin/enrollments/{$this->enrollment->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');
});

describe('Super Admin Routes Smoke Tests - Students', function () {
    test('can access students index', function () {
        $browser = visit('/super-admin/students')
            ->waitForText('Students')
            ->assertSee('Students');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access students create', function () {
        $browser = visit('/super-admin/students/create')
            ->waitForText('Create Student')
            ->assertSee('Create');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access student show', function () {
        $browser = visit("/super-admin/students/{$this->student->id}")
            ->waitForText('Student')
            ->assertSee($this->student->first_name);

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access student edit', function () {
        $browser = visit("/super-admin/students/{$this->student->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');
});

describe('Super Admin Routes Smoke Tests - Guardians', function () {
    test('can access guardians index', function () {
        $browser = visit('/super-admin/guardians')
            ->waitForText('Guardians')
            ->assertSee('Guardians');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access guardians create', function () {
        $browser = visit('/super-admin/guardians/create')
            ->waitForText('Create Guardian')
            ->assertSee('Create');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access guardian show', function () {
        $browser = visit("/super-admin/guardians/{$this->guardian->id}")
            ->waitForText('Guardian')
            ->assertSee($this->guardian->first_name);

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access guardian edit', function () {
        $browser = visit("/super-admin/guardians/{$this->guardian->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');
});

describe('Super Admin Routes Smoke Tests - Documents', function () {
    test('can access documents index', function () {
        $browser = visit('/super-admin/documents')
            ->waitForText('Documents')
            ->assertSee('Documents');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access documents pending', function () {
        $browser = visit('/super-admin/documents/pending')
            ->waitForText('Pending Documents')
            ->assertSee('Documents');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access document show', function () {
        $browser = visit("/super-admin/documents/{$this->document->id}")
            ->waitForText('Document')
            ->assertSee('Document');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');
});

describe('Super Admin Routes Smoke Tests - Grade Level Fees', function () {
    test('can access grade level fees index', function () {
        $browser = visit('/super-admin/grade-level-fees')
            ->waitForText('Grade Level Fees')
            ->assertSee('Fees');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access grade level fees create', function () {
        $browser = visit('/super-admin/grade-level-fees/create')
            ->waitForText('Create')
            ->assertSee('Create');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access grade level fee show', function () {
        $browser = visit("/super-admin/grade-level-fees/{$this->gradeLevelFee->id}")
            ->waitForText('Grade Level Fee')
            ->assertSee('Fee');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access grade level fee edit', function () {
        $browser = visit("/super-admin/grade-level-fees/{$this->gradeLevelFee->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');
});

describe('Super Admin Routes Smoke Tests - Enrollment Periods', function () {
    test('can access enrollment periods index', function () {
        $browser = visit('/super-admin/enrollment-periods')
            ->waitForText('Enrollment Periods')
            ->assertSee('Periods');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access enrollment periods create', function () {
        $browser = visit('/super-admin/enrollment-periods/create')
            ->waitForText('Create')
            ->assertSee('Create');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access enrollment period show', function () {
        $browser = visit("/super-admin/enrollment-periods/{$this->enrollmentPeriod->id}")
            ->waitForText('Enrollment Period')
            ->assertSee('Period');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access enrollment period edit', function () {
        $browser = visit("/super-admin/enrollment-periods/{$this->enrollmentPeriod->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');
});

describe('Super Admin Routes Smoke Tests - School Years', function () {
    test('can access school years index', function () {
        $browser = visit('/super-admin/school-years')
            ->waitForText('School Years')
            ->assertSee('School Years');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access school years create', function () {
        $browser = visit('/super-admin/school-years/create')
            ->waitForText('Create')
            ->assertSee('Create');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access school year show', function () {
        $browser = visit("/super-admin/school-years/{$this->schoolYear->id}")
            ->waitForText('School Year')
            ->assertSee('School Year');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access school year edit', function () {
        $browser = visit("/super-admin/school-years/{$this->schoolYear->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');
});

describe('Super Admin Routes Smoke Tests - Invoices', function () {
    test('can access invoices index', function () {
        $browser = visit('/super-admin/invoices')
            ->waitForText('Invoices')
            ->assertSee('Invoices');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access invoices create', function () {
        $browser = visit('/super-admin/invoices/create')
            ->waitForText('Create Invoice')
            ->assertSee('Invoice');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access invoice show', function () {
        $browser = visit("/super-admin/invoices/{$this->invoice->id}")
            ->waitForText('Invoice')
            ->assertSee('Invoice');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access invoice edit', function () {
        $browser = visit("/super-admin/invoices/{$this->invoice->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');
});

describe('Super Admin Routes Smoke Tests - Payments', function () {
    test('can access payments index', function () {
        $browser = visit('/super-admin/payments')
            ->waitForText('Payments')
            ->assertSee('Payments');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access payments create', function () {
        $browser = visit('/super-admin/payments/create')
            ->waitForText('Record Payment')
            ->assertSee('Payment');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access payment show', function () {
        $browser = visit("/super-admin/payments/{$this->payment->id}")
            ->waitForText('Payment')
            ->assertSee('Payment');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access payment edit', function () {
        $browser = visit("/super-admin/payments/{$this->payment->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');
});

describe('Super Admin Routes Smoke Tests - Receipts', function () {
    test('can access receipts index', function () {
        $browser = visit('/super-admin/receipts')
            ->waitForText('Receipts')
            ->assertSee('Receipts');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access receipts create', function () {
        $browser = visit('/super-admin/receipts/create')
            ->waitForText('Create Receipt')
            ->assertSee('Receipt');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access receipt show', function () {
        $browser = visit("/super-admin/receipts/{$this->receipt->id}")
            ->waitForText('Receipt')
            ->assertSee('Receipt');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access receipt edit', function () {
        $browser = visit("/super-admin/receipts/{$this->receipt->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');
});

describe('Super Admin Routes Smoke Tests - Users', function () {
    test('can access users index', function () {
        $browser = visit('/super-admin/users')
            ->waitForText('Users')
            ->assertSee('Users');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access users create', function () {
        $browser = visit('/super-admin/users/create')
            ->waitForText('Create User')
            ->assertSee('Create');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access user show', function () {
        $browser = visit("/super-admin/users/{$this->user->id}")
            ->waitForText('User')
            ->assertSee($this->user->name);

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access user edit', function () {
        $browser = visit("/super-admin/users/{$this->user->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');
});

describe('Super Admin Routes Smoke Tests - Reports', function () {
    test('can access reports index', function () {
        $browser = visit('/super-admin/reports')
            ->waitForText('System Reports')
            ->assertSee('Reports');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');
});

describe('Super Admin Routes Smoke Tests - Audit Logs', function () {
    test('can access audit logs index', function () {
        $browser = visit('/super-admin/audit-logs')
            ->waitForText('Audit Logs')
            ->assertSee('Audit');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');
});

describe('Super Admin Routes Smoke Tests - School Information', function () {
    test('can access school information', function () {
        $browser = visit('/super-admin/school-information')
            ->waitForText('School Information')
            ->assertSee('School');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');
});

describe('Super Admin Routes Smoke Tests - Settings', function () {
    test('can access settings profile', function () {
        $browser = visit('/settings/profile')
            ->waitForText('Profile')
            ->assertSee('Profile');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access settings password', function () {
        $browser = visit('/settings/password')
            ->waitForText('Password')
            ->assertSee('Password');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access settings appearance', function () {
        $browser = visit('/settings/appearance')
            ->waitForText('Appearance')
            ->assertSee('Appearance');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access settings notifications', function () {
        $browser = visit('/settings/notifications')
            ->waitForText('Notifications')
            ->assertSee('Notifications');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');

    test('can access notifications page', function () {
        $browser = visit('/notifications')
            ->waitForText('Notifications')
            ->assertSee('Notifications');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'super-admin');
});

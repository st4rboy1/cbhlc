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

    // Create admin user
    $this->admin = User::factory()->administrator()->create([
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
    ]);

    // Calculate current school year name
    $currentYear = now()->year;
    $currentSchoolYearName = $currentYear.'-'.($currentYear + 1);

    // Create or get the current school year and ensure it's active

    $this->schoolYear = SchoolYear::firstOrCreate(

        ['name' => $currentSchoolYearName],

        [

            'start_year' => $currentYear,

            'end_year' => $currentYear + 1,

            'start_date' => $currentYear.'-06-01',

            'end_date' => ($currentYear + 1).'-03-31',

            'status' => 'active',

            'is_active' => true,

        ]

    );

    // Create enrollment period for this school year

    $this->enrollmentPeriod = EnrollmentPeriod::firstOrCreate(

        ['school_year_id' => $this->schoolYear->id],

        [

            'start_date' => $currentYear.'-06-01',

            'end_date' => ($currentYear + 1).'-03-31',

            'early_registration_deadline' => $currentYear.'-05-31',

            'regular_registration_deadline' => $currentYear.'-07-31',

            'late_registration_deadline' => $currentYear.'-08-31',

            'status' => 'active',

            'is_active' => true,

            'description' => "School Year {$currentSchoolYearName} Enrollment Period",

            'allow_new_students' => true,

            'allow_returning_students' => true,

        ]

    );

    $this->gradeLevelFee = GradeLevelFee::factory()->schoolYear($this->schoolYear->name)->create([
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

    $this->actingAs($this->admin);
});

describe('Admin Routes Smoke Tests - Dashboard', function () {
    test('can access admin dashboard', function () {
        visit('/admin/dashboard')
            ->waitForText('Administrator Dashboard')
            ->assertSee('Administrator Dashboard');
    })->group('smoke', 'admin');
});

describe('Admin Routes Smoke Tests - Enrollments', function () {
    test('can access enrollments index', function () {
        visit('/admin/enrollments')
            ->waitForText('Enrollments')
            ->assertSee('Enrollments');
    })->group('smoke', 'admin');

    test('can access enrollments create', function () {
        visit('/admin/enrollments/create')
            ->waitForText('New Enrollment')
            ->assertSee('New Enrollment');
    })->group('smoke', 'admin');

    test('can access enrollment show', function () {
        visit("/admin/enrollments/{$this->enrollment->id}")
            ->waitForText('Enrollment')
            ->assertSee('Enrollment');
    })->group('smoke', 'admin');

    test('can access enrollment edit', function () {
        visit("/admin/enrollments/{$this->enrollment->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');
    })->group('smoke', 'admin');
});

describe('Admin Routes Smoke Tests - Students', function () {
    test('can access students index', function () {
        visit('/admin/students')
            ->waitForText('Students')
            ->assertSee('Students');
    })->group('smoke', 'admin');

    test('can access students create', function () {
        visit('/admin/students/create')
            ->waitForText('Create Student')
            ->assertSee('Create');
    })->group('smoke', 'admin');

    test('can access student show', function () {
        visit("/admin/students/{$this->student->id}")
            ->waitForText('Student')
            ->assertSee($this->student->first_name);
    })->group('smoke', 'admin');

    test('can access student edit', function () {
        visit("/admin/students/{$this->student->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');
    })->group('smoke', 'admin');
});

describe('Admin Routes Smoke Tests - Guardians', function () {
    test('can access guardians index', function () {
        $browser = visit('/admin/guardians')
            ->waitForText('Guardians')
            ->assertSee('Guardians');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'admin');

    test('can access guardians create', function () {
        $browser = visit('/admin/guardians/create')
            ->waitForText('Create Guardian')
            ->assertSee('Create');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'admin');

    test('can access guardian show', function () {
        $browser = visit("/admin/guardians/{$this->guardian->id}")
            ->waitForText('Guardian')
            ->assertSee($this->guardian->first_name);

        assertNoConsoleErrors($browser);
    })->group('smoke', 'admin');

    test('can access guardian edit', function () {
        $browser = visit("/admin/guardians/{$this->guardian->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'admin');
});

describe('Admin Routes Smoke Tests - Documents', function () {
    test('can access documents index', function () {
        $browser = visit('/admin/documents')
            ->waitForText('Documents')
            ->assertSee('Documents');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'admin');

    test('can access documents pending', function () {
        $browser = visit('/admin/documents/pending')
            ->waitForText('Pending Documents')
            ->assertSee('Pending');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'admin');

    test('can access document show', function () {
        $browser = visit("/admin/documents/{$this->document->id}")
            ->waitForText('Document')
            ->assertSee('Document');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'admin');
});

describe('Admin Routes Smoke Tests - Grade Level Fees', function () {
    test('can access grade level fees index', function () {
        visit('/admin/grade-level-fees')
            ->waitForText('Grade Level Fees')
            ->assertSee('Fees');
    })->group('smoke', 'admin');

    test('can access grade level fees create', function () {
        visit('/admin/grade-level-fees/create')
            ->waitForText('Create')
            ->assertSee('Create');
    })->group('smoke', 'admin');

    test('can access grade level fee show', function () {
        visit("/admin/grade-level-fees/{$this->gradeLevelFee->id}")
            ->waitForText('Grade Level Fee')
            ->assertSee('Fee');
    })->group('smoke', 'admin');

    test('can access grade level fee edit', function () {
        visit("/admin/grade-level-fees/{$this->gradeLevelFee->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');
    })->group('smoke', 'admin');
});

describe('Admin Routes Smoke Tests - Enrollment Periods', function () {
    test('can access enrollment periods index', function () {
        visit('/admin/enrollment-periods')
            ->waitForText('Enrollment Periods')
            ->assertSee('Periods');
    })->group('smoke', 'admin');

    test('can access enrollment periods create', function () {
        visit('/admin/enrollment-periods/create')
            ->waitForText('Create')
            ->assertSee('Create');
    })->group('smoke', 'admin');

    test('can access enrollment period show', function () {
        visit("/admin/enrollment-periods/{$this->enrollmentPeriod->id}")
            ->waitForText('Enrollment Period')
            ->assertSee('Period');
    })->group('smoke', 'admin');

    test('can access enrollment period edit', function () {
        visit("/admin/enrollment-periods/{$this->enrollmentPeriod->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');
    })->group('smoke', 'admin');
});

describe('Admin Routes Smoke Tests - School Years', function () {
    test('can access school years index', function () {
        $browser = visit('/admin/school-years')
            ->waitForText('School Years')
            ->assertSee('School Years');

        // Check for console errors and failed network requests
        assertNoConsoleErrors($browser);
    })->group('smoke', 'admin');

    test('can access school years create', function () {
        $browser = visit('/admin/school-years/create')
            ->waitForText('Create')
            ->assertSee('Create');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'admin');

    test('can access school year show', function () {
        $browser = visit("/admin/school-years/{$this->schoolYear->id}")
            ->waitForText('School Year')
            ->assertSee('School Year');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'admin');

    test('can access school year edit', function () {
        $browser = visit("/admin/school-years/{$this->schoolYear->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'admin');
});

describe('Admin Routes Smoke Tests - Invoices', function () {
    test('can access invoices index', function () {
        visit('/admin/invoices')
            ->waitForText('Invoices')
            ->assertSee('Invoices');
    })->group('smoke', 'admin');

    test('can access invoice show', function () {
        visit("/admin/invoices/{$this->invoice->id}")
            ->waitForText('Invoice')
            ->assertSee('Invoice');
    })->group('smoke', 'admin');
});

describe('Admin Routes Smoke Tests - Payments', function () {
    test('can access payments index', function () {
        visit('/admin/payments')
            ->waitForText('Payments')
            ->assertSee('Payments');
    })->group('smoke', 'admin');

    test('can access payments create', function () {
        visit('/admin/payments/create')
            ->waitForText('Record Payment')
            ->assertSee('Payment');
    })->group('smoke', 'admin');

    test('can access payment show', function () {
        visit("/admin/payments/{$this->payment->id}")
            ->waitForText('Payment')
            ->assertSee('Payment');
    })->group('smoke', 'admin');

    test('can access payment edit', function () {
        visit("/admin/payments/{$this->payment->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');
    })->group('smoke', 'admin');
});

describe('Admin Routes Smoke Tests - Receipts', function () {
    test('can access receipts index', function () {
        $browser = visit('/admin/receipts')
            ->waitForText('Receipts')
            ->assertSee('Receipts');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'admin');

    test('can access receipts create', function () {
        $browser = visit('/admin/receipts/create')
            ->waitForText('Create Receipt')
            ->assertSee('Receipt');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'admin');

    test('can access receipt show', function () {
        $browser = visit("/admin/receipts/{$this->receipt->id}")
            ->waitForText('Receipt')
            ->assertSee('Receipt');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'admin');

    test('can access receipt edit', function () {
        $browser = visit("/admin/receipts/{$this->receipt->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'admin');
});

describe('Admin Routes Smoke Tests - Users', function () {
    test('can access users index', function () {
        visit('/admin/users')
            ->waitForText('Users')
            ->assertSee('Users');
    })->group('smoke', 'admin');

    test('can access users create', function () {
        visit('/admin/users/create')
            ->waitForText('Create User')
            ->assertSee('Create');
    })->group('smoke', 'admin');

    test('can access user show', function () {
        visit("/admin/users/{$this->user->id}")
            ->waitForText('User')
            ->assertSee($this->user->name);
    })->group('smoke', 'admin');

    test('can access user edit', function () {
        visit("/admin/users/{$this->user->id}/edit")
            ->waitForText('Edit')
            ->assertSee('Edit');
    })->group('smoke', 'admin');
});

describe('Admin Routes Smoke Tests - Reports', function () {
    test('can access reports index', function () {
        visit('/admin/reports')
            ->waitForText('Reports')
            ->assertSee('Reports');
    })->group('smoke', 'admin');
});

describe('Admin Routes Smoke Tests - Audit Logs', function () {
    test('can access audit logs index', function () {
        visit('/admin/audit-logs')
            ->waitForText('Audit Logs')
            ->assertSee('Audit');
    })->group('smoke', 'admin');
});

describe('Admin Routes Smoke Tests - School Information', function () {
    test('can access school information', function () {
        visit('/admin/school-information')
            ->waitForText('School Information')
            ->assertSee('School');
    })->group('smoke', 'admin');
});

describe('Admin Routes Smoke Tests - Settings', function () {
    test('can access settings index', function () {
        $browser = visit('/admin/settings')
            ->waitForText('Settings')
            ->assertSee('Settings');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'admin');

    test('can access settings profile', function () {
        visit('/settings/profile')
            ->waitForText('Profile')
            ->assertSee('Profile');
    })->group('smoke', 'admin');

    test('can access settings password', function () {
        visit('/settings/password')
            ->waitForText('Password')
            ->assertSee('Password');
    })->group('smoke', 'admin');

    test('can access settings appearance', function () {
        visit('/settings/appearance')
            ->waitForText('Appearance')
            ->assertSee('Appearance');
    })->group('smoke', 'admin');

    test('can access settings notifications', function () {
        visit('/settings/notifications')
            ->waitForText('Notifications')
            ->assertSee('Notifications');
    })->group('smoke', 'admin');

    test('can access notifications page', function () {
        visit('/notifications')
            ->waitForText('Notifications')
            ->assertSee('Notifications');
    })->group('smoke', 'admin');
});

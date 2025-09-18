<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Dusk\Browser;
use Illuminate\Http\UploadedFile;

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

beforeEach(function () {
    // Seed roles and permissions for each test
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('parent can access enrollment form', function () {
    $parent = User::factory()->parent()->create();

    $this->browse(function (Browser $browser) use ($parent) {
        $browser->loginAs($parent)
            ->visit('/enrollment')
            ->assertSee('Student Enrollment Form')
            ->assertPresent('input[name="student_first_name"]')
            ->assertPresent('input[name="student_last_name"]')
            ->assertPresent('input[name="birth_date"]')
            ->assertPresent('select[name="grade_level"]')
            ->assertPresent('input[name="guardian_name"]')
            ->assertPresent('input[name="guardian_email"]')
            ->assertPresent('input[name="guardian_phone"]');
    });
});

test('enrollment form validates required fields', function () {
    $parent = User::factory()->parent()->create();

    $this->browse(function (Browser $browser) use ($parent) {
        $browser->loginAs($parent)
            ->visit('/enrollment')
            ->press('Submit Application')
            ->waitForText('The student first name field is required')
            ->assertSee('The student first name field is required')
            ->assertSee('The student last name field is required')
            ->assertSee('The birth date field is required')
            ->assertSee('The grade level field is required');
    });
});

test('parent can submit enrollment application', function () {
    $parent = User::factory()->parent()->create();

    $this->browse(function (Browser $browser) use ($parent) {
        $browser->loginAs($parent)
            ->visit('/enrollment')
            ->type('student_first_name', 'John')
            ->type('student_middle_name', 'Michael')
            ->type('student_last_name', 'Doe')
            ->type('birth_date', '01/15/2015')
            ->select('gender', 'male')
            ->select('grade_level', 'grade_1')
            ->type('address', '123 Main St, City')
            ->type('guardian_name', 'Jane Doe')
            ->type('guardian_email', 'jane.doe@example.com')
            ->type('guardian_phone', '09123456789')
            ->select('guardian_relationship', 'mother')
            ->press('Submit Application')
            ->waitForText('Application submitted successfully')
            ->assertSee('Application submitted successfully')
            ->assertSee('Application Reference Number');
    });
});

test('enrollment form allows document upload', function () {
    $parent = User::factory()->parent()->create();

    $this->browse(function (Browser $browser) use ($parent) {
        $browser->loginAs($parent)
            ->visit('/enrollment')
            ->attach('birth_certificate', __DIR__ . '/../../storage/app/test-files/birth_cert.jpg')
            ->attach('report_card', __DIR__ . '/../../storage/app/test-files/report_card.pdf')
            ->assertSee('birth_cert.jpg')
            ->assertSee('report_card.pdf');
    });
});

test('enrollment form saves draft automatically', function () {
    $parent = User::factory()->parent()->create();

    $this->browse(function (Browser $browser) use ($parent) {
        $browser->loginAs($parent)
            ->visit('/enrollment')
            ->type('student_first_name', 'John')
            ->type('student_last_name', 'Doe')
            ->pause(2000) // Wait for auto-save
            ->assertSee('Draft saved')
            ->refresh()
            ->assertInputValue('student_first_name', 'John')
            ->assertInputValue('student_last_name', 'Doe');
    });
});

test('registrar can review submitted applications', function () {
    $registrar = User::factory()->registrar()->create();
    $parent = User::factory()->parent()->create();

    // Parent submits application
    $this->browse(function (Browser $browser) use ($parent) {
        $browser->loginAs($parent)
            ->visit('/enrollment')
            ->type('student_first_name', 'John')
            ->type('student_last_name', 'Doe')
            ->type('birth_date', '01/15/2015')
            ->select('grade_level', 'grade_1')
            ->type('guardian_name', 'Jane Doe')
            ->type('guardian_email', 'jane@example.com')
            ->type('guardian_phone', '09123456789')
            ->press('Submit Application');
    });

    // Registrar reviews application
    $this->browse(function (Browser $browser) use ($registrar) {
        $browser->loginAs($registrar)
            ->visit('/enrollments')
            ->assertSee('John Doe')
            ->click('[data-testid="view-application-1"]')
            ->assertSee('Student Information')
            ->assertSee('John Doe')
            ->assertSee('Guardian: Jane Doe')
            ->assertPresent('[data-testid="approve-button"]')
            ->assertPresent('[data-testid="reject-button"]');
    });
});

test('registrar can approve enrollment application', function () {
    $registrar = User::factory()->registrar()->create();

    $this->browse(function (Browser $browser) use ($registrar) {
        $browser->loginAs($registrar)
            ->visit('/enrollments')
            ->click('[data-testid="view-application-1"]')
            ->press('Approve')
            ->waitForText('Application approved successfully')
            ->assertSee('Application approved successfully')
            ->assertSee('Status: Approved');
    });
});

test('registrar can reject enrollment application', function () {
    $registrar = User::factory()->registrar()->create();

    $this->browse(function (Browser $browser) use ($registrar) {
        $browser->loginAs($registrar)
            ->visit('/enrollments')
            ->click('[data-testid="view-application-1"]')
            ->press('Reject')
            ->type('rejection_reason', 'Missing required documents')
            ->press('Confirm Rejection')
            ->waitForText('Application rejected')
            ->assertSee('Application rejected')
            ->assertSee('Status: Rejected');
    });
});

test('parent can track application status', function () {
    $parent = User::factory()->parent()->create();

    $this->browse(function (Browser $browser) use ($parent) {
        $browser->loginAs($parent)
            ->visit('/my-applications')
            ->assertSee('My Applications')
            ->assertSee('Application #')
            ->assertSee('Status')
            ->assertPresent('[data-testid="application-status"]');
    });
});

test('file upload validates file types and size', function () {
    $parent = User::factory()->parent()->create();

    $this->browse(function (Browser $browser) use ($parent) {
        $browser->loginAs($parent)
            ->visit('/enrollment')
            ->attach('birth_certificate', __DIR__ . '/../../storage/app/test-files/invalid.txt')
            ->waitForText('File must be JPEG or PNG')
            ->assertSee('File must be JPEG or PNG');
    });
});

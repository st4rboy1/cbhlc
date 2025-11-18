<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Dusk\Browser;

beforeEach(function () {
    // Seed roles and permissions for each test
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('super admin sees all menu items', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->browse(function (Browser $browser) use ($admin) {
        $browser->loginAs($admin)
            ->visit('/admin/dashboard')
            ->assertSeeLink('Dashboard')
            ->assertSeeLink('Students')
            ->assertSeeLink('Enrollments')
            ->assertSeeLink('Documents')
            ->assertSeeLink('Reports')
            ->assertSeeLink('Users')
            ->assertSeeLink('Settings');
    });
});

test('registrar sees limited menu items', function () {
    $registrar = User::factory()->registrar()->create();

    $this->browse(function (Browser $browser) use ($registrar) {
        $browser->loginAs($registrar)
            ->visit('/registrar/dashboard')
            ->assertSeeLink('Dashboard')
            ->assertSeeLink('Students')
            ->assertSeeLink('Enrollments')
            ->assertSeeLink('Documents')
            ->assertSeeLink('Reports')
            ->assertDontSeeLink('Users')
            ->assertDontSeeLink('Settings');
    });
});

test('guardian sees enrollment-related menu items only', function () {
    $guardian = User::factory()->guardian()->create();

    $this->browse(function (Browser $browser) use ($guardian) {
        $browser->loginAs($guardian)
            ->visit('/guardian/dashboard')
            ->assertSeeLink('Dashboard')
            ->assertSeeLink('My Applications')
            ->assertSeeLink('Documents')
            ->assertDontSeeLink('Students')
            ->assertDontSeeLink('Reports')
            ->assertDontSeeLink('Users')
            ->assertDontSeeLink('Settings');
    });
});

test('student sees minimal menu items', function () {
    $student = User::factory()->student()->create();

    $this->browse(function (Browser $browser) use ($student) {
        $browser->loginAs($student)
            ->visit('/student/dashboard')
            ->assertSeeLink('Dashboard')
            ->assertSeeLink('My Enrollment')
            ->assertDontSeeLink('Documents')
            ->assertDontSeeLink('Students')
            ->assertDontSeeLink('Reports')
            ->assertDontSeeLink('Users')
            ->assertDontSeeLink('Settings');
    });
});

test('enrollment approval button only visible to authorized users', function () {
    $registrar = User::factory()->registrar()->create();
    $guardian = User::factory()->guardian()->create();

    // Registrar should see approval button
    $this->browse(function (Browser $browser) use ($registrar) {
        $browser->loginAs($registrar)
            ->visit('/enrollments')
            ->waitFor('[data-testid="approve-button"]')
            ->assertPresent('[data-testid="approve-button"]')
            ->assertPresent('[data-testid="reject-button"]');
    });

    // Parent should not see approval buttons
    $this->browse(function (Browser $browser) use ($guardian) {
        $browser->loginAs($guardian)
            ->visit('/enrollments')
            ->assertMissing('[data-testid="approve-button"]')
            ->assertMissing('[data-testid="reject-button"]');
    });
});

test('document verification only available to authorized users', function () {
    $registrar = User::factory()->registrar()->create();
    $guardian = User::factory()->guardian()->create();

    // Registrar can verify documents
    $this->browse(function (Browser $browser) use ($registrar) {
        $browser->loginAs($registrar)
            ->visit('/documents')
            ->waitFor('[data-testid="verify-document-button"]')
            ->assertPresent('[data-testid="verify-document-button"]');
    });

    // Parent cannot verify documents
    $this->browse(function (Browser $browser) use ($guardian) {
        $browser->loginAs($guardian)
            ->visit('/documents')
            ->assertMissing('[data-testid="verify-document-button"]');
    });
});

test('user management only accessible to administrators', function () {
    $admin = User::factory()->administrator()->create();
    $registrar = User::factory()->registrar()->create();

    // Admin can access user management
    $this->browse(function (Browser $browser) use ($admin) {
        $browser->loginAs($admin)
            ->visit('/users')
            ->assertPathIs('/users')
            ->assertSee('User Management')
            ->assertPresent('[data-testid="create-user-button"]');
    });

    // Registrar cannot access user management
    $this->browse(function (Browser $browser) use ($registrar) {
        $browser->loginAs($registrar)
            ->visit('/users')
            ->assertPathIsNot('/users')
            ->assertSee('403'); // Forbidden
    });
});

test('system settings only accessible to super admin', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $admin = User::factory()->administrator()->create();

    // Super Admin can access system settings
    $this->browse(function (Browser $browser) use ($superAdmin) {
        $browser->loginAs($superAdmin)
            ->visit('/settings')
            ->assertPathIs('/settings')
            ->assertSee('System Settings')
            ->assertPresent('[data-testid="system-config"]');
    });

    // Regular admin might have limited access
    $this->browse(function (Browser $browser) use ($admin) {
        $browser->loginAs($admin)
            ->visit('/settings')
            ->assertPathIs('/settings')
            ->assertPresent('[data-testid="system-config"]');
    });
});

test('report generation restricted by role', function () {
    $registrar = User::factory()->registrar()->create();
    $guardian = User::factory()->guardian()->create();

    // Registrar can generate reports
    $this->browse(function (Browser $browser) use ($registrar) {
        $browser->loginAs($registrar)
            ->visit('/reports')
            ->assertPresent('[data-testid="generate-report-button"]')
            ->assertSee('Generate Report');
    });

    // Parent cannot generate reports
    $this->browse(function (Browser $browser) use ($guardian) {
        $browser->loginAs($guardian)
            ->visit('/reports')
            ->assertPathIsNot('/reports')
            ->assertSee('403');
    });
});

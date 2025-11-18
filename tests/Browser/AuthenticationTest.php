<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Dusk\Browser;

beforeEach(function () {
    // Seed roles and permissions for each test
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => RolesAndPermissionsSeeder::class]);
});

test('login screen can be accessed', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
            ->assertSee('Sign in')
            ->assertPresent('input[name="email"]')
            ->assertPresent('input[name="password"]')
            ->assertPresent('button[type="submit"]');
    });
});

test('users can login with valid credentials', function () {
    $user = User::factory()->create();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->visit('/login')
            ->type('email', $user->email)
            ->type('password', 'password')
            ->press('Sign in')
            ->waitForLocation('/guardian/dashboard')
            ->assertAuthenticated()
            ->assertPathIs('/guardian/dashboard');
    });
});

test('super admin redirects to admin dashboard', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->browse(function (Browser $browser) use ($admin) {
        $browser->visit('/login')
            ->type('email', $admin->email)
            ->type('password', 'password')
            ->press('Sign in')
            ->waitForLocation('/admin/dashboard')
            ->assertAuthenticated()
            ->assertPathIs('/admin/dashboard');
    });
});

test('registrar redirects to registrar dashboard', function () {
    $registrar = User::factory()->registrar()->create();

    $this->browse(function (Browser $browser) use ($registrar) {
        $browser->visit('/login')
            ->type('email', $registrar->email)
            ->type('password', 'password')
            ->press('Sign in')
            ->waitForLocation('/registrar/dashboard')
            ->assertAuthenticated()
            ->assertPathIs('/registrar/dashboard');
    });
});

test('guardian redirects to guardian dashboard', function () {
    $guardian = User::factory()->guardian()->create();

    $this->browse(function (Browser $browser) use ($guardian) {
        $browser->visit('/login')
            ->type('email', $guardian->email)
            ->type('password', 'password')
            ->press('Sign in')
            ->waitForLocation('/guardian/dashboard')
            ->assertAuthenticated()
            ->assertPathIs('/guardian/dashboard');
    });
});

test('student redirects to student dashboard', function () {
    $student = User::factory()->student()->create();

    $this->browse(function (Browser $browser) use ($student) {
        $browser->visit('/login')
            ->type('email', $student->email)
            ->type('password', 'password')
            ->press('Sign in')
            ->waitForLocation('/student/dashboard')
            ->assertAuthenticated()
            ->assertPathIs('/student/dashboard');
    });
});

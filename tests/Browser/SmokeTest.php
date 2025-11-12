<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

beforeEach(function () {
    // Seed roles and permissions for each test
    $this->seed(RolesAndPermissionsSeeder::class);
});

describe('Critical Path Smoke Tests', function () {

    test('super admin can login and access dashboard', function () {
        $admin = User::factory()->superAdmin()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $browser = visit('/login')
            ->type('email', $admin->email)
            ->type('password', 'password')
            ->press('Log in')
            ->waitForText('Dashboard')
            ->assertPathIs('/super-admin/dashboard');

        $this->assertAuthenticatedAs($admin);
    })->group('smoke', 'critical');

    test('super admin can access create student form', function () {
        $admin = User::factory()->superAdmin()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        // Login first
        visit('/login')
            ->type('email', $admin->email)
            ->type('password', 'password')
            ->press('Log in')
            ->waitForText('Dashboard');

        // Navigate to create student form
        $browser = visit('/super-admin/students/create')
            ->waitForText('Create Student')
            ->assertSee('Create Student')
            ->assertSee('First Name')
            ->assertSee('Last Name')
            ->assertSee('Birth Date');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'critical', 'student-form');

    test('registrar can login and access dashboard', function () {
        $registrar = User::factory()->registrar()->create([
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
        ]);

        visit('/login')
            ->type('email', $registrar->email)
            ->type('password', 'password')
            ->press('Log in')
            ->waitForText('Dashboard')
            ->assertPathIs('/registrar/dashboard');

        $this->assertAuthenticatedAs($registrar);
    })->group('smoke', 'critical');
});

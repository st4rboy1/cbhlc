<?php

use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    // Create student user
    $this->studentUser = User::factory()->student()->create([
        'email' => 'student@test.com',
        'password' => bcrypt('password'),
    ]);

    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
    ]);

    $this->actingAs($this->studentUser);
});

describe('Student Routes Smoke Tests', function () {
    test('can access student dashboard', function () {
        $browser = visit('/student/dashboard')
            ->waitForText('Dashboard')
            ->assertSee('Dashboard');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'student');

    test('can access student report', function () {
        $browser = visit("/students/{$this->student->id}/report")
            ->waitForText('Report')
            ->assertSee('Report');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'student');

    test('can access settings profile', function () {
        $browser = visit('/settings/profile')
            ->waitForText('Profile')
            ->assertSee('Profile');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'student');

    test('can access settings password', function () {
        $browser = visit('/settings/password')
            ->waitForText('Password')
            ->assertSee('Password');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'student');

    test('can access settings appearance', function () {
        $browser = visit('/settings/appearance')
            ->waitForText('Appearance')
            ->assertSee('Appearance');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'student');

    test('can access settings notifications', function () {
        $browser = visit('/settings/notifications')
            ->waitForText('Notifications')
            ->assertSee('Notifications');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'student');

    test('can access notifications page', function () {
        $browser = visit('/notifications')
            ->waitForText('Notifications')
            ->assertSee('Notifications');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'student');
});

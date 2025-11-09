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
        visit('/student/dashboard')
            ->waitForText('Dashboard')
            ->assertSee('Dashboard');
    })->group('smoke', 'student');

    test('can access student report', function () {
        visit("/students/{$this->student->id}/report")
            ->waitForText('Report')
            ->assertSee('Report');
    })->group('smoke', 'student');

    test('can access settings profile', function () {
        visit('/settings/profile')
            ->waitForText('Profile')
            ->assertSee('Profile');
    })->group('smoke', 'student');

    test('can access settings password', function () {
        visit('/settings/password')
            ->waitForText('Password')
            ->assertSee('Password');
    })->group('smoke', 'student');

    test('can access settings appearance', function () {
        visit('/settings/appearance')
            ->waitForText('Appearance')
            ->assertSee('Appearance');
    })->group('smoke', 'student');

    test('can access settings notifications', function () {
        visit('/settings/notifications')
            ->waitForText('Notifications')
            ->assertSee('Notifications');
    })->group('smoke', 'student');

    test('can access notifications page', function () {
        visit('/notifications')
            ->waitForText('Notifications')
            ->assertSee('Notifications');
    })->group('smoke', 'student');
});

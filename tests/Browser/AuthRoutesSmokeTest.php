<?php

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

describe('Auth Routes Smoke Tests', function () {
    test('can access login page', function () {
        visit('/login')
            ->waitForText('Log in')
            ->assertSee('Email')
            ->assertSee('Password');
    })->group('smoke', 'auth');

    test('can access register page', function () {
        visit('/register')
            ->waitForText('Create an account')
            ->assertSee('Name')
            ->assertSee('Email')
            ->assertSee('Password');
    })->group('smoke', 'auth');

    test('can access forgot password page', function () {
        visit('/forgot-password')
            ->waitForText('Forgot Password')
            ->assertSee('Email');
    })->group('smoke', 'auth');

    test('can access verify email page', function () {
        $user = \App\Models\User::factory()->unverified()->create();
        $this->actingAs($user);

        visit('/verify-email')
            ->waitForText('Verify')
            ->assertSee('verify');
    })->group('smoke', 'auth');

    test('can access confirm password page', function () {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        visit('/confirm-password')
            ->waitForText('Confirm Password')
            ->assertSee('Password');
    })->group('smoke', 'auth');
});

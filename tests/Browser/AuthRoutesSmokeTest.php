<?php

describe('Auth Routes Smoke Tests', function () {
    test('can access login page', function () {
        $browser = visit('/login')
            ->waitForText('Log in')
            ->assertSee('Email')
            ->assertSee('Password');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'auth');

    test('can access register page', function () {
        $browser = visit('/register')
            ->waitForText('Create an account')
            ->assertSee('Name')
            ->assertSee('Email')
            ->assertSee('Password');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'auth');

    test('can access forgot password page', function () {
        $browser = visit('/forgot-password')
            ->waitForText('Forgot Password')
            ->assertSee('Email');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'auth');

    test('can access verify email page', function () {
        $user = \App\Models\User::factory()->unverified()->create();
        $this->actingAs($user);

        $browser = visit('/verify-email')
            ->waitForText('Verify')
            ->assertSee('verify');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'auth');

    test('can access confirm password page', function () {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $browser = visit('/confirm-password')
            ->waitForText('Confirm Password')
            ->assertSee('Password');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'auth');
});

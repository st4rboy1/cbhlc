<?php

test('browser automation demo - login page loads', function () {
    // Simple demo showing Pest v4 browser testing in action
    visit('/login')
        ->assertSee('Log in to your account')
        ->assertSee('Email address')
        ->assertSee('Password')
        ->assertPathIs('/login');
})->group('demo', 'simple');

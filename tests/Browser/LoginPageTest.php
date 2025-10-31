<?php

test('login page loads without errors', function () {
    // Ultra-simple smoke test to verify browser testing works
    // Just check that the page loads and we're on the right path
    visit('/login')
        ->assertPathIs('/login');
})->group('browser', 'smoke');

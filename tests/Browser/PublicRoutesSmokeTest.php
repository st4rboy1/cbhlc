<?php

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

describe('Public Routes Smoke Tests', function () {
    test('can access homepage', function () {
        $browser = visit('/')
            ->waitForText('Christian Bible Heritage Learning Center')
            ->assertSee('Christian Bible Heritage Learning Center');

        assertNoConsoleErrors($browser);
    })->skip('Homepage not yet implemented')->group('smoke', 'public');

    test('can access about page', function () {
        $browser = visit('/about')
            ->waitForText('About')
            ->assertSee('About');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'public');

    test('can access contact page', function () {
        $browser = visit('/contact')
            ->waitForText('Contact')
            ->assertSee('Contact');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'public');

    test('can access application page', function () {
        $browser = visit('/application')
            ->waitForText('Application')
            ->assertSee('Application');

        assertNoConsoleErrors($browser);
    })->group('smoke', 'public');

    test('can access tuition page', function () {
        $browser = visit('/tuition')
            ->waitForText('Tuition')
            ->assertSee('Tuition');

        assertNoConsoleErrors($browser);
    })->skip('Tuition page not yet implemented')->group('smoke', 'public');

    test('can access help page', function () {
        $browser = visit('/help')
            ->waitForText('Help')
            ->assertSee('Help');

        assertNoConsoleErrors($browser);
    })->skip('Help page not yet implemented')->group('smoke', 'public');

    test('can access support page', function () {
        $browser = visit('/support')
            ->waitForText('Support')
            ->assertSee('Support');

        assertNoConsoleErrors($browser);
    })->skip('Support page not yet implemented')->group('smoke', 'public');

    test('can access resources page', function () {
        $browser = visit('/resources')
            ->waitForText('Resources')
            ->assertSee('Resources');

        assertNoConsoleErrors($browser);
    })->skip('Resources page not yet implemented')->group('smoke', 'public');

    test('can access docs page', function () {
        $browser = visit('/docs')
            ->waitForText('Documentation')
            ->assertSee('Documentation');

        assertNoConsoleErrors($browser);
    })->skip('Documentation page not yet implemented')->group('smoke', 'public');

    test('can access parent guide page', function () {
        $browser = visit('/parent-guide')
            ->waitForText('Parent Guide')
            ->assertSee('Parent Guide');

        assertNoConsoleErrors($browser);
    })->skip('Parent guide page not yet implemented')->group('smoke', 'public');
});

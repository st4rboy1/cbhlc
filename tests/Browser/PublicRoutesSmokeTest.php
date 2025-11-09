<?php

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

describe('Public Routes Smoke Tests', function () {
    test('can access homepage', function () {
        visit('/')
            ->waitForText('Christian Bible Heritage Learning Center')
            ->assertSee('Christian Bible Heritage Learning Center');
    })->group('smoke', 'public');

    test('can access about page', function () {
        visit('/about')
            ->waitForText('About')
            ->assertSee('About');
    })->group('smoke', 'public');

    test('can access contact page', function () {
        visit('/contact')
            ->waitForText('Contact')
            ->assertSee('Contact');
    })->group('smoke', 'public');

    test('can access application page', function () {
        visit('/application')
            ->waitForText('Application')
            ->assertSee('Application');
    })->group('smoke', 'public');

    test('can access tuition page', function () {
        visit('/tuition')
            ->waitForText('Tuition')
            ->assertSee('Tuition');
    })->group('smoke', 'public');

    test('can access help page', function () {
        visit('/help')
            ->waitForText('Help')
            ->assertSee('Help');
    })->group('smoke', 'public');

    test('can access support page', function () {
        visit('/support')
            ->waitForText('Support')
            ->assertSee('Support');
    })->group('smoke', 'public');

    test('can access resources page', function () {
        visit('/resources')
            ->waitForText('Resources')
            ->assertSee('Resources');
    })->group('smoke', 'public');

    test('can access docs page', function () {
        visit('/docs')
            ->waitForText('Documentation')
            ->assertSee('Documentation');
    })->group('smoke', 'public');

    test('can access parent guide page', function () {
        visit('/parent-guide')
            ->waitForText('Parent Guide')
            ->assertSee('Parent Guide');
    })->group('smoke', 'public');
});

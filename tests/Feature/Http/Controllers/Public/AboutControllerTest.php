<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

test('about page renders successfully', function () {
    $response = $this->get('/about');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('public/about')
    );
});

test('about page can be accessed without authentication', function () {
    $response = $this->get('/about');

    $response->assertOk();
    // Verify we're not redirected to login
});

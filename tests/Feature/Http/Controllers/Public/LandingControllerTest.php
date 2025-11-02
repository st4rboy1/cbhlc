<?php

use Inertia\Testing\AssertableInertia;

test('landing page renders successfully', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('public/landing')
    );
});

test('landing page can be accessed without authentication', function () {
    $response = $this->get('/');

    $response->assertOk();
    // Verify we're not redirected to login
});

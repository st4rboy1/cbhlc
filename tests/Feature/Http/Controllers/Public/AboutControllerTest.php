<?php

use Inertia\Testing\AssertableInertia;

test('about page renders successfully', function () {
    $response = $this->get('/about');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('about')
    );
});

test('about page can be accessed without authentication', function () {
    $response = $this->get('/about');

    $response->assertOk();
    // Verify we're not redirected to login
    $response->assertDontSee('login');
});

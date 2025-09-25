<?php

use Inertia\Testing\AssertableInertia;

test('application page renders successfully', function () {
    $response = $this->get('/application');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('application')
    );
});

test('application page can be accessed without authentication', function () {
    $response = $this->get('/application');

    $response->assertOk();
    // Verify we're not redirected to login
    $response->assertDontSee('login');
});

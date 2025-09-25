<?php

use Inertia\Testing\AssertableInertia;

test('registrar info page renders successfully', function () {
    $response = $this->get('/registrar');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('registrar')
    );
});

test('registrar info page can be accessed without authentication', function () {
    $response = $this->get('/registrar');

    $response->assertOk();
    // Verify we're not redirected to login
    $response->assertDontSee('login');
});

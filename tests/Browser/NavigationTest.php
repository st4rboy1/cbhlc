<?php

use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Dusk\Browser;

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

beforeEach(function () {
    // Seed roles and permissions for each test
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('can navigate through all pages by clicking links', function () {
    $this->browse(function (Browser $browser) {
        // Start at landing page
        $browser->visit('/')
            ->assertSee('CBHLC')
            ->assertSee('Start children off on the way they should go');

        // Click About link and verify navigation
        $browser->clickLink('About')
            ->assertPathIs('/about')
            ->assertSee('About');

        // Go back to home by clicking CBHLC logo
        $browser->clickLink('CBHLC')
            ->assertPathIs('/')
            ->assertSee('Start children off on the way they should go');
    });
});

test('enrollment and application pages are accessible and linked', function () {
    $this->browse(function (Browser $browser) {
        // Visit enrollment page
        $browser->visit('/enrollment')
            ->assertSee('Enrollment Form');

        // Click Edit Submitted Application link
        $browser->clickLink('Edit Submitted Application')
            ->assertPathIs('/application')
            ->assertSee('Application Form');

        // Click Save to go back to enrollment
        $browser->clickLink('Save')
            ->assertPathIs('/enrollment')
            ->assertSee('Enrollment Form');
    });
});

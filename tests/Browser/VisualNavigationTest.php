<?php

use Laravel\Dusk\Browser;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

beforeEach(function () {
    // Seed roles and permissions for each test
    $this->seed(RolesAndPermissionsSeeder::class);
    
    // Set environment variable to disable headless mode
    putenv('DUSK_HEADLESS_DISABLED=true');
});

test('visual check of landing page', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->pause(2000) // Wait for page to fully load
            ->assertSee('CBHLC')
            ->screenshot('landing-visual')
            ->pause(1000);
    });
});

test('visual navigation from landing to about', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->pause(2000)
            ->screenshot('before-clicking-about')
            ->clickLink('About')
            ->pause(2000)
            ->assertPathIs('/about')
            ->screenshot('after-clicking-about')
            ->pause(1000);
    });
});

test('check what is actually rendered on pages', function () {
    $this->browse(function (Browser $browser) {
        // Check landing page content
        $browser->visit('/')
            ->pause(2000);
        
        $bodyText = $browser->text('body');
        echo "\n\n=== LANDING PAGE CONTENT ===\n";
        echo substr($bodyText, 0, 500) . "...\n";
        
        // Check about page content
        $browser->visit('/about')
            ->pause(2000);
            
        $aboutText = $browser->text('body');
        echo "\n=== ABOUT PAGE CONTENT ===\n";
        echo substr($aboutText, 0, 500) . "...\n";
        
        // Check enrollment page
        $browser->visit('/enrollment')
            ->pause(2000);
            
        $enrollmentText = $browser->text('body');
        echo "\n=== ENROLLMENT PAGE CONTENT ===\n";
        echo substr($enrollmentText, 0, 500) . "...\n";
    });
});

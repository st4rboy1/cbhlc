<?php

use Laravel\Dusk\Browser;

test('can visit landing page and click about', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->assertSee('CBHLC')
            ->clickLink('About')
            ->pause(1000)
            ->assertPathIs('/about');
    });
});

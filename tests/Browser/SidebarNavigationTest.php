<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class SidebarNavigationTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test sidebar navigation links are clickable and functional
     */
    public function test_sidebar_navigation_links_work(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/dashboard')
                ->assertSee('DASHBOARD')
                ->assertSee('ENROLLMENT')
                ->assertSee('BILLING')
                ->assertSee('STUDENT REPORT')
                ->assertSee('REGISTRAR');

            // Test navigation to enrollment page
            $browser->clickLink('ENROLLMENT')
                ->pause(500)
                ->assertPathIs('/enrollment')
                ->assertSee('Enrollment Form');

            // Test navigation to billing/invoice page
            $browser->clickLink('BILLING')
                ->pause(200)
                ->clickLink('Generate Invoice')
                ->pause(500)
                ->assertPathIs('/invoice')
                ->assertSee('INVOICE');

            // Test navigation to tuition page
            $browser->clickLink('BILLING')
                ->pause(200)
                ->clickLink('Tuition Fee')
                ->pause(500)
                ->assertPathIs('/tuition')
                ->assertSee('TUITION');

            // Test navigation to student report page
            $browser->clickLink('STUDENT REPORT')
                ->pause(500)
                ->assertPathIs('/studentreport')
                ->assertSee('STUDENT REPORT');

            // Test navigation to registrar page
            $browser->clickLink('REGISTRAR')
                ->pause(500)
                ->assertPathIs('/registrar')
                ->assertSee('REGISTRAR');

            // Test navigation back to dashboard
            $browser->clickLink('DASHBOARD')
                ->pause(500)
                ->assertPathIs('/dashboard')
                ->assertSee('Dashboard');
        });
    }

    /**
     * Test sidebar maintains active state on current page
     */
    public function test_sidebar_shows_active_state(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/enrollment')
                ->assertPresent('.nav-item.active')
                ->assertSeeIn('.nav-item.active', 'ENROLLMENT');

            $browser->visit('/studentreport')
                ->assertPresent('.nav-item.active')
                ->assertSeeIn('.nav-item.active', 'STUDENT REPORT');
        });
    }

    /**
     * Test logout functionality from sidebar
     */
    public function test_sidebar_logout_works(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/dashboard')
                ->assertSee('LOGOUT')
                ->click('form[action="/logout"] button')
                ->pause(500)
                ->assertPathIs('/login')
                ->assertGuest();
        });
    }

    /**
     * Test sidebar is consistent across all pages
     */
    public function test_sidebar_consistent_across_pages(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $pages = [
                '/dashboard',
                '/enrollment',
                '/invoice',
                '/tuition',
                '/studentreport',
                '/registrar',
                '/application'
            ];

            $browser->loginAs($user);

            foreach ($pages as $page) {
                $browser->visit($page)
                    ->assertSee('DASHBOARD')
                    ->assertSee('ENROLLMENT')
                    ->assertSee('BILLING')
                    ->assertSee('STUDENT REPORT')
                    ->assertSee('REGISTRAR')
                    ->assertSee('LOGOUT');
            }
        });
    }
}

<?php

namespace Tests\Browser;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class RegistrationRoleSelectionTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles for the tests
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * Test that registration page shows role selection options
     */
    public function test_registration_page_displays_role_selection(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->assertSee('I am registering as a')
                ->assertRadioSelected('role', 'parent') // Default selection
                ->assertSeeIn('label[for="parent"]', 'Parent/Guardian')
                ->assertSeeIn('label[for="student"]', 'Student (18+ years old)');
        });
    }

    /**
     * Test parent registration flow
     */
    public function test_parent_can_register_and_is_redirected_to_parent_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->type('name', 'Test Parent User')
                ->type('email', 'testparent@example.com')
                ->type('password', 'password123')
                ->type('password_confirmation', 'password123')
                ->radio('role', 'parent')
                ->press('Create account')
                ->waitForLocation('/parent/dashboard')
                ->assertPathIs('/parent/dashboard')
                ->assertSee('Parent Dashboard');
        });

        // Verify the user was created with the correct role
        $this->assertDatabaseHas('users', [
            'email' => 'testparent@example.com',
            'name' => 'Test Parent User',
        ]);

        $user = \App\Models\User::where('email', 'testparent@example.com')->first();
        $this->assertTrue($user->hasRole('parent'));
    }

    /**
     * Test student registration flow
     */
    public function test_student_can_register_and_is_redirected_to_student_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->type('name', 'Test Student User')
                ->type('email', 'teststudent@example.com')
                ->type('password', 'password123')
                ->type('password_confirmation', 'password123')
                ->radio('role', 'student')
                ->press('Create account')
                ->waitForLocation('/student/dashboard')
                ->assertPathIs('/student/dashboard')
                ->assertSee('Student Dashboard');
        });

        // Verify the user was created with the correct role
        $this->assertDatabaseHas('users', [
            'email' => 'teststudent@example.com',
            'name' => 'Test Student User',
        ]);

        $user = \App\Models\User::where('email', 'teststudent@example.com')->first();
        $this->assertTrue($user->hasRole('student'));
    }

    /**
     * Test that registration fails without role selection
     */
    public function test_registration_requires_role_selection(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->type('name', 'Test User')
                ->type('email', 'testuser@example.com')
                ->type('password', 'password123')
                ->type('password_confirmation', 'password123')
                // Attempt to remove the role selection via JavaScript
                ->script('document.querySelectorAll("input[name=\'role\']").forEach(el => el.checked = false)');

            $browser->press('Create account')
                ->waitForText('The role field is required')
                ->assertSee('The role field is required');
        });

        // Verify the user was NOT created
        $this->assertDatabaseMissing('users', [
            'email' => 'testuser@example.com',
        ]);
    }

    /**
     * Test role selection is maintained on validation errors
     */
    public function test_role_selection_persists_on_validation_error(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->type('name', 'Test User')
                ->type('email', 'invalid-email') // Invalid email
                ->type('password', 'password123')
                ->type('password_confirmation', 'password123')
                ->radio('role', 'student')
                ->press('Create account')
                ->waitForText('The email field must be a valid email address')
                ->assertRadioSelected('role', 'student'); // Should still be selected
        });
    }

    /**
     * Test user can switch between role options
     */
    public function test_user_can_switch_role_selection(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->assertRadioSelected('role', 'parent') // Default
                ->radio('role', 'student')
                ->assertRadioSelected('role', 'student')
                ->assertRadioNotSelected('role', 'parent')
                ->radio('role', 'parent')
                ->assertRadioSelected('role', 'parent')
                ->assertRadioNotSelected('role', 'student');
        });
    }

    /**
     * Test registration with all required fields and parent role via form submission
     */
    public function test_complete_parent_registration_flow(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->assertSee('Create an account')
                ->assertSee('Enter your details below to create your account')
                ->type('name', 'John Parent')
                ->radio('role', 'parent')
                ->type('email', 'john.parent@example.com')
                ->type('password', 'SecurePassword123!')
                ->type('password_confirmation', 'SecurePassword123!')
                ->press('Create account')
                ->waitForLocation('/parent/dashboard', 10)
                ->assertAuthenticated();

            // Logout for cleanup
            $browser->visit('/logout')
                ->assertGuest();
        });
    }

    /**
     * Test registration with all required fields and student role via form submission
     */
    public function test_complete_student_registration_flow(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->assertSee('Create an account')
                ->type('name', 'Jane Student')
                ->radio('role', 'student')
                ->type('email', 'jane.student@example.com')
                ->type('password', 'SecurePassword123!')
                ->type('password_confirmation', 'SecurePassword123!')
                ->press('Create account')
                ->waitForLocation('/student/dashboard', 10)
                ->assertAuthenticated();

            // Logout for cleanup
            $browser->visit('/logout')
                ->assertGuest();
        });
    }
}

<?php

namespace Tests\Feature\Http\Controllers\Guardian;

use App\Models\Guardian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class LayoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $guardian;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->guardian = User::factory()->create();
        $this->guardian->assignRole('guardian');

        // Create Guardian model for the user
        Guardian::create([
            'user_id' => $this->guardian->id,
            'first_name' => 'Test',
            'last_name' => 'Guardian',
            'contact_number' => '09123456789',
            'address' => '123 Test St',
        ]);
    }

    public function test_guardian_pages_use_app_layout(): void
    {
        // Test dashboard
        $response = $this->actingAs($this->guardian)->get(route('guardian.dashboard'));
        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('guardian/dashboard')
            );

        // Test students index
        $response = $this->actingAs($this->guardian)->get(route('guardian.students.index'));
        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('guardian/students/index')
            );

        // Test enrollments index
        $response = $this->actingAs($this->guardian)->get(route('guardian.enrollments.index'));
        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('guardian/enrollments/index')
            );

        // Test billing index
        $response = $this->actingAs($this->guardian)->get(route('guardian.billing.index'));
        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('guardian/billing/index')
            );
    }
}

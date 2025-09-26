<?php

namespace Tests\Feature\Http\Controllers\Registrar;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class LayoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $registrar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->registrar = User::factory()->create();
        $this->registrar->assignRole('registrar');
    }

    public function test_registrar_pages_use_app_layout(): void
    {
        // Test dashboard
        $response = $this->actingAs($this->registrar)->get(route('registrar.dashboard'));
        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('registrar/dashboard')
            );

        // Test enrollments index
        $response = $this->actingAs($this->registrar)->get(route('registrar.enrollments.index'));
        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('registrar/enrollments/index')
            );

        // Test students index
        $response = $this->actingAs($this->registrar)->get(route('registrar.students.index'));
        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('registrar/students/index')
            );
    }
}

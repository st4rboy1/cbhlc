<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class EnrollmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');
    }

    public function test_admin_can_view_enrollments_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.enrollments.index'));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/enrollments/index')
                ->has('enrollments')
                ->has('filters')
            );
    }

    public function test_admin_can_view_enrollment_details(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.enrollments.show', 1));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/enrollments/show')
                ->has('enrollment')
                ->where('enrollment.id', '1')
            );
    }

    public function test_admin_can_view_enrollment_edit_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.enrollments.edit', 1));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/enrollments/edit')
                ->has('enrollment')
                ->where('enrollment.id', '1')
            );
    }

    public function test_non_admin_cannot_access_enrollments(): void
    {
        $user = User::factory()->create();
        $user->assignRole('guardian');

        $response = $this->actingAs($user)->get(route('admin.enrollments.index'));
        $response->assertForbidden();

        $response = $this->actingAs($user)->get(route('admin.enrollments.show', 1));
        $response->assertForbidden();

        $response = $this->actingAs($user)->get(route('admin.enrollments.edit', 1));
        $response->assertForbidden();
    }

    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get(route('admin.enrollments.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('admin.enrollments.show', 1));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('admin.enrollments.edit', 1));
        $response->assertRedirect(route('login'));
    }

    public function test_filters_are_passed_to_index_view(): void
    {
        $filters = [
            'status' => 'pending',
            'grade' => 'Grade 1',
        ];

        $response = $this->actingAs($this->admin)
            ->get(route('admin.enrollments.index', $filters));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/enrollments/index')
                ->where('filters.status', 'pending')
                ->where('filters.grade', 'Grade 1')
            );
    }
}
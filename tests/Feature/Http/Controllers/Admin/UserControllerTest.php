<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class UserControllerTest extends TestCase
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

    public function test_admin_can_view_users_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/users/index')
                ->has('users')
                ->has('total')
            );
    }

    public function test_admin_can_view_user_details(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.show', 1));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/users/show')
                ->has('user')
                ->where('user.id', '1')
                ->has('user.name')
                ->has('user.email')
                ->has('user.role')
                ->has('user.created_at')
            );
    }

    public function test_admin_can_view_user_edit_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.edit', 1));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/users/edit')
                ->has('user')
                ->where('user.id', '1')
                ->has('roles')
                ->where('roles', ['administrator', 'registrar', 'guardian', 'student'])
            );
    }

    public function test_non_admin_cannot_access_users(): void
    {
        $user = User::factory()->create();
        $user->assignRole('guardian');

        $response = $this->actingAs($user)->get(route('admin.users.index'));
        $response->assertForbidden();

        $response = $this->actingAs($user)->get(route('admin.users.show', 1));
        $response->assertForbidden();

        $response = $this->actingAs($user)->get(route('admin.users.edit', 1));
        $response->assertForbidden();
    }

    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get(route('admin.users.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('admin.users.show', 1));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('admin.users.edit', 1));
        $response->assertRedirect(route('login'));
    }

    public function test_users_index_returns_correct_data_structure(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/users/index')
                ->has('users', 2)
                ->has('users.0', fn ($user) => $user
                    ->has('id')
                    ->has('name')
                    ->has('email')
                    ->has('role')
                )
                ->where('total', 2)
            );
    }
}

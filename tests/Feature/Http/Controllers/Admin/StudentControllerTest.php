<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class StudentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');

        // Create students for testing
        \App\Models\Student::factory()->count(2)->create();
    }

    public function test_admin_can_view_students_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.students.index'));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/students/index')
                ->has('students')
                ->has('total')
            );
    }

    public function test_admin_can_view_student_details(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.students.show', 1));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/students/show')
                ->has('student')
                ->where('student.id', 1)
                ->has('student.name')
                ->has('student.grade')
                ->has('student.status')
                ->has('student.birth_date')
                ->has('student.address')
            );
    }

    public function test_admin_can_view_student_edit_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.students.edit', 1));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/students/edit')
                ->has('student')
                ->where('student.id', 1)
            );
    }

    public function test_non_admin_cannot_access_students(): void
    {
        $user = User::factory()->create();
        $user->assignRole('guardian');

        $response = $this->actingAs($user)->get(route('admin.students.index'));
        $response->assertForbidden();

        $response = $this->actingAs($user)->get(route('admin.students.show', 1));
        $response->assertForbidden();

        $response = $this->actingAs($user)->get(route('admin.students.edit', 1));
        $response->assertForbidden();
    }

    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get(route('admin.students.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('admin.students.show', 1));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('admin.students.edit', 1));
        $response->assertRedirect(route('login'));
    }

    public function test_students_index_returns_correct_data_structure(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.students.index'));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/students/index')
                ->has('students', 2)
                ->has('students.0', fn ($student) => $student
                    ->has('id')
                    ->has('name')
                    ->has('grade')
                    ->has('status')
                )
                ->where('total', 2)
            );
    }
}

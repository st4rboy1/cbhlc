<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Student;
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
        $this->seed(\Database\Seeders\UserSeeder::class); // Seed UserSeeder to populate with students and users

        $this->admin = User::where('email', 'admin@cbhlc.edu')->first(); // Get admin from seeder
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
        // Get an enrolled student from the seeder data (e.g., Juan Santos)
        $student = Student::whereHas('user', fn ($query) => $query->where('email', 'juan.santos@student.cbhlc.edu'))->first();
        if (! $student) {
            $this->fail('Seeded student "Juan Santos" not found. Check UserSeeder and Enrollment statuses.');
        }

        $response = $this->actingAs($this->admin)->get(route('admin.students.show', $student->id));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/students/show')
                ->has('student')
                ->where('student.id', $student->id)
                ->has('student.full_name') // Changed from ->has('student.name')
                ->has('student.grade')
                ->has('student.status')
                ->has('student.birth_date')
                ->has('student.address')
            );
    }

    public function test_admin_can_view_student_edit_page(): void
    {
        // Get an enrolled student from the seeder data (e.g., Juan Santos)
        $student = Student::whereHas('user', fn ($query) => $query->where('email', 'juan.santos@student.cbhlc.edu'))->first();
        if (! $student) {
            $this->fail('Seeded student "Juan Santos" not found. Check UserSeeder and Enrollment statuses.');
        }

        $response = $this->actingAs($this->admin)->get(route('admin.students.edit', $student->id));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/students/edit')
                ->has('student')
                ->where('student.id', $student->id)
            );
    }

    public function test_non_admin_cannot_access_students(): void
    {
        $user = User::factory()->create();
        $user->assignRole('guardian');

        // Get any student for this test
        $student = Student::first();
        if (! $student) {
            $this->fail('No students found for non-admin access test.');
        }

        $response = $this->actingAs($user)->get(route('admin.students.index'));
        $response->assertForbidden();

        $response = $this->actingAs($user)->get(route('admin.students.show', $student->id));
        $response->assertForbidden();

        $response = $this->actingAs($user)->get(route('admin.students.edit', $student->id));
        $response->assertForbidden();
    }

    public function test_guest_redirected_to_login(): void
    {
        // Get any student for this test
        $student = Student::first();
        if (! $student) {
            $this->fail('No students found for guest redirect test.');
        }

        $response = $this->get(route('admin.students.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('admin.students.show', $student->id));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('admin.students.edit', $student->id));
        $response->assertRedirect(route('login'));
    }

    public function test_students_index_returns_correct_data_structure(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.students.index'));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/students/index')
                ->has('students.data', 2) // Expect 2 enrolled students from UserSeeder
                ->has('students.data.0', fn ($student) => $student
                    ->has('id')
                    ->has('guardian_id')
                    ->has('student_id')
                    ->has('first_name')
                    ->has('last_name')
                    ->has('middle_name')
                    ->has('birthdate')
                    ->has('gender')
                    ->has('address')
                    ->has('contact_number')
                    ->has('email')
                    ->has('grade_level')
                    ->has('section')
                    ->has('user_id')
                    ->has('created_at')
                    ->has('updated_at')
                    ->has('birth_place')
                    ->has('nationality')
                    ->has('religion')
                    ->has('guardians')
                    ->has('enrollments')
                    ->has('full_name')
                )
                ->where('total', 2) // Expect total to be 2 enrolled students
            );
    }
}

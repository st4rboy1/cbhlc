<?php

use App\Models\Guardian;
use App\Models\GuardianStudent;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

describe('StudentReportController', function () {
    test('super admin can view any student report', function () {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $student = Student::factory()->create();

        $response = $this->actingAs($superAdmin)
            ->get(route('students.report', $student));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('shared/studentreport')
            ->has('student')
            ->where('student.id', $student->id)
        );
    });

    test('administrator can view any student report', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $student = Student::factory()->create();

        $response = $this->actingAs($admin)
            ->get(route('students.report', $student));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('shared/studentreport')
            ->has('student')
            ->where('student.id', $student->id)
        );
    });

    test('registrar can view any student report', function () {
        $registrar = User::factory()->create();
        $registrar->assignRole('registrar');

        $student = Student::factory()->create();

        $response = $this->actingAs($registrar)
            ->get(route('students.report', $student));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('shared/studentreport')
            ->has('student')
            ->where('student.id', $student->id)
        );
    });

    test('guardian can view their children report', function () {
        $guardian = User::factory()->create();
        $guardian->assignRole('guardian');

        // Create guardian model
        $guardianModel = Guardian::create([
            'user_id' => $guardian->id,
            'first_name' => 'Test',
            'last_name' => 'Guardian',
            'contact_number' => '09123456789',
            'address' => '123 Test St',
        ]);

        // Create student
        $student = Student::factory()->create();

        // Link guardian to student
        GuardianStudent::create([
            'guardian_id' => $guardian->id,
            'student_id' => $student->id,
            'relationship_type' => 'mother',
            'is_primary_contact' => true,
        ]);

        $response = $this->actingAs($guardian)
            ->get(route('students.report', $student));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('shared/studentreport')
            ->has('student')
            ->where('student.id', $student->id)
        );
    });

    test('guardian cannot view other children report', function () {
        $guardian = User::factory()->create();
        $guardian->assignRole('guardian');

        // Create guardian model
        $guardianModel = Guardian::create([
            'user_id' => $guardian->id,
            'first_name' => 'Test',
            'last_name' => 'Guardian',
            'contact_number' => '09123456789',
            'address' => '123 Test St',
        ]);

        // Create student (not linked to guardian)
        $student = Student::factory()->create();

        $response = $this->actingAs($guardian)
            ->get(route('students.report', $student));

        $response->assertStatus(403);
        $response->assertSeeText('You do not have permission to view this student report.');
    });

    test('guardian without profile cannot view any report', function () {
        $guardian = User::factory()->create();
        $guardian->assignRole('guardian');

        // No guardian model created
        $student = Student::factory()->create();

        $response = $this->actingAs($guardian)
            ->get(route('students.report', $student));

        $response->assertStatus(403);
        $response->assertSeeText('Guardian profile not found.');
    });

    test('student can view their own report', function () {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('student');

        $student = Student::factory()->create([
            'user_id' => $studentUser->id,
        ]);

        $response = $this->actingAs($studentUser)
            ->get(route('students.report', $student));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('shared/studentreport')
            ->has('student')
            ->where('student.id', $student->id)
        );
    });

    test('student cannot view other students report', function () {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('student');

        $ownStudent = Student::factory()->create([
            'user_id' => $studentUser->id,
        ]);

        // Create another student
        $otherStudent = Student::factory()->create();

        $response = $this->actingAs($studentUser)
            ->get(route('students.report', $otherStudent));

        $response->assertStatus(403);
        $response->assertSeeText('You can only view your own report.');
    });

    test('user without role cannot view any report', function () {
        $user = User::factory()->create();
        // No role assigned

        $student = Student::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('students.report', $student));

        $response->assertStatus(403);
        $response->assertSeeText('You do not have permission to view student reports.');
    });

    test('unauthenticated user cannot view reports', function () {
        $student = Student::factory()->create();

        $response = $this->get(route('students.report', $student));

        $response->assertRedirect('/login');
    });

    test('report loads student enrollments', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $student = Student::factory()->create();

        // Create enrollments
        \App\Models\Enrollment::factory()->count(2)->create([
            'student_id' => $student->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('students.report', $student));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('shared/studentreport')
            ->has('student.enrollments', 2)
        );
    });

    test('report loads student guardians', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $student = Student::factory()->create();

        // Create guardian relationships
        $guardian1 = User::factory()->create();
        $guardian1->assignRole('guardian');
        GuardianStudent::create([
            'guardian_id' => $guardian1->id,
            'student_id' => $student->id,
            'relationship_type' => 'mother',
            'is_primary_contact' => true,
        ]);

        $guardian2 = User::factory()->create();
        $guardian2->assignRole('guardian');
        GuardianStudent::create([
            'guardian_id' => $guardian2->id,
            'student_id' => $student->id,
            'relationship_type' => 'father',
            'is_primary_contact' => false,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('students.report', $student));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('shared/studentreport')
            ->has('student.guardians', 2)
        );
    });

    test('guardian can view multiple children reports', function () {
        $guardian = User::factory()->create();
        $guardian->assignRole('guardian');

        // Create guardian model
        $guardianModel = Guardian::create([
            'user_id' => $guardian->id,
            'first_name' => 'Test',
            'last_name' => 'Guardian',
            'contact_number' => '09123456789',
            'address' => '123 Test St',
        ]);

        // Create two students
        $student1 = Student::factory()->create();
        $student2 = Student::factory()->create();

        // Link guardian to both students
        GuardianStudent::create([
            'guardian_id' => $guardian->id,
            'student_id' => $student1->id,
            'relationship_type' => 'mother',
            'is_primary_contact' => true,
        ]);

        GuardianStudent::create([
            'guardian_id' => $guardian->id,
            'student_id' => $student2->id,
            'relationship_type' => 'mother',
            'is_primary_contact' => true,
        ]);

        // Can view first child's report
        $response1 = $this->actingAs($guardian)
            ->get(route('students.report', $student1));

        $response1->assertStatus(200);
        $response1->assertInertia(fn (AssertableInertia $page) => $page
            ->component('shared/studentreport')
            ->has('student')
            ->where('student.id', $student1->id)
        );

        // Can view second child's report
        $response2 = $this->actingAs($guardian)
            ->get(route('students.report', $student2));

        $response2->assertStatus(200);
        $response2->assertInertia(fn (AssertableInertia $page) => $page
            ->component('shared/studentreport')
            ->has('student')
            ->where('student.id', $student2->id)
        );
    });

    test('non-existent student returns 404', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)
            ->get('/students/99999/report');

        $response->assertStatus(404);
    });
});

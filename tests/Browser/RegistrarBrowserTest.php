<?php

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

describe('Registrar Browser Tests - Dashboard', function () {
    test('registrar can view dashboard', function () {
        $registrar = User::factory()->registrar()->create([
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($registrar);

        visit('/registrar/dashboard')
            ->waitForText('Dashboard')
            ->assertSee('Dashboard');
    })->group('registrar-browser', 'critical');
});

describe('Registrar Browser Tests - Enrollment Management', function () {
    test('registrar can view enrollments list', function () {
        $registrar = User::factory()->registrar()->create([
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
        ]);

        $student = Student::factory()->create([
            'first_name' => 'John',
            'middle_name' => null,
            'last_name' => 'TestStudent',
        ]);

        Enrollment::factory()->create([
            'student_id' => $student->id,
            'status' => EnrollmentStatus::PENDING,
        ]);

        $this->actingAs($registrar);

        visit('/registrar/enrollments')
            ->waitForText('Enrollments')
            ->assertSee('John TestStudent');
    })->group('registrar-browser', 'critical');

    test('registrar can view enrollment details page directly', function () {
        $registrar = User::factory()->registrar()->create([
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
        ]);

        $student = Student::factory()->create([
            'first_name' => 'Jane',
            'middle_name' => null,
            'last_name' => 'DetailTest',
        ]);

        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'status' => EnrollmentStatus::PENDING,
        ]);

        $this->actingAs($registrar);

        visit("/registrar/enrollments/{$enrollment->id}")
            ->waitForText('Jane DetailTest')
            ->assertSee('Jane DetailTest');
    })->group('registrar-browser', 'critical');

    test('registrar can see enrollment status badge', function () {
        $registrar = User::factory()->registrar()->create([
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
        ]);

        $student = Student::factory()->create([
            'first_name' => 'Status',
            'middle_name' => null,
            'last_name' => 'BadgeTest',
        ]);

        Enrollment::factory()->create([
            'student_id' => $student->id,
            'status' => EnrollmentStatus::PENDING,
        ]);

        $this->actingAs($registrar);

        visit('/registrar/enrollments')
            ->waitForText('Enrollments')
            ->assertSee('pending'); // Status badge text
    })->group('registrar-browser', 'critical');
});

describe('Registrar Browser Tests - Student Management', function () {
    test('registrar can view students list page', function () {
        $registrar = User::factory()->registrar()->create([
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
        ]);

        $student = Student::factory()->create([
            'first_name' => 'Alice',
            'middle_name' => null,
            'last_name' => 'StudentList',
        ]);

        $this->actingAs($registrar);

        visit('/registrar/students')
            ->waitForText('Students')
            ->assertSee('Alice StudentList');
    })->group('registrar-browser', 'critical');

    test('registrar can view student details page directly', function () {
        $registrar = User::factory()->registrar()->create([
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
        ]);

        $student = Student::factory()->create([
            'first_name' => 'Bob',
            'middle_name' => null,
            'last_name' => 'StudentDetail',
        ]);

        $this->actingAs($registrar);

        visit("/registrar/students/{$student->id}")
            ->waitForText('Bob StudentDetail')
            ->assertSee('Bob StudentDetail');
    })->group('registrar-browser', 'critical');

    test('registrar can search students by name', function () {
        $registrar = User::factory()->registrar()->create([
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
        ]);

        Student::factory()->create([
            'first_name' => 'FindMe',
            'middle_name' => null,
            'last_name' => 'SearchTest',
        ]);

        Student::factory()->create([
            'first_name' => 'Other',
            'middle_name' => null,
            'last_name' => 'Student',
        ]);

        $this->actingAs($registrar);

        visit('/registrar/students')
            ->waitForText('Students')
            ->type('[placeholder="Filter by name..."]', 'FindMe')
            ->wait(1)
            ->assertSee('FindMe SearchTest')
            ->assertDontSee('Other Student');
    })->group('registrar-browser', 'critical');
});

describe('Registrar Browser Tests - Authorization', function () {
    test('registrar cannot access super admin routes', function () {
        $registrar = User::factory()->registrar()->create([
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($registrar);

        visit('/super-admin/dashboard')
            ->wait(1)
            // Should be redirected or see 403
            ->assertDontSee('Super Admin Dashboard');
    })->group('registrar-browser', 'critical');
});

describe('Registrar Browser Tests - Complete Workflows', function () {
    test('registrar can view enrollment details and see student information', function () {
        $registrar = User::factory()->registrar()->create([
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
        ]);

        $guardian = Guardian::factory()->create([
            'first_name' => 'Parent',
            'last_name' => 'Guardian',
        ]);

        $student = Student::factory()->create([
            'first_name' => 'Complete',
            'middle_name' => null,
            'last_name' => 'Workflow',
        ]);

        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'status' => EnrollmentStatus::PENDING,
        ]);

        $this->actingAs($registrar);

        visit("/registrar/enrollments/{$enrollment->id}")
            ->waitForText('Complete Workflow')
            ->assertSee('Complete Workflow')
            ->assertSee('Parent Guardian')
            ->assertSee('pending');
    })->group('registrar-browser', 'critical');

    test('registrar can view student with enrollment history', function () {
        $registrar = User::factory()->registrar()->create([
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
        ]);

        $student = Student::factory()->create([
            'first_name' => 'History',
            'middle_name' => null,
            'last_name' => 'Student',
        ]);

        Enrollment::factory()->count(2)->create([
            'student_id' => $student->id,
            'status' => EnrollmentStatus::ENROLLED,
        ]);

        $this->actingAs($registrar);

        visit("/registrar/students/{$student->id}")
            ->waitForText('History Student')
            ->assertSee('History Student')
            ->wait(1)
            // Should see enrollment information
            ->assertSee('Enrollment');
    })->group('registrar-browser', 'critical');
});

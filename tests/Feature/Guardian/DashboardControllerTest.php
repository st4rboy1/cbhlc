<?php

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\PaymentStatus;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles
    $guardianRole = \Spatie\Permission\Models\Role::create(['name' => 'guardian', 'guard_name' => 'web']);

    // Create guardian user and associated Guardian model
    $guardianModel = Guardian::factory()->create();
    $this->guardian = $guardianModel->user;
    $this->guardian->assignRole($guardianRole);
    $this->guardianModel = $guardianModel;

    // Create school year
    $this->schoolYear = SchoolYear::factory()->create([
        'name' => '2024-2025',
        'start_date' => now()->subMonths(2),
        'end_date' => now()->addMonths(10),
    ]);
});

test('guardian can view dashboard', function () {
    $response = $this->actingAs($this->guardian)
        ->get(route('guardian.dashboard'));

    $response->assertStatus(200)
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/dashboard')
            ->has('children')
            ->has('announcements')
            ->has('upcomingEvents')
            ->has('enrollmentStats')
        );
});

test('dashboard shows guardian children', function () {
    // Create students for the guardian
    $student1 = Student::factory()->create([
        'first_name' => 'John',
        'middle_name' => null,
        'last_name' => 'Doe',
        'grade_level' => GradeLevel::GRADE_1,
    ]);
    $student2 = Student::factory()->create([
        'first_name' => 'Jane',
        'middle_name' => null,
        'last_name' => 'Doe',
        'grade_level' => GradeLevel::GRADE_2,
    ]);

    $this->guardianModel->children()->attach([$student1->id, $student2->id]);

    $response = $this->actingAs($this->guardian)
        ->get(route('guardian.dashboard'));

    $response->assertStatus(200)
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/dashboard')
            ->has('children', 2)
            ->where('children.0.name', 'John Doe')
            ->where('children.1.name', 'Jane Doe')
        );
});

test('dashboard shows recent enrollments', function () {
    // Create student and enrollment
    $student = Student::factory()->create(['first_name' => 'John', 'middle_name' => null, 'last_name' => 'Doe']);
    $this->guardianModel->children()->attach($student->id);

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'school_year_id' => $this->schoolYear->id,
        'grade_level' => GradeLevel::GRADE_1,
        'status' => EnrollmentStatus::ENROLLED,
        'payment_status' => PaymentStatus::PAID,
    ]);

    $response = $this->actingAs($this->guardian)
        ->get(route('guardian.dashboard'));

    $response->assertStatus(200)
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/dashboard')
            ->has('enrollments', 1)
            ->where('enrollments.0.student_name', 'John Doe')
            ->where('enrollments.0.status', EnrollmentStatus::ENROLLED->value)
        );
});

test('dashboard shows enrollment statistics', function () {
    // Create students
    $student1 = Student::factory()->create();
    $student2 = Student::factory()->create();
    $this->guardianModel->children()->attach([$student1->id, $student2->id]);

    // Create enrollments with different statuses
    Enrollment::factory()->create([
        'student_id' => $student1->id,
        'school_year_id' => $this->schoolYear->id,
        'status' => EnrollmentStatus::PENDING,
    ]);

    Enrollment::factory()->create([
        'student_id' => $student2->id,
        'school_year_id' => $this->schoolYear->id,
        'status' => EnrollmentStatus::ENROLLED,
    ]);

    $response = $this->actingAs($this->guardian)
        ->get(route('guardian.dashboard'));

    $response->assertStatus(200)
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/dashboard')
            ->where('enrollmentStats.total', 2)
            ->where('enrollmentStats.pending', 1)
            ->where('enrollmentStats.enrolled', 1)
        );
});

test('dashboard shows announcements', function () {
    $response = $this->actingAs($this->guardian)
        ->get(route('guardian.dashboard'));

    $response->assertStatus(200)
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/dashboard')
            ->has('announcements')
            ->where('announcements.0.title', 'Welcome to CBHLC Online Enrollment')
        );
});

test('dashboard shows upcoming events', function () {
    $response = $this->actingAs($this->guardian)
        ->get(route('guardian.dashboard'));

    $response->assertStatus(200)
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/dashboard')
            ->has('upcomingEvents', 3)
        );
});

test('dashboard only shows guardian own children', function () {
    // Create student for this guardian
    $myStudent = Student::factory()->create(['first_name' => 'John']);
    $this->guardianModel->children()->attach($myStudent->id);

    // Create another guardian with their student
    $otherGuardian = Guardian::factory()->create();
    $otherStudent = Student::factory()->create(['first_name' => 'Other']);
    $otherGuardian->children()->attach($otherStudent->id);

    $response = $this->actingAs($this->guardian)
        ->get(route('guardian.dashboard'));

    $response->assertStatus(200)
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/dashboard')
            ->has('children', 1)
            ->where('children.0.name', fn ($name) => str_contains($name, 'John'))
        );
});

test('unauthenticated user cannot access dashboard', function () {
    $response = $this->get(route('guardian.dashboard'));

    $response->assertStatus(302)
        ->assertRedirect(route('login'));
});

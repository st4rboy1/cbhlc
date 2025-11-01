<?php

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\EnrollmentPeriod;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles
    Role::create(['name' => 'administrator']);
    Role::create(['name' => 'guardian']);

    // Create administrator user
    $this->admin = User::factory()->create();
    $this->admin->assignRole('administrator');

    // Create school year
    $this->schoolYear = SchoolYear::factory()->create([
        'start_year' => 2024,
        'end_year' => 2025,
        'status' => 'active',
    ]);

    // Create enrollment period
    $this->enrollmentPeriod = EnrollmentPeriod::factory()->create([
        'school_year_id' => $this->schoolYear->id,
        'status' => 'active',
    ]);

    // Create grade level
    $this->gradeLevel = GradeLevel::factory()->create(['name' => 'Grade 1']);
});

test('admin can access reports dashboard', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.reports.index'));

    $response->assertStatus(200);
})->skip('Frontend pages not yet implemented');

test('admin can get enrollment statistics', function () {
    // Create enrollments with different statuses
    Enrollment::factory()->count(3)->create([
        'enrollment_period_id' => $this->enrollmentPeriod->id,
        'grade_level_id' => $this->gradeLevel->id,
        'status' => EnrollmentStatus::APPROVED,
    ]);

    Enrollment::factory()->count(2)->create([
        'enrollment_period_id' => $this->enrollmentPeriod->id,
        'grade_level_id' => $this->gradeLevel->id,
        'status' => EnrollmentStatus::PENDING,
    ]);

    $response = $this->actingAs($this->admin)->getJson(route('admin.reports.enrollment-statistics'));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'summary' => ['total', 'pending', 'approved', 'rejected', 'withdrawn'],
            'byGradeLevel',
            'trend',
            'filters',
        ]);

    expect($response->json('summary.total'))->toBe(5)
        ->and($response->json('summary.approved'))->toBe(3)
        ->and($response->json('summary.pending'))->toBe(2);
});

test('admin can filter enrollment statistics by school year', function () {
    $anotherYear = SchoolYear::factory()->create();
    $anotherPeriod = EnrollmentPeriod::factory()->create(['school_year_id' => $anotherYear->id]);

    Enrollment::factory()->count(3)->create(['enrollment_period_id' => $this->enrollmentPeriod->id]);
    Enrollment::factory()->count(2)->create(['enrollment_period_id' => $anotherPeriod->id]);

    $response = $this->actingAs($this->admin)->getJson(
        route('admin.reports.enrollment-statistics', ['school_year_id' => $this->schoolYear->id])
    );

    $response->assertStatus(200);
    expect($response->json('summary.total'))->toBe(3);
});

test('admin can filter enrollment statistics by grade level', function () {
    $anotherGrade = GradeLevel::factory()->create(['name' => 'Grade 2']);

    Enrollment::factory()->count(3)->create([
        'enrollment_period_id' => $this->enrollmentPeriod->id,
        'grade_level_id' => $this->gradeLevel->id,
    ]);

    Enrollment::factory()->count(2)->create([
        'enrollment_period_id' => $this->enrollmentPeriod->id,
        'grade_level_id' => $anotherGrade->id,
    ]);

    $response = $this->actingAs($this->admin)->getJson(
        route('admin.reports.enrollment-statistics', ['grade_level_id' => $this->gradeLevel->id])
    );

    $response->assertStatus(200);
    expect($response->json('summary.total'))->toBe(3);
});

test('admin can filter enrollment statistics by status', function () {
    Enrollment::factory()->count(3)->create([
        'enrollment_period_id' => $this->enrollmentPeriod->id,
        'status' => EnrollmentStatus::APPROVED,
    ]);

    Enrollment::factory()->count(2)->create([
        'enrollment_period_id' => $this->enrollmentPeriod->id,
        'status' => EnrollmentStatus::PENDING,
    ]);

    $response = $this->actingAs($this->admin)->getJson(
        route('admin.reports.enrollment-statistics', ['status' => 'approved'])
    );

    $response->assertStatus(200);
    expect($response->json('summary.total'))->toBe(3);
});

test('admin can filter enrollment statistics by date range', function () {
    $oldEnrollments = Enrollment::factory()->count(2)->create([
        'enrollment_period_id' => $this->enrollmentPeriod->id,
        'created_at' => now()->subDays(10),
    ]);

    $recentEnrollments = Enrollment::factory()->count(3)->create([
        'enrollment_period_id' => $this->enrollmentPeriod->id,
        'created_at' => now()->subDays(2),
    ]);

    $response = $this->actingAs($this->admin)->getJson(
        route('admin.reports.enrollment-statistics', [
            'start_date' => now()->subDays(5)->toDateString(),
        ])
    );

    $response->assertStatus(200);
    expect($response->json('summary.total'))->toBe(3);
});

test('admin can get student demographics', function () {
    // Create students with different demographics
    $maleStudent = Student::factory()->create(['gender' => 'male']);
    $femaleStudent = Student::factory()->create(['gender' => 'female']);

    Enrollment::factory()->create([
        'student_id' => $maleStudent->id,
        'enrollment_period_id' => $this->enrollmentPeriod->id,
    ]);

    Enrollment::factory()->create([
        'student_id' => $femaleStudent->id,
        'enrollment_period_id' => $this->enrollmentPeriod->id,
    ]);

    $response = $this->actingAs($this->admin)->getJson(route('admin.reports.student-demographics'));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'total',
            'byGender',
            'byAge',
            'byReligion',
            'byNationality',
            'filters',
        ]);

    expect($response->json('total'))->toBe(2);
});

test('admin can filter student demographics by school year', function () {
    $student = Student::factory()->create();
    Enrollment::factory()->create([
        'student_id' => $student->id,
        'enrollment_period_id' => $this->enrollmentPeriod->id,
    ]);

    $response = $this->actingAs($this->admin)->getJson(
        route('admin.reports.student-demographics', ['school_year_id' => $this->schoolYear->id])
    );

    $response->assertStatus(200);
    expect($response->json('total'))->toBe(1);
});

test('admin can get class roster', function () {
    $students = Student::factory()->count(3)->create();

    foreach ($students as $student) {
        Enrollment::factory()->create([
            'student_id' => $student->id,
            'enrollment_period_id' => $this->enrollmentPeriod->id,
            'grade_level_id' => $this->gradeLevel->id,
            'status' => EnrollmentStatus::APPROVED,
        ]);
    }

    $response = $this->actingAs($this->admin)->getJson(
        route('admin.reports.class-roster', [
            'school_year_id' => $this->schoolYear->id,
            'grade_level_id' => $this->gradeLevel->id,
        ])
    );

    $response->assertStatus(200)
        ->assertJsonStructure([
            'school_year' => ['id', 'name'],
            'grade_level' => ['id', 'name'],
            'total_students',
            'roster',
            'filters',
        ]);

    expect($response->json('total_students'))->toBe(3)
        ->and($response->json('roster'))->toHaveCount(3);
});

test('class roster defaults to approved enrollments only', function () {
    $approvedStudent = Student::factory()->create();
    $pendingStudent = Student::factory()->create();

    Enrollment::factory()->create([
        'student_id' => $approvedStudent->id,
        'enrollment_period_id' => $this->enrollmentPeriod->id,
        'grade_level_id' => $this->gradeLevel->id,
        'status' => EnrollmentStatus::APPROVED,
    ]);

    Enrollment::factory()->create([
        'student_id' => $pendingStudent->id,
        'enrollment_period_id' => $this->enrollmentPeriod->id,
        'grade_level_id' => $this->gradeLevel->id,
        'status' => EnrollmentStatus::PENDING,
    ]);

    $response = $this->actingAs($this->admin)->getJson(
        route('admin.reports.class-roster', [
            'school_year_id' => $this->schoolYear->id,
            'grade_level_id' => $this->gradeLevel->id,
        ])
    );

    $response->assertStatus(200);
    expect($response->json('total_students'))->toBe(1);
});

test('class roster can include other statuses when specified', function () {
    $students = Student::factory()->count(2)->create();

    foreach ($students as $student) {
        Enrollment::factory()->create([
            'student_id' => $student->id,
            'enrollment_period_id' => $this->enrollmentPeriod->id,
            'grade_level_id' => $this->gradeLevel->id,
            'status' => EnrollmentStatus::PENDING,
        ]);
    }

    $response = $this->actingAs($this->admin)->getJson(
        route('admin.reports.class-roster', [
            'school_year_id' => $this->schoolYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'status' => 'pending',
        ])
    );

    $response->assertStatus(200);
    expect($response->json('total_students'))->toBe(2);
});

test('class roster requires school year and grade level', function () {
    $response = $this->actingAs($this->admin)->getJson(route('admin.reports.class-roster'));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['school_year_id', 'grade_level_id']);
});

test('admin can get filter options', function () {
    $response = $this->actingAs($this->admin)->getJson(route('admin.reports.filter-options'));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'schoolYears',
            'gradeLevels',
            'enrollmentPeriods',
        ]);

    expect($response->json('schoolYears'))->toHaveCount(1)
        ->and($response->json('gradeLevels'))->toHaveCount(1)
        ->and($response->json('enrollmentPeriods'))->toHaveCount(1);
});

test('non-admin cannot access report endpoints', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');

    $response = $this->actingAs($user)->getJson(route('admin.reports.enrollment-statistics'));
    $response->assertStatus(403);

    $response = $this->actingAs($user)->getJson(route('admin.reports.student-demographics'));
    $response->assertStatus(403);

    $response = $this->actingAs($user)->getJson(
        route('admin.reports.class-roster', [
            'school_year_id' => $this->schoolYear->id,
            'grade_level_id' => $this->gradeLevel->id,
        ])
    );
    $response->assertStatus(403);

    $response = $this->actingAs($user)->getJson(route('admin.reports.filter-options'));
    $response->assertStatus(403);
});

test('enrollment statistics validates date range', function () {
    $response = $this->actingAs($this->admin)->getJson(
        route('admin.reports.enrollment-statistics', [
            'start_date' => '2024-01-10',
            'end_date' => '2024-01-05', // End before start
        ])
    );

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['end_date']);
});

test('enrollment statistics validates foreign keys', function () {
    $response = $this->actingAs($this->admin)->getJson(
        route('admin.reports.enrollment-statistics', [
            'school_year_id' => 999,
            'grade_level_id' => 999,
        ])
    );

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['school_year_id', 'grade_level_id']);
});

test('roster is ordered by student last name', function () {
    $john = Student::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
    $jane = Student::factory()->create(['first_name' => 'Jane', 'last_name' => 'Apple']);

    Enrollment::factory()->create([
        'student_id' => $john->id,
        'enrollment_period_id' => $this->enrollmentPeriod->id,
        'grade_level_id' => $this->gradeLevel->id,
        'status' => EnrollmentStatus::APPROVED,
    ]);

    Enrollment::factory()->create([
        'student_id' => $jane->id,
        'enrollment_period_id' => $this->enrollmentPeriod->id,
        'grade_level_id' => $this->gradeLevel->id,
        'status' => EnrollmentStatus::APPROVED,
    ]);

    $response = $this->actingAs($this->admin)->getJson(
        route('admin.reports.class-roster', [
            'school_year_id' => $this->schoolYear->id,
            'grade_level_id' => $this->gradeLevel->id,
        ])
    );

    $response->assertStatus(200);
    $roster = $response->json('roster');

    expect($roster[0]['last_name'])->toBe('Apple')
        ->and($roster[1]['last_name'])->toBe('Doe');
});

<?php

use App\Enums\GradeLevel;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\GuardianStudent;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    // Create registrar user
    $this->registrar = User::factory()->create();
    $this->registrar->assignRole('registrar');

    // Create guardian user for relationship testing
    $this->guardian = User::factory()->create();
    $this->guardian->assignRole('guardian');

    // Create guardian model
    $this->guardianModel = Guardian::create([
        'user_id' => $this->guardian->id,
        'first_name' => 'Test',
        'last_name' => 'Guardian',
        'contact_number' => '09123456789',
        'address' => '123 Test St',
    ]);
});

describe('Registrar StudentController', function () {
    test('registrar can view students index', function () {
        // Create students
        $students = Student::factory()->count(3)->create();

        // Create enrollments for the students
        foreach ($students as $student) {
            Enrollment::factory()->create([
                'student_id' => $student->id,
                'status' => \App\Enums\EnrollmentStatus::ENROLLED,
            ]);
        }

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.students.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('registrar/students/index')
            ->has('students.data', 3)
            ->has('filters')
            ->has('gradeLevels')
        );
    });

    test('students index shows paginated results', function () {
        // Create 20 students for pagination
        $students = Student::factory()->count(20)->create();

        // Create enrollments for the students
        foreach ($students as $student) {
            Enrollment::factory()->create([
                'student_id' => $student->id,
                'status' => \App\Enums\EnrollmentStatus::ENROLLED,
            ]);
        }

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.students.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('registrar/students/index')
            ->has('students.data', 20) // Default pagination is 20
            ->has('students.links')
        );
    });

    test('students index search by name works', function () {
        $student1 = Student::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'student_id' => 'STU-JOHN-001',
        ]);

        Enrollment::factory()->create([
            'student_id' => $student1->id,
            'status' => \App\Enums\EnrollmentStatus::ENROLLED,
        ]);

        $student2 = Student::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'student_id' => 'STU-JANE-001',
        ]);

        Enrollment::factory()->create([
            'student_id' => $student2->id,
            'status' => \App\Enums\EnrollmentStatus::ENROLLED,
        ]);

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.students.index', ['search' => 'John']));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('registrar/students/index')
            ->has('students.data', 1)
            ->where('students.data.0.first_name', 'John')
        );
    });

    test('students index search by student ID works', function () {
        $student1 = Student::factory()->create([
            'student_id' => 'STU-001',
        ]);

        Enrollment::factory()->create([
            'student_id' => $student1->id,
            'status' => \App\Enums\EnrollmentStatus::ENROLLED,
        ]);

        $student2 = Student::factory()->create([
            'student_id' => 'STU-002',
        ]);

        Enrollment::factory()->create([
            'student_id' => $student2->id,
            'status' => \App\Enums\EnrollmentStatus::ENROLLED,
        ]);

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.students.index', ['search' => 'STU-001']));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('registrar/students/index')
            ->has('students.data', 1)
            ->where('students.data.0.student_id', 'STU-001')
        );
    });

    test('students index filter by grade level works', function () {
        $student1 = Student::factory()->create([
            'grade_level' => GradeLevel::GRADE_1,
        ]);

        Enrollment::factory()->create([
            'student_id' => $student1->id,
            'status' => \App\Enums\EnrollmentStatus::ENROLLED,
        ]);

        $student2 = Student::factory()->create([
            'grade_level' => GradeLevel::GRADE_2,
        ]);

        Enrollment::factory()->create([
            'student_id' => $student2->id,
            'status' => \App\Enums\EnrollmentStatus::ENROLLED,
        ]);

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.students.index', ['grade_level' => GradeLevel::GRADE_1->value]));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('registrar/students/index')
            ->has('students.data', 1)
            ->where('students.data.0.grade_level', GradeLevel::GRADE_1->value)
        );
    });

    test('students index filter by section works', function () {
        $student1 = Student::factory()->create([
            'section' => 'A',
        ]);

        Enrollment::factory()->create([
            'student_id' => $student1->id,
            'status' => \App\Enums\EnrollmentStatus::ENROLLED,
        ]);

        $student2 = Student::factory()->create([
            'section' => 'B',
        ]);

        Enrollment::factory()->create([
            'student_id' => $student2->id,
            'status' => \App\Enums\EnrollmentStatus::ENROLLED,
        ]);

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.students.index', ['section' => 'A']));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('registrar/students/index')
            ->has('students.data', 1)
            ->where('students.data.0.section', 'A')
        );
    });

    test('registrar can view student details', function () {
        $student = Student::factory()->create([
            'first_name' => 'John',
            'middle_name' => 'Michael',
            'last_name' => 'Doe',
        ]);

        // Create guardian relationship
        GuardianStudent::create([
            'guardian_id' => $this->guardianModel->id,
            'student_id' => $student->id,
            'relationship_type' => 'mother',
            'is_primary_contact' => true,
        ]);

        // Create enrollment
        Enrollment::factory()->create([
            'student_id' => $student->id,
            'guardian_id' => $this->guardianModel->id,
        ]);

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.students.show', $student));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('registrar/students/show')
            ->has('student')
            ->where('student.first_name', 'John')
            ->where('student.middle_name', 'Michael')
            ->where('student.last_name', 'Doe')
            ->has('student.enrollments', 1)
            ->has('student.guardians', 1)
        );
    });

    test('student show displays guardian relationships', function () {
        $student = Student::factory()->create();

        // Create multiple guardian relationships
        $guardian1 = Guardian::factory()->create();
        GuardianStudent::create([
            'guardian_id' => $guardian1->id,
            'student_id' => $student->id,
            'relationship_type' => 'mother',
            'is_primary_contact' => true,
        ]);

        $guardian2 = Guardian::factory()->create();
        GuardianStudent::create([
            'guardian_id' => $guardian2->id,
            'student_id' => $student->id,
            'relationship_type' => 'father',
            'is_primary_contact' => false,
        ]);

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.students.show', $student));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('registrar/students/show')
            ->has('student.guardians', 2)
            ->where('student.guardians.0.relationship_type', 'mother')
            ->where('student.guardians.0.is_primary_contact', true)
        );
    });

    test('registrar can access create student form', function () {
        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.students.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('registrar/students/create')
            ->has('gradelevels')
            ->has('guardians')
        );
    });

    test('registrar can create new student', function () {
        $studentData = [
            'first_name' => 'Jane',
            'middle_name' => 'Marie',
            'last_name' => 'Smith',
            'birthdate' => '2010-05-15',
            'gender' => 'Female',
            'address' => '456 Oak St',
            'contact_number' => '09876543210',
            'email' => 'jane.smith@example.com',
            'grade_level' => GradeLevel::GRADE_3->value,
            'section' => 'B',
        ];

        $response = $this->actingAs($this->registrar)
            ->post(route('registrar.students.store'), $studentData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);

        // Check that student_id was generated
        $student = Student::where('email', 'jane.smith@example.com')->first();
        expect($student->student_id)->toStartWith('CBHLC');
    });

    test('student creation validates required fields', function () {
        $response = $this->actingAs($this->registrar)
            ->post(route('registrar.students.store'), []);

        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'birthdate',
            'gender',
            'address',
        ]);
    });

    test('student creation validates email uniqueness', function () {
        Student::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $studentData = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'birthdate' => '2010-01-01',
            'gender' => 'Male',
            'address' => 'Test Address',
            'email' => 'existing@example.com',
        ];

        $response = $this->actingAs($this->registrar)
            ->post(route('registrar.students.store'), $studentData);

        $response->assertSessionHasErrors(['email']);
    });

    test('registrar can access edit student form', function () {
        $student = Student::factory()->create();

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.students.edit', $student));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('registrar/students/edit')
            ->has('student')
            ->has('gradeLevels')
        );
    });

    test('registrar can update student', function () {
        $student = Student::factory()->create([
            'first_name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $updateData = [
            'first_name' => 'New Name',
            'middle_name' => 'Middle',
            'last_name' => $student->last_name,
            'birthdate' => $student->birthdate->format('Y-m-d'),
            'gender' => is_string($student->gender) ? $student->gender : $student->gender->value,
            'address' => $student->address,
            'contact_number' => $student->contact_number,
            'email' => 'new@example.com',
            'grade_level' => is_string($student->grade_level) ? $student->grade_level : $student->grade_level->value,
            'section' => $student->section,
        ];

        $response = $this->actingAs($this->registrar)
            ->put(route('registrar.students.update', $student), $updateData);

        $response->assertRedirect(route('registrar.students.show', $student));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'first_name' => 'New Name',
        ]);
    });

    test('update validates email uniqueness excluding current student', function () {
        $student1 = Student::factory()->create([
            'email' => 'student1@example.com',
        ]);

        $student2 = Student::factory()->create([
            'email' => 'student2@example.com',
        ]);

        // Try to update student1 with student2's email
        $updateData = [
            'first_name' => $student1->first_name,
            'last_name' => $student1->last_name,
            'birthdate' => $student1->birthdate->format('Y-m-d'),
            'gender' => is_string($student1->gender) ? $student1->gender : $student1->gender->value,
            'address' => $student1->address,
            'email' => 'student2@example.com',
        ];

        $response = $this->actingAs($this->registrar)
            ->put(route('registrar.students.update', $student1), $updateData);

        $response->assertSessionHasErrors(['email']);
    });

    test('registrar can delete student without enrollments', function () {
        $student = Student::factory()->create();

        $response = $this->actingAs($this->registrar)
            ->delete(route('registrar.students.destroy', $student));

        $response->assertRedirect(route('registrar.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('students', [
            'id' => $student->id,
        ]);
    });

    test('cannot delete student with enrollments', function () {
        $student = Student::factory()->create();

        // Create enrollment
        Enrollment::factory()->create([
            'student_id' => $student->id,
            'guardian_id' => $this->guardianModel->id,
        ]);

        $response = $this->actingAs($this->registrar)
            ->delete(route('registrar.students.destroy', $student));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Cannot delete student with enrollment records.');

        // Student should still exist
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
        ]);
    });

    test('export functionality returns TODO message', function () {
        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.students.export'));

        $response->assertStatus(302);
        $response->assertSessionHas('info', 'Export functionality coming soon.');
    });

    test('non-registrar users cannot access student management', function () {
        $regularUser = User::factory()->create();

        $response = $this->actingAs($regularUser)
            ->get(route('registrar.students.index'));

        $response->assertStatus(403);
    });

    test('students index handles empty results gracefully', function () {
        // No students created

        $response = $this->actingAs($this->registrar)
            ->get(route('registrar.students.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('registrar/students/index')
            ->has('students.data', 0)
        );
    });
});

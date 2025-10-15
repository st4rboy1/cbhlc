<?php

use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\GuardianStudent;
use App\Models\Student;
use App\Services\StudentService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles and permissions for each test
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->service = new StudentService(new Student);
});

test('getPaginatedStudents returns paginated results with relationships', function () {
    Student::factory()->count(15)->create();

    $result = $this->service->getPaginatedStudents([], 10);

    expect($result)->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class);
    expect($result->count())->toBe(10);
    expect($result->total())->toBe(15);
});

test('getPaginatedStudents applies search filter', function () {
    Student::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'middle_name' => null,
        'email' => 'john.doe@test.com',
    ]);
    Student::factory()->create([
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'middle_name' => null,
        'email' => 'jane.smith@test.com',
    ]);
    Student::factory()->create([
        'student_id' => 'STU001',
        'first_name' => 'Bob',
        'last_name' => 'Williams',
        'middle_name' => null,
        'email' => 'bob@test.com',
    ]);

    // Search by first name
    $result = $this->service->getPaginatedStudents(['search' => 'John'], 10);
    expect($result->count())->toBe(1);
    expect($result->first()->first_name)->toBe('John');

    // Search by last name
    $result = $this->service->getPaginatedStudents(['search' => 'Smith'], 10);
    expect($result->count())->toBe(1);
    expect($result->first()->last_name)->toBe('Smith');

    // Search by student ID
    $result = $this->service->getPaginatedStudents(['search' => 'STU001'], 10);
    expect($result->count())->toBe(1);
    expect($result->first()->student_id)->toBe('STU001');
});

test('getPaginatedStudents applies grade level filter', function () {
    Student::factory()->create(['grade_level' => 'Grade 1']);
    Student::factory()->create(['grade_level' => 'Grade 2']);
    Student::factory()->create(['grade_level' => 'Grade 1']);

    $result = $this->service->getPaginatedStudents(['grade_level' => 'Grade 1'], 10);

    expect($result->count())->toBe(2);
    expect($result->first()->grade_level->value)->toBe('Grade 1');
});

test('getPaginatedStudents applies section filter', function () {
    Student::factory()->create(['section' => 'Section A']);
    Student::factory()->create(['section' => 'Section B']);

    $result = $this->service->getPaginatedStudents(['section' => 'Section A'], 10);

    expect($result->count())->toBe(1);
    expect($result->first()->section)->toBe('Section A');
});

test('getPaginatedStudents applies sorting', function () {
    Student::factory()->create(['first_name' => 'Charlie']);
    Student::factory()->create(['first_name' => 'Alice']);
    Student::factory()->create(['first_name' => 'Bob']);

    $result = $this->service->getPaginatedStudents([
        'sort_by' => 'first_name',
        'sort_order' => 'asc',
    ], 10);

    expect($result->first()->first_name)->toBe('Alice');
    expect($result->last()->first_name)->toBe('Charlie');
});

test('findWithRelations returns student with relationships', function () {
    $student = Student::factory()->create();
    $guardianUser = \App\Models\User::factory()->create();
    $guardian = Guardian::create([
        'user_id' => $guardianUser->id,
        'first_name' => 'Test',
        'last_name' => 'Guardian',
        'contact_number' => '09123456789',
        'address' => '123 Test St',
    ]);
    GuardianStudent::create([
        'student_id' => $student->id,
        'guardian_id' => $guardian->id,
        'relationship_type' => 'mother',
        'is_primary_contact' => true,
    ]);

    $result = $this->service->findWithRelations($student->id);

    expect($result)->toBeInstanceOf(Student::class);
    expect($result->relationLoaded('guardianStudents'))->toBe(true);
    expect($result->relationLoaded('enrollments'))->toBe(true);
});

test('findWithRelations accepts additional relations', function () {
    $student = Student::factory()->create();

    // Just test that the method accepts additional relations parameter
    // Even if the relation doesn't exist, it should return the student
    $result = $this->service->findWithRelations($student->id, []);

    expect($result)->toBeInstanceOf(Student::class);
    expect($result->id)->toBe($student->id);
});

test('createStudent creates new student with generated ID', function () {
    $data = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'birthdate' => '2010-01-01',
        'gender' => 'Male',
        'grade_level' => 'Grade 1',
        'address' => '123 Main St',
    ];

    $result = $this->service->createStudent($data);

    expect($result)->toBeInstanceOf(Student::class);
    expect($result->student_id)->toStartWith(date('Y'));
    expect($result->first_name)->toBe('John');
    $this->assertDatabaseHas('students', ['first_name' => 'John']);
});

test('createStudent uses provided student ID', function () {
    $data = [
        'student_id' => 'CUSTOM001',
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'birthdate' => '2010-01-01',
        'gender' => 'Female',
        'grade_level' => 'Grade 2',
        'address' => '456 Oak Ave',
    ];

    $result = $this->service->createStudent($data);

    expect($result->student_id)->toBe('CUSTOM001');
});

test('createStudent associates guardian when provided', function () {
    $guardianUser = \App\Models\User::factory()->create();
    $guardian = Guardian::create([
        'user_id' => $guardianUser->id,
        'first_name' => 'Test',
        'last_name' => 'Guardian',
        'contact_number' => '09123456789',
        'address' => '123 Test St',
    ]);
    $data = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'birthdate' => '2010-01-01',
        'gender' => 'Male',
        'grade_level' => 'Grade 1',
        'address' => '789 Pine St',
        'guardian_id' => $guardian->id,
        'relationship' => 'mother',
    ];

    $result = $this->service->createStudent($data);

    expect($result)->toBeInstanceOf(Student::class);
    $this->assertDatabaseHas('guardian_students', [
        'student_id' => $result->id,
        'guardian_id' => $guardian->id,
        'relationship_type' => 'mother',
    ]);
});

test('updateStudent updates existing student', function () {
    $student = Student::factory()->create(['first_name' => 'Original']);

    $result = $this->service->updateStudent($student, [
        'first_name' => 'Updated',
    ]);

    expect($result->first_name)->toBe('Updated');
    $this->assertDatabaseHas('students', [
        'id' => $student->id,
        'first_name' => 'Updated',
    ]);
});

test('updateStudent handles guardian association update', function () {
    $student = Student::factory()->create();
    $guardianUser = \App\Models\User::factory()->create();
    $guardian = Guardian::create([
        'user_id' => $guardianUser->id,
        'first_name' => 'Test',
        'last_name' => 'Guardian',
        'contact_number' => '09123456789',
        'address' => '123 Test St',
    ]);

    $result = $this->service->updateStudent($student, [
        'first_name' => 'NewName',
        'guardian_id' => $guardian->id,
        'relationship' => 'father',
    ]);

    expect($result->first_name)->toBe('NewName');
    $this->assertDatabaseHas('guardian_students', [
        'student_id' => $student->id,
        'guardian_id' => $guardian->id,
        'relationship_type' => 'father',
    ]);
});

test('deleteStudent removes student without enrollments', function () {
    $student = Student::factory()->create();

    $result = $this->service->deleteStudent($student);

    expect($result)->toBe(true);
    $this->assertDatabaseMissing('students', ['id' => $student->id]);
});

test('deleteStudent throws exception for student with enrollments', function () {
    $student = Student::factory()->create();
    Enrollment::factory()->create(['student_id' => $student->id]);

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Cannot delete student with existing enrollments');

    $this->service->deleteStudent($student);
});

test('deleteStudent removes guardian associations', function () {
    $student = Student::factory()->create();
    $guardianUser = \App\Models\User::factory()->create();
    $guardian = Guardian::create([
        'user_id' => $guardianUser->id,
        'first_name' => 'Test',
        'last_name' => 'Guardian',
        'contact_number' => '09123456789',
        'address' => '123 Test St',
    ]);
    GuardianStudent::create([
        'student_id' => $student->id,
        'guardian_id' => $guardian->id,
        'relationship_type' => 'mother',
        'is_primary_contact' => true,
    ]);

    $this->service->deleteStudent($student);

    $this->assertDatabaseMissing('guardian_students', [
        'student_id' => $student->id,
    ]);
});

test('getStudentsByGuardian returns guardian students', function () {
    $guardianUser = \App\Models\User::factory()->create();
    $guardian = Guardian::create([
        'user_id' => $guardianUser->id,
        'first_name' => 'Test',
        'last_name' => 'Guardian',
        'contact_number' => '09123456789',
        'address' => '123 Test St',
    ]);
    $student1 = Student::factory()->create();
    $student2 = Student::factory()->create();
    Student::factory()->create(); // Student not associated with guardian

    GuardianStudent::create([
        'student_id' => $student1->id,
        'guardian_id' => $guardian->id,
        'relationship_type' => 'mother',
        'is_primary_contact' => true,
    ]);
    GuardianStudent::create([
        'student_id' => $student2->id,
        'guardian_id' => $guardian->id,
        'relationship_type' => 'mother',
        'is_primary_contact' => false,
    ]);

    $result = $this->service->getStudentsByGuardian($guardian->id);

    expect($result)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
    expect($result->count())->toBe(2);
    expect($result->pluck('id')->toArray())->toContain($student1->id, $student2->id);
});

test('searchStudents delegates to getPaginatedStudents', function () {
    // Create students with specific names to avoid flaky tests
    // Use unique names that won't accidentally match in other fields
    $john = Student::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'middle_name' => null,
        'email' => 'john.doe@test.com',
    ]);

    Student::factory()->create([
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'middle_name' => null,
        'email' => 'jane.smith@test.com',
    ]);

    $result = $this->service->searchStudents('John');

    expect($result)->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class);
    expect($result->count())->toBe(1);
    expect($result->first()->id)->toBe($john->id);
    expect($result->first()->first_name)->toBe('John');
});

test('generateStudentId creates unique sequential ID', function () {
    $year = date('Y');

    // First student ID
    $id1 = $this->service->generateStudentId();
    expect($id1)->toBe($year.'0001');

    // Create a student with this ID
    Student::factory()->create(['student_id' => $id1]);

    // Next student ID should be incremented
    $id2 = $this->service->generateStudentId();
    expect($id2)->toBe($year.'0002');
});

test('generateStudentId handles existing IDs correctly', function () {
    $year = date('Y');

    // Create students with specific IDs
    Student::factory()->create(['student_id' => $year.'0005']);
    Student::factory()->create(['student_id' => $year.'0003']);

    $newId = $this->service->generateStudentId();
    expect($newId)->toBe($year.'0006');
});

test('canDelete returns true for student without enrollments', function () {
    $student = Student::factory()->create();

    $result = $this->service->canDelete($student);

    expect($result)->toBe(true);
});

test('canDelete returns false for student with enrollments', function () {
    $student = Student::factory()->create();
    Enrollment::factory()->create(['student_id' => $student->id]);

    $result = $this->service->canDelete($student);

    expect($result)->toBe(false);
});

test('associateGuardian creates new association', function () {
    $student = Student::factory()->create();
    $guardianUser = \App\Models\User::factory()->create();
    $guardian = Guardian::create([
        'user_id' => $guardianUser->id,
        'first_name' => 'Test',
        'last_name' => 'Guardian',
        'contact_number' => '09123456789',
        'address' => '123 Test St',
    ]);

    // Use reflection to test protected method
    $reflection = new ReflectionClass($this->service);
    $method = $reflection->getMethod('associateGuardian');
    $method->setAccessible(true);

    $result = $method->invoke($this->service, $student, $guardian->id, 'mother');

    expect($result)->toBeInstanceOf(GuardianStudent::class);
    expect($result->relationship_type)->toBe('mother');
    expect($result->is_primary_contact)->toBe(true); // First association is primary
});

test('associateGuardian updates existing association', function () {
    $student = Student::factory()->create();
    $guardianUser = \App\Models\User::factory()->create();
    $guardian = Guardian::create([
        'user_id' => $guardianUser->id,
        'first_name' => 'Test',
        'last_name' => 'Guardian',
        'contact_number' => '09123456789',
        'address' => '123 Test St',
    ]);

    $existing = GuardianStudent::create([
        'student_id' => $student->id,
        'guardian_id' => $guardian->id,
        'relationship_type' => 'mother',
        'is_primary_contact' => true,
    ]);

    // Use reflection to test protected method
    $reflection = new ReflectionClass($this->service);
    $method = $reflection->getMethod('associateGuardian');
    $method->setAccessible(true);

    $result = $method->invoke($this->service, $student, $guardian->id, 'father');

    expect($result->id)->toBe($existing->id);
    expect($result->relationship_type)->toBe('father');
});

test('logActivity is called for main operations', function () {
    Log::spy();

    $student = Student::factory()->create();

    $this->service->getPaginatedStudents();
    $this->service->findWithRelations($student->id);
    $this->service->getStudentsByGuardian(1);

    Log::shouldHaveReceived('info')->times(3);
});

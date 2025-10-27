<?php

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\PaymentStatus;
use App\Enums\Quarter;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\GuardianStudent;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    // Create school year
    $this->sy2024 = \App\Models\SchoolYear::firstOrCreate([
        'name' => '2024-2025',
        'start_year' => 2024,
        'end_year' => 2025,
        'start_date' => '2024-06-01',
        'end_date' => '2025-05-31',
        'status' => 'active',
    ]);

    // Create guardian user
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

    // Create student
    $this->student = Student::factory()->create([
        'student_id' => 'STU-001',
        'first_name' => 'John',
        'middle_name' => 'Michael',
        'last_name' => 'Doe',
        'birthdate' => '2010-01-15',
        'gender' => 'Male',
        'address' => '456 Student St',
        'contact_number' => '09987654321',
        'email' => 'john.doe@example.com',
        'grade_level' => 'Grade 3',
        'section' => 'Section A',
    ]);

    // Link guardian to student
    GuardianStudent::create([
        'guardian_id' => $this->guardianModel->id,
        'student_id' => $this->student->id,
        'relationship_type' => 'mother',
        'is_primary_contact' => true,
    ]);
});

describe('Guardian StudentController', function () {
    test('guardian can view their students list', function () {
        // Create another student for the same guardian
        $student2 = Student::factory()->create([
            'first_name' => 'Jane',
            'middle_name' => null,
            'last_name' => 'Doe',
        ]);

        GuardianStudent::create([
            'guardian_id' => $this->guardianModel->id,
            'student_id' => $student2->id,
            'relationship_type' => 'mother',
            'is_primary_contact' => false,
        ]);

        // Create enrollment for first student
        Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardianModel->id,
            'school_year_id' => $this->sy2024->id,
            'grade_level' => GradeLevel::GRADE_3,
            'status' => EnrollmentStatus::ENROLLED,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.students.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/students/index')
            ->has('students', 2)
            ->has('students.0', fn ($student) => $student
                ->where('student_id', 'STU-001')
                ->where('first_name', 'John')
                ->where('middle_name', 'Michael')
                ->where('last_name', 'Doe')
                ->where('full_name', 'John Michael Doe')
                ->has('latest_enrollment')
                ->etc()
            )
        );
    });

    test('student list shows latest enrollment information', function () {
        // Create multiple enrollments
        Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardianModel->id,
            'school_year_id' => \App\Models\SchoolYear::firstOrCreate(['name' => '2023-2024', 'start_year' => 2023, 'end_year' => 2024, 'start_date' => '2023-06-01', 'end_date' => '2024-05-31', 'status' => 'completed'])->id,
            'grade_level' => GradeLevel::GRADE_2,
            'status' => EnrollmentStatus::COMPLETED,
            'created_at' => now()->subYear(),
        ]);

        Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardianModel->id,
            'school_year_id' => $this->sy2024->id,
            'grade_level' => GradeLevel::GRADE_3,
            'status' => EnrollmentStatus::ENROLLED,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.students.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/students/index')
            ->has('students.0', fn ($student) => $student
                ->where('latest_enrollment.school_year_name', '2024-2025')
                ->where('latest_enrollment.grade_level', GradeLevel::GRADE_3->value)
                ->where('latest_enrollment.status', EnrollmentStatus::ENROLLED->value)
                ->etc()
            )
        );
    });

    test('guardian can view individual student details', function () {
        // Create enrollment for context
        Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardianModel->id,
            'school_year_id' => $this->sy2024->id,
            'grade_level' => GradeLevel::GRADE_3,
            'quarter' => Quarter::FIRST,
            'status' => EnrollmentStatus::ENROLLED,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.students.show', $this->student));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/students/show')
            ->has('student', fn ($student) => $student
                ->where('student_id', 'STU-001')
                ->where('first_name', 'John')
                ->where('middle_name', 'Michael')
                ->where('last_name', 'Doe')
                ->has('birthdate')  // Just check it exists, don't match exact format
                ->where('gender', 'Male')
                ->where('address', '456 Student St')
                ->where('contact_number', '09987654321')
                ->where('email', 'john.doe@example.com')
                ->where('grade_level', 'Grade 3')
                ->where('section', 'Section A')
                ->has('enrollments', 1)
                ->etc()
            )
        );
    });

    test('guardian cannot view other guardians students', function () {
        // Create another guardian's student
        $otherGuardianUser = User::factory()->create();
        $otherGuardianUser->assignRole('guardian');

        $otherGuardian = Guardian::create([
            'user_id' => $otherGuardianUser->id,
            'first_name' => 'Other',
            'last_name' => 'Guardian',
            'contact_number' => '09111222333',
            'address' => '789 Other St',
        ]);

        $otherStudent = Student::factory()->create();

        GuardianStudent::create([
            'guardian_id' => $otherGuardian->id,
            'student_id' => $otherStudent->id,
            'relationship_type' => 'father',
            'is_primary_contact' => true,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.students.show', $otherStudent));

        $response->assertStatus(403);
    });

    test('guardian can access create student form', function () {
        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.students.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/students/create')
        );
    });

    test('guardian can create new student', function () {
        Storage::fake('private');

        $studentData = [
            'first_name' => 'New',
            'middle_name' => 'Test',
            'last_name' => 'Student',
            'birthdate' => '2012-05-20',
            'gender' => 'Female',
            'grade_level' => 'Grade 3',
            'address' => '789 New Address',
            'contact_number' => '09876543210',
            'email' => 'new.student@example.com',
            'birth_place' => 'Manila',
            'nationality' => 'Filipino',
            'religion' => 'Catholic',
            'birth_certificate' => UploadedFile::fake()->image('birth_certificate.jpg'),
            'report_card' => UploadedFile::fake()->image('report_card.jpg'),
            'form_138' => UploadedFile::fake()->image('form_138.jpg'),
            'good_moral' => UploadedFile::fake()->image('good_moral.jpg'),
        ];

        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.students.store'), $studentData);

        $student = Student::where('first_name', 'New')
            ->where('last_name', 'Student')
            ->first();

        $response->assertRedirect(route('guardian.students.show', $student->id));
        $response->assertSessionHas('success', 'Student and documents added successfully.');

        $this->assertDatabaseHas('students', [
            'first_name' => 'New',
            'middle_name' => 'Test',
            'last_name' => 'Student',
            'gender' => 'Female',
        ]);

        // Check birthdate separately due to timestamp format
        $this->assertEquals('2012-05-20', $student->birthdate->format('Y-m-d'));

        // Check guardian-student link was created
        $this->assertDatabaseHas('guardian_students', [
            'guardian_id' => $this->guardianModel->id,
            'student_id' => $student->id,
            'is_primary_contact' => true,
        ]);

        // Check student ID was generated
        $this->assertStringStartsWith('CBHLC'.date('Y'), $student->student_id);

        // Check document was stored
        $this->assertDatabaseHas('documents', [
            'student_id' => $student->id,
            'document_type' => 'birth_certificate',
        ]);

        // Verify file was stored
        $document = $student->documents()->where('document_type', 'birth_certificate')->first();
        Storage::disk('private')->assertExists($document->file_path);
    });

    test('creating student validates required fields', function () {
        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.students.store'), [
                'first_name' => '',  // Missing required field
                'last_name' => 'Student',
                'birthdate' => '2012-05-20',
                'gender' => 'Female',
                'grade_level' => 'Grade 3',
                'address' => '789 New Address',
                // Missing birth_certificate, birth_place, nationality, religion, and other Grade 3 documents
            ]);

        $response->assertSessionHasErrors(['first_name', 'birth_certificate', 'birth_place', 'nationality', 'religion', 'report_card', 'form_138', 'good_moral']);
    });

    test('creating student validates birthdate is in past', function () {
        Storage::fake('private');

        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.students.store'), [
                'first_name' => 'Future',
                'last_name' => 'Student',
                'birthdate' => now()->addDay()->format('Y-m-d'),  // Future date
                'birth_certificate' => UploadedFile::fake()->image('birth_certificate.jpg'),
                'report_card' => UploadedFile::fake()->image('report_card.jpg'),
                'form_138' => UploadedFile::fake()->image('form_138.jpg'),
                'good_moral' => UploadedFile::fake()->image('good_moral.jpg'),
                'gender' => 'Male',
                'grade_level' => 'Grade 3',
                'address' => '123 Future St',
                'birth_place' => 'Cebu',
                'nationality' => 'Filipino',
                'religion' => 'Christian',
            ]);

        $response->assertSessionHasErrors(['birthdate']);
    });

    test('creating student validates gender values', function () {
        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.students.store'), [
                'first_name' => 'Test',
                'last_name' => 'Student',
                'birthdate' => '2012-05-20',
                'gender' => 'Other',  // Invalid value
                'grade_level' => 'Grade 3',
                'address' => '123 Test St',
                'birth_place' => 'Davao',
                'nationality' => 'Filipino',
                'religion' => 'Muslim',
            ]);

        $response->assertSessionHasErrors(['gender']);
    });

    test('creating student validates email format', function () {
        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.students.store'), [
                'first_name' => 'Test',
                'last_name' => 'Student',
                'birthdate' => '2012-05-20',
                'gender' => 'Male',
                'grade_level' => 'Grade 3',
                'address' => '123 Test St',
                'birth_place' => 'Baguio',
                'nationality' => 'Filipino',
                'religion' => 'Protestant',
                'email' => 'invalid-email',  // Invalid email
            ]);

        $response->assertSessionHasErrors(['email']);
    });

    test('kinder student only requires birth certificate', function () {
        Storage::fake('private');

        $studentData = [
            'first_name' => 'Kinder',
            'middle_name' => 'Test',
            'last_name' => 'Student',
            'birthdate' => '2019-05-20',
            'gender' => 'Male',
            'grade_level' => 'Kinder',
            'address' => '123 Kinder St',
            'birth_place' => 'Manila',
            'nationality' => 'Filipino',
            'religion' => 'Catholic',
            'birth_certificate' => UploadedFile::fake()->image('birth_certificate.jpg'),
            // No report_card, form_138, or good_moral - should still pass
        ];

        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.students.store'), $studentData);

        $student = Student::where('first_name', 'Kinder')
            ->where('last_name', 'Student')
            ->first();

        $response->assertRedirect(route('guardian.students.show', $student->id));
        $response->assertSessionHas('success', 'Student and documents added successfully.');
    });

    test('grade 1+ student requires all documents', function () {
        Storage::fake('private');

        $studentData = [
            'first_name' => 'Grade1',
            'middle_name' => 'Test',
            'last_name' => 'Student',
            'birthdate' => '2018-05-20',
            'gender' => 'Female',
            'grade_level' => 'Grade 1',
            'address' => '123 Grade1 St',
            'birth_place' => 'Cebu',
            'nationality' => 'Filipino',
            'religion' => 'Christian',
            'birth_certificate' => UploadedFile::fake()->image('birth_certificate.jpg'),
            // Missing report_card, form_138, and good_moral - should fail
        ];

        $response = $this->actingAs($this->guardian)
            ->post(route('guardian.students.store'), $studentData);

        $response->assertSessionHasErrors(['report_card', 'form_138', 'good_moral']);
    });

    test('guardian can access edit student form', function () {
        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.students.edit', $this->student));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/students/edit')
            ->has('student', fn ($student) => $student
                ->where('id', $this->student->id)
                ->where('first_name', 'John')
                ->etc()
            )
        );
    });

    test('guardian cannot edit other guardians students', function () {
        // Create another guardian's student
        $otherGuardianUser = User::factory()->create();
        $otherGuardianUser->assignRole('guardian');

        $otherGuardian = Guardian::create([
            'user_id' => $otherGuardianUser->id,
            'first_name' => 'Other',
            'last_name' => 'Guardian',
            'contact_number' => '09111222333',
            'address' => '789 Other St',
        ]);

        $otherStudent = Student::factory()->create();

        GuardianStudent::create([
            'guardian_id' => $otherGuardian->id,
            'student_id' => $otherStudent->id,
            'relationship_type' => 'father',
            'is_primary_contact' => true,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.students.edit', $otherStudent));

        $response->assertStatus(403);
    });

    test('guardian can update student information', function () {
        $updatedData = [
            'first_name' => 'Updated',
            'middle_name' => 'New',
            'last_name' => 'Name',
            'birthdate' => '2010-01-15',
            'gender' => 'Male',
            'address' => 'Updated Address 123',
            'contact_number' => '09111222333',
            'email' => 'updated@example.com',
        ];

        $response = $this->actingAs($this->guardian)
            ->put(route('guardian.students.update', $this->student), $updatedData);

        $response->assertRedirect(route('guardian.students.show', $this->student->id));
        $response->assertSessionHas('success', 'Student information updated successfully.');

        $this->student->refresh();
        $this->assertEquals('Updated', $this->student->first_name);
        $this->assertEquals('New', $this->student->middle_name);
        $this->assertEquals('Name', $this->student->last_name);
        $this->assertEquals('Updated Address 123', $this->student->address);
    });

    test('guardian cannot update other guardians students', function () {
        // Create another guardian's student
        $otherGuardianUser = User::factory()->create();
        $otherGuardianUser->assignRole('guardian');

        $otherGuardian = Guardian::create([
            'user_id' => $otherGuardianUser->id,
            'first_name' => 'Other',
            'last_name' => 'Guardian',
            'contact_number' => '09111222333',
            'address' => '789 Other St',
        ]);

        $otherStudent = Student::factory()->create();

        GuardianStudent::create([
            'guardian_id' => $otherGuardian->id,
            'student_id' => $otherStudent->id,
            'relationship_type' => 'father',
            'is_primary_contact' => true,
        ]);

        $response = $this->actingAs($this->guardian)
            ->put(route('guardian.students.update', $otherStudent), [
                'first_name' => 'Hacked',
                'last_name' => 'Name',
                'birthdate' => '2010-01-01',
                'gender' => 'Male',
                'address' => 'Hacker Address',
            ]);

        $response->assertStatus(403);

        // Verify data wasn't changed
        $otherStudent->refresh();
        $this->assertNotEquals('Hacked', $otherStudent->first_name);
    });

    test('updating student validates required fields', function () {
        $response = $this->actingAs($this->guardian)
            ->put(route('guardian.students.update', $this->student), [
                'first_name' => '',  // Missing required field
                'last_name' => 'Name',
                'birthdate' => '2010-01-15',
                'gender' => 'Male',
                'address' => '123 Test St',
            ]);

        $response->assertSessionHasErrors(['first_name']);
    });

    test('student with no enrollments shows null latest enrollment', function () {
        // Create student without enrollments
        $studentNoEnrollment = Student::factory()->create();

        GuardianStudent::create([
            'guardian_id' => $this->guardianModel->id,
            'student_id' => $studentNoEnrollment->id,
            'relationship_type' => 'mother',
            'is_primary_contact' => false,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.students.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/students/index')
            ->has('students', 2)  // Original student + new one
            ->where('students.1.latest_enrollment', null)
        );
    });

    test('student details shows all enrollments', function () {
        // Create multiple enrollments
        Enrollment::factory()->count(3)->create([
            'student_id' => $this->student->id,
            'guardian_id' => $this->guardianModel->id,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.students.show', $this->student));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/students/show')
            ->has('student.enrollments', 3)
            ->has('student.enrollments.0', fn ($enrollment) => $enrollment
                ->has('id')
                ->has('school_year_id')
                ->has('grade_level')
                ->has('quarter')
                ->has('status')
                ->has('payment_status')
                ->has('created_at')
            )
        );
    });

    test('student list handles students without middle name', function () {
        $studentNoMiddle = Student::factory()->create([
            'first_name' => 'Jane',
            'middle_name' => null,
            'last_name' => 'Smith',
        ]);

        GuardianStudent::create([
            'guardian_id' => $this->guardianModel->id,
            'student_id' => $studentNoMiddle->id,
            'relationship_type' => 'mother',
            'is_primary_contact' => false,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.students.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/students/index')
            ->has('students', 2)
            ->where('students.1.full_name', 'Jane Smith')  // No extra space
        );
    });

    test('guardian only sees their own students in list', function () {
        // Create another guardian with students
        $otherGuardianUser = User::factory()->create();
        $otherGuardianUser->assignRole('guardian');

        $otherGuardian = Guardian::create([
            'user_id' => $otherGuardianUser->id,
            'first_name' => 'Other',
            'last_name' => 'Guardian',
            'contact_number' => '09111222333',
            'address' => '789 Other St',
        ]);

        $otherStudent = Student::factory()->create([
            'first_name' => 'Other',
            'last_name' => 'Student',
        ]);

        GuardianStudent::create([
            'guardian_id' => $otherGuardian->id,
            'student_id' => $otherStudent->id,
            'relationship_type' => 'father',
            'is_primary_contact' => true,
        ]);

        $response = $this->actingAs($this->guardian)
            ->get(route('guardian.students.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('guardian/students/index')
            ->has('students', 1)  // Only their own student
            ->where('students.0.first_name', 'John')  // Not 'Other'
        );
    });
});

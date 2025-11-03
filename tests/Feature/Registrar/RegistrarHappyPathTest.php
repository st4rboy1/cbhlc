<?php

use App\Enums\DocumentType;
use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\VerificationStatus;
use App\Models\Document;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Storage::fake('private');
});

describe('Registrar Happy Path - Dashboard', function () {
    test('registrar can view dashboard', function () {
        $registrar = User::factory()->registrar()->create([
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($registrar);

        $response = $this->get('/registrar/dashboard');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('registrar/dashboard')
            ->has('enrollmentStats')
            ->has('recentApplications')
            ->has('studentStats')
        );
    })->group('registrar-happy', 'feature', 'critical');

    test('registrar dashboard shows enrollment statistics', function () {
        $registrar = User::factory()->registrar()->create([
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
        ]);

        // Create some enrollments with different statuses
        Enrollment::factory()->count(3)->create(['status' => EnrollmentStatus::PENDING]);
        Enrollment::factory()->count(2)->create(['status' => EnrollmentStatus::ENROLLED]);
        Enrollment::factory()->count(1)->create(['status' => EnrollmentStatus::REJECTED]);

        $this->actingAs($registrar);

        $response = $this->get('/registrar/dashboard');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('registrar/dashboard')
            ->where('enrollmentStats.pending', 3)
            ->where('enrollmentStats.approved', 2)
            ->where('enrollmentStats.rejected', 1)
        );
    })->group('registrar-happy', 'feature', 'critical');
});

describe('Registrar Happy Path - Enrollment Management', function () {
    test('registrar can view enrollments list', function () {
        $registrar = User::factory()->registrar()->create([
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
        ]);

        $student = Student::factory()->create([
            'first_name' => 'John',
            'last_name' => 'ApplicantOne',
        ]);

        Enrollment::factory()->create([
            'student_id' => $student->id,
            'status' => EnrollmentStatus::PENDING,
        ]);

        $this->actingAs($registrar);

        $response = $this->get('/registrar/enrollments');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('registrar/enrollments/index')
            ->has('enrollments.data', 1)
        );
    })->group('registrar-happy', 'feature', 'critical');

    test('registrar can approve enrollment from enrollments page', function () {
        $registrar = User::factory()->registrar()->create([
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
        ]);

        $guardian = Guardian::factory()->create();
        $student = Student::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'ApproveMe',
        ]);

        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'status' => EnrollmentStatus::PENDING,
        ]);

        // Login and verify enrollment can be approved via backend
        $this->actingAs($registrar);

        $response = $this->post("/registrar/enrollments/{$enrollment->id}/approve");
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify status changed in database
        $enrollment->refresh();
        // Enrollment approval changes status to READY_FOR_PAYMENT initially
        expect($enrollment->status)->toBe(EnrollmentStatus::READY_FOR_PAYMENT);
    })->group('registrar-happy', 'feature', 'critical');

    test('registrar can reject enrollment with reason', function () {
        $registrar = User::factory()->registrar()->create([
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
        ]);

        $guardian = Guardian::factory()->create();
        $student = Student::factory()->create([
            'first_name' => 'Bob',
            'last_name' => 'RejectMe',
        ]);

        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'status' => EnrollmentStatus::PENDING,
        ]);

        $this->actingAs($registrar);

        $response = $this->post("/registrar/enrollments/{$enrollment->id}/reject", [
            'reason' => 'Incomplete documentation',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $enrollment->refresh();
        expect($enrollment->status)->toBe(EnrollmentStatus::REJECTED);
        expect($enrollment->remarks)->toBe('Incomplete documentation');
    })->group('registrar-happy', 'feature', 'critical');
});

describe('Registrar Happy Path - Student Management', function () {
    test('registrar can view student details', function () {
        $registrar = User::factory()->registrar()->create([
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
        ]);

        $student = Student::factory()->create([
            'first_name' => 'View',
            'last_name' => 'Details',
        ]);

        $this->actingAs($registrar);

        $response = $this->get("/registrar/students/{$student->id}");
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('registrar/students/show')
            ->where('student.id', $student->id)
            ->where('student.first_name', 'View')
        );
    })->group('registrar-happy', 'feature', 'critical');
});

describe('Registrar Happy Path - Document Management', function () {
    test('registrar can verify document', function () {
        $registrar = User::factory()->registrar()->create([
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
        ]);

        $student = Student::factory()->create();
        $document = Document::factory()->create([
            'student_id' => $student->id,
            'verification_status' => VerificationStatus::PENDING,
        ]);

        $this->actingAs($registrar);

        $response = $this->post("/registrar/documents/{$document->id}/verify");

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Document verified successfully.');

        $document->refresh();
        expect($document->verification_status)->toBe(VerificationStatus::VERIFIED);
        expect($document->verified_by)->toBe($registrar->id);
    })->group('registrar-happy', 'feature', 'critical');

});

describe('Registrar Happy Path - Search and Filter', function () {
    test('registrar can filter enrollments by status', function () {
        $registrar = User::factory()->registrar()->create([
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
        ]);

        Enrollment::factory()->count(2)->create(['status' => EnrollmentStatus::PENDING]);
        Enrollment::factory()->count(1)->create(['status' => EnrollmentStatus::REJECTED]);

        $this->actingAs($registrar);

        $response = $this->get('/registrar/enrollments?status='.EnrollmentStatus::PENDING->value);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('registrar/enrollments/index')
            ->has('enrollments.data', 2)
        );
    })->group('registrar-happy', 'feature', 'critical');

    test('registrar can filter enrollments by grade level', function () {
        $registrar = User::factory()->registrar()->create([
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
        ]);

        Enrollment::factory()->count(3)->create([
            'grade_level' => GradeLevel::GRADE_1,
            'status' => EnrollmentStatus::PENDING,
        ]);

        Enrollment::factory()->count(2)->create([
            'grade_level' => GradeLevel::GRADE_2,
            'status' => EnrollmentStatus::PENDING,
        ]);

        $this->actingAs($registrar);

        $response = $this->get('/registrar/enrollments?grade_level='.GradeLevel::GRADE_1->value);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('registrar/enrollments/index')
            ->has('enrollments.data', 3)
        );
    })->group('registrar-happy', 'feature', 'critical');
});

describe('Registrar Happy Path - Complete Workflow', function () {
    test('registrar can complete full enrollment workflow', function () {
        $registrar = User::factory()->registrar()->create([
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
        ]);

        $guardian = Guardian::factory()->create();
        $student = Student::factory()->create([
            'first_name' => 'Complete',
            'last_name' => 'Workflow',
        ]);

        // Step 1: Create enrollment
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'status' => EnrollmentStatus::PENDING,
        ]);

        // Step 2: Upload and verify document
        $file = UploadedFile::fake()->image('test-document.jpg');
        Storage::disk('private')->putFileAs(
            "{$student->id}",
            $file,
            'test-document.jpg'
        );

        $document = Document::factory()->create([
            'student_id' => $student->id,
            'document_type' => DocumentType::BIRTH_CERTIFICATE,
            'file_path' => "{$student->id}/test-document.jpg",
            'verification_status' => VerificationStatus::PENDING,
        ]);

        $this->actingAs($registrar);

        // Verify document
        $this->post("/registrar/documents/{$document->id}/verify")
            ->assertRedirect()
            ->assertSessionHas('success');

        $document->refresh();
        expect($document->verification_status)->toBe(VerificationStatus::VERIFIED);

        // Step 3: Approve enrollment
        $this->post("/registrar/enrollments/{$enrollment->id}/approve")
            ->assertRedirect()
            ->assertSessionHas('success');

        $enrollment->refresh();
        // Enrollment approval changes status to READY_FOR_PAYMENT initially
        expect($enrollment->status)->toBe(EnrollmentStatus::READY_FOR_PAYMENT);
        expect($enrollment->approved_by)->toBe($registrar->id);
        expect($enrollment->approved_at)->not->toBeNull();

        // Step 4: Verify student is now enrolled
        $response = $this->get("/registrar/students/{$student->id}");
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('registrar/students/show')
            ->where('student.id', $student->id)
        );
    })->group('registrar-happy', 'feature', 'critical');
});

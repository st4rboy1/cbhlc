<?php

use App\Enums\DocumentType;
use App\Enums\VerificationStatus;
use App\Models\Document;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('private');

    // Seed roles and permissions
    $this->seed(RolesAndPermissionsSeeder::class);

    // Create guardian user and associated Guardian model
    $this->guardianModel = Guardian::factory()->create();
    $this->guardian = $this->guardianModel->user;
    $this->guardian->assignRole('guardian');

    $this->student = Student::factory()->create();
    $this->student->guardians()->attach($this->guardianModel->id);
});

test('guardian can upload document for their student', function () {
    $file = UploadedFile::fake()->image('birth-certificate.jpg', 1024, 768)->size(1000);

    $response = $this->actingAs($this->guardian)
        ->postJson(route('guardian.students.documents.store', $this->student), [
            'document' => $file,
            'document_type' => DocumentType::BIRTH_CERTIFICATE->value,
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'message' => 'Document uploaded successfully.',
        ]);

    expect(Document::count())->toBe(1);

    $document = Document::first();
    expect($document->student_id)->toBe($this->student->id)
        ->and($document->document_type)->toBe(DocumentType::BIRTH_CERTIFICATE)
        ->and($document->original_filename)->toBe('birth-certificate.jpg')
        ->and($document->verification_status)->toBe(VerificationStatus::PENDING);

    Storage::disk('private')->assertExists($document->file_path);
});

test('guardian cannot upload PDF document - only JPEG and PNG allowed', function () {
    $file = UploadedFile::fake()->create('form-138.pdf', 2000, 'application/pdf');

    $response = $this->actingAs($this->guardian)
        ->postJson(route('guardian.students.documents.store', $this->student), [
            'document' => $file,
            'document_type' => DocumentType::FORM_138->value,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['document']);

    expect(Document::count())->toBe(0);
});

test('guardian cannot upload document for student they do not own', function () {
    $otherStudent = Student::factory()->create();
    $file = UploadedFile::fake()->image('birth-certificate.jpg');

    $response = $this->actingAs($this->guardian)
        ->postJson(route('guardian.students.documents.store', $otherStudent), [
            'document' => $file,
            'document_type' => DocumentType::BIRTH_CERTIFICATE->value,
        ]);

    $response->assertStatus(403);
    expect(Document::count())->toBe(0);
});

test('document upload requires document file', function () {
    $response = $this->actingAs($this->guardian)
        ->postJson(route('guardian.students.documents.store', $this->student), [
            'document_type' => DocumentType::BIRTH_CERTIFICATE->value,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['document']);
});

test('document upload requires document type', function () {
    $file = UploadedFile::fake()->image('birth-certificate.jpg');

    $response = $this->actingAs($this->guardian)
        ->postJson(route('guardian.students.documents.store', $this->student), [
            'document' => $file,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['document_type']);
});

test('document upload rejects invalid file types', function () {
    $file = UploadedFile::fake()->create('document.txt', 100);

    $response = $this->actingAs($this->guardian)
        ->postJson(route('guardian.students.documents.store', $this->student), [
            'document' => $file,
            'document_type' => DocumentType::BIRTH_CERTIFICATE->value,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['document']);
});

test('document upload rejects files larger than 50MB', function () {
    $file = UploadedFile::fake()->create('large-file.jpg', 51201); // 50MB + 1KB

    $response = $this->actingAs($this->guardian)
        ->postJson(route('guardian.students.documents.store', $this->student), [
            'document' => $file,
            'document_type' => DocumentType::BIRTH_CERTIFICATE->value,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['document']);
});

test('guardian can list documents for their student', function () {
    Document::factory()->count(3)->create(['student_id' => $this->student->id]);

    $response = $this->actingAs($this->guardian)
        ->get(route('guardian.students.documents.index', $this->student));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('guardian/students/documents/index')
            ->has('documents', 3)
            ->has('student')
        );
});

test('guardian cannot list documents for student they do not own', function () {
    $otherStudent = Student::factory()->create();
    Document::factory()->count(3)->create(['student_id' => $otherStudent->id]);

    $response = $this->actingAs($this->guardian)
        ->get(route('guardian.students.documents.index', $otherStudent));

    $response->assertStatus(403);
});

test('guardian can view a specific document', function () {
    $document = Document::factory()->create(['student_id' => $this->student->id]);

    $response = $this->actingAs($this->guardian)
        ->getJson(route('guardian.students.documents.show', [$this->student, $document]));

    $response->assertStatus(200)
        ->assertJsonPath('document.id', $document->id);
});

test('guardian cannot view document from another student', function () {
    $otherStudent = Student::factory()->create();
    $document = Document::factory()->create(['student_id' => $otherStudent->id]);

    $response = $this->actingAs($this->guardian)
        ->getJson(route('guardian.students.documents.show', [$this->student, $document]));

    // Should return 403 because policy check fails before document ownership check
    $response->assertStatus(403);
});

test('guardian can delete pending document', function () {
    $document = Document::factory()->pending()->create(['student_id' => $this->student->id]);
    $response = $this->actingAs($this->guardian)
        ->deleteJson(route('guardian.students.documents.destroy', [$this->student, $document]));

    $response->assertStatus(200);
    expect(Document::withTrashed()->count())->toBe(1);
    expect($document->fresh()->trashed())->toBeTrue();
});

test('guardian can delete rejected document', function () {
    $document = Document::factory()->rejected()->create(['student_id' => $this->student->id]);

    $response = $this->actingAs($this->guardian)
        ->deleteJson(route('guardian.students.documents.destroy', [$this->student, $document]));

    $response->assertStatus(200);
    expect(Document::withTrashed()->count())->toBe(1);
    expect($document->fresh()->trashed())->toBeTrue();
});

test('guardian cannot delete verified document', function () {
    $document = Document::factory()->verified()->create(['student_id' => $this->student->id]);

    $response = $this->actingAs($this->guardian)
        ->deleteJson(route('guardian.students.documents.destroy', [$this->student, $document]));

    $response->assertStatus(403);
    expect(Document::count())->toBe(1);
});

test('guardian cannot delete document from another student', function () {
    $otherStudent = Student::factory()->create();
    $document = Document::factory()->pending()->create(['student_id' => $otherStudent->id]);

    $response = $this->actingAs($this->guardian)
        ->deleteJson(route('guardian.students.documents.destroy', [$this->student, $document]));

    // Should return 403 because policy check fails before document ownership check
    $response->assertStatus(403);
    expect(Document::count())->toBe(1);
});

test('unauthenticated user cannot upload document', function () {
    $file = UploadedFile::fake()->image('birth-certificate.jpg');

    $response = $this->postJson(route('guardian.students.documents.store', $this->student), [
        'document' => $file,
        'document_type' => DocumentType::BIRTH_CERTIFICATE->value,
    ]);

    $response->assertStatus(401);
});

test('uploaded document has correct metadata', function () {
    $file = UploadedFile::fake()->image('test.jpg', 800, 600)->size(2048);

    $this->actingAs($this->guardian)
        ->postJson(route('guardian.students.documents.store', $this->student), [
            'document' => $file,
            'document_type' => DocumentType::REPORT_CARD->value,
        ]);

    $document = Document::first();

    expect($document->file_size)->toBeGreaterThan(0)
        ->and($document->mime_type)->toBe('image/jpeg')
        ->and($document->stored_filename)->toMatch('/^[a-zA-Z0-9]{40}\.(jpg|jpeg)$/')
        ->and($document->file_path)->toContain("{$this->student->id}/");
});

test('file is stored in correct directory structure', function () {
    $file = UploadedFile::fake()->image('birth-certificate.jpg');

    $this->actingAs($this->guardian)
        ->postJson(route('guardian.students.documents.store', $this->student), [
            'document' => $file,
            'document_type' => DocumentType::BIRTH_CERTIFICATE->value,
        ]);

    $document = Document::first();

    expect($document->file_path)->toStartWith("documents/{$this->student->id}/");
    Storage::disk('private')->assertExists($document->file_path);
});

test('guardian can download document with valid signed URL', function () {
    // Upload a real file first
    $file = UploadedFile::fake()->image('test-document.jpg');

    $this->actingAs($this->guardian)
        ->postJson(route('guardian.students.documents.store', $this->student), [
            'document' => $file,
            'document_type' => DocumentType::BIRTH_CERTIFICATE->value,
        ]);

    $document = Document::first();

    // Get the document to retrieve signed URL
    $showResponse = $this->actingAs($this->guardian)
        ->getJson(route('guardian.students.documents.show', [$this->student, $document]));

    $signedUrl = $showResponse->json('url');

    // Download using signed URL
    $response = $this->actingAs($this->guardian)
        ->get($signedUrl);

    $response->assertStatus(200)
        ->assertDownload($document->original_filename);
});

test('document download requires valid signature', function () {
    $document = Document::factory()->create(['student_id' => $this->student->id]);

    // Try to download without valid signature
    $response = $this->actingAs($this->guardian)
        ->getJson(route('guardian.students.documents.download', [$this->student, $document]));

    $response->assertStatus(403)
        ->assertJson(['message' => 'Invalid or expired download link.']);
});

test('show returns 404 when document does not belong to student', function () {
    // Create document for different student
    $otherStudent = Student::factory()->create();
    $document = Document::factory()->create(['student_id' => $otherStudent->id]);

    // Try to view document with wrong student context (this guardian owns this->student)
    // But document belongs to otherStudent
    $this->guardianModel->children()->attach($otherStudent->id);

    $response = $this->actingAs($this->guardian)
        ->getJson(route('guardian.students.documents.show', [$this->student, $document]));

    // Should return 404 because document.student_id !== student.id
    $response->assertStatus(404)
        ->assertJson(['message' => 'Document not found for this student.']);
});

test('destroy returns 404 when document does not belong to student', function () {
    // Create document for different student
    $otherStudent = Student::factory()->create();
    $document = Document::factory()->pending()->create(['student_id' => $otherStudent->id]);

    // Make guardian own both students
    $this->guardianModel->children()->attach($otherStudent->id);

    $response = $this->actingAs($this->guardian)
        ->deleteJson(route('guardian.students.documents.destroy', [$this->student, $document]));

    // Should return 404 because document.student_id !== student.id
    $response->assertStatus(404)
        ->assertJson(['message' => 'Document not found for this student.']);
});

test('destroy deletes physical file when it exists', function () {
    // Upload a real file first
    $file = UploadedFile::fake()->image('test-delete.jpg');

    $this->actingAs($this->guardian)
        ->postJson(route('guardian.students.documents.store', $this->student), [
            'document' => $file,
            'document_type' => DocumentType::BIRTH_CERTIFICATE->value,
        ]);

    $document = Document::first();

    // Verify file exists before deletion
    Storage::disk('private')->assertExists($document->file_path);

    $response = $this->actingAs($this->guardian)
        ->deleteJson(route('guardian.students.documents.destroy', [$this->student, $document]));

    $response->assertStatus(200);

    // Verify file no longer exists
    Storage::disk('private')->assertMissing($document->file_path);
});

test('download logs activity', function () {
    // Upload a real file first
    $file = UploadedFile::fake()->image('test-log.jpg');

    $this->actingAs($this->guardian)
        ->postJson(route('guardian.students.documents.store', $this->student), [
            'document' => $file,
            'document_type' => DocumentType::BIRTH_CERTIFICATE->value,
        ]);

    $document = Document::first();

    // Get signed URL
    $showResponse = $this->actingAs($this->guardian)
        ->getJson(route('guardian.students.documents.show', [$this->student, $document]));

    $signedUrl = $showResponse->json('url');

    // Download document
    $this->actingAs($this->guardian)
        ->get($signedUrl);

    // Check activity log
    $this->assertDatabaseHas('activity_log', [
        'subject_type' => Document::class,
        'subject_id' => $document->id,
        'description' => 'Document downloaded',
    ]);
});

test('show logs document access', function () {
    $document = Document::factory()->create(['student_id' => $this->student->id]);

    $this->actingAs($this->guardian)
        ->getJson(route('guardian.students.documents.show', [$this->student, $document]));

    // Check activity log
    $this->assertDatabaseHas('activity_log', [
        'subject_type' => Document::class,
        'subject_id' => $document->id,
        'description' => 'Document accessed',
    ]);
});

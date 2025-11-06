<?php

use App\Enums\DocumentType;
use App\Enums\VerificationStatus;
use App\Models\Document;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Create fake storage disk
    Storage::fake('private');

    // Create guardian user and student
    $this->user = User::factory()->create();
    $this->user->assignRole('guardian');

    $this->guardian = Guardian::create([
        'user_id' => $this->user->id,
        'first_name' => 'Test',
        'last_name' => 'Guardian',
        'contact_number' => '09123456789',
        'address' => '123 Test St',
    ]);

    $this->student = Student::factory()->create();
    $this->student->guardians()->attach($this->guardian->id);

    // Create admin user
    $this->admin = User::factory()->create();
    $this->admin->assignRole('administrator');
});

// Mime Type Validation Tests
test('file upload validates allowed mime types', function () {
    // Test with an explicitly unsupported mime type
    $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

    $response = $this->actingAs($this->user)
        ->postJson(route('guardian.students.documents.store', $this->student), [
            'document' => $file,
            'document_type' => DocumentType::BIRTH_CERTIFICATE->value,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['document']);
});

test('valid jpeg image passes mime type validation', function () {
    $file = UploadedFile::fake()->image('document.jpg');

    $response = $this->actingAs($this->user)
        ->postJson(route('guardian.students.documents.store', $this->student), [
            'document' => $file,
            'document_type' => DocumentType::BIRTH_CERTIFICATE->value,
        ]);

    $response->assertStatus(201)
        ->assertJson(['message' => 'Document uploaded successfully.']);
    expect(Document::count())->toBe(1);
});

test('valid png image passes mime type validation', function () {
    $file = UploadedFile::fake()->image('document.png');

    $response = $this->actingAs($this->user)
        ->postJson(route('guardian.students.documents.store', $this->student), [
            'document' => $file,
            'document_type' => DocumentType::BIRTH_CERTIFICATE->value,
        ]);

    $response->assertStatus(201)
        ->assertJson(['message' => 'Document uploaded successfully.']);
});

test('file content verification rejects non-image files with image extension', function () {
    // Create a text file disguised as an image
    $file = UploadedFile::fake()->createWithContent('fake.jpg', 'This is not an image');

    $response = $this->actingAs($this->user)
        ->postJson(route('guardian.students.documents.store', $this->student), [
            'document' => $file,
            'document_type' => DocumentType::BIRTH_CERTIFICATE->value,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['document']);
});

// Rate Limiting Tests
test('rate limiting prevents more than 5 uploads per minute', function () {
    // Make 5 successful uploads
    for ($i = 0; $i < 5; $i++) {
        $file = UploadedFile::fake()->image("document{$i}.jpg");

        $response = $this->actingAs($this->user)
            ->postJson(route('guardian.students.documents.store', $this->student), [
                'document' => $file,
                'document_type' => DocumentType::BIRTH_CERTIFICATE->value,
            ]);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Document uploaded successfully.']);
    }

    // 6th upload should be rate limited
    $file = UploadedFile::fake()->image('document6.jpg');

    $response = $this->actingAs($this->user)
        ->postJson(route('guardian.students.documents.store', $this->student), [
            'document' => $file,
            'document_type' => DocumentType::BIRTH_CERTIFICATE->value,
        ]);

    $response->assertStatus(429)
        ->assertJson(['message' => 'Too many upload attempts. Please try again later.']);
});

// Authorization Policy Tests
test('guardian can view documents of their own students', function () {
    $document = Document::factory()->create([
        'student_id' => $this->student->id,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('guardian.students.documents.show', [$this->student, $document]));

    $response->assertStatus(200);
});

test('guardian cannot view documents of other students', function () {
    $otherStudent = Student::factory()->create();
    $document = Document::factory()->create([
        'student_id' => $otherStudent->id,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('guardian.students.documents.show', [$this->student, $document]));

    // Should return 403 because policy check fails before document ownership check
    $response->assertStatus(403);
});

test('administrator cannot access guardian document routes', function () {
    $otherStudent = Student::factory()->create();
    $document = Document::factory()->create([
        'student_id' => $otherStudent->id,
    ]);

    // Admin trying to access guardian-specific route should be blocked by role middleware
    $response = $this->actingAs($this->admin)
        ->getJson(route('guardian.students.documents.show', [$otherStudent, $document]));

    $response->assertStatus(403);
});

test('guardian can delete pending documents', function () {
    $document = Document::factory()->create([
        'student_id' => $this->student->id,
        'verification_status' => VerificationStatus::PENDING,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson(route('guardian.students.documents.destroy', [$this->student, $document]));

    $response->assertStatus(200);
    expect(Document::withTrashed()->count())->toBe(1);
    expect($document->fresh()->trashed())->toBeTrue();
});

test('guardian cannot delete verified documents', function () {
    $document = Document::factory()->create([
        'student_id' => $this->student->id,
        'verification_status' => VerificationStatus::VERIFIED,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson(route('guardian.students.documents.destroy', [$this->student, $document]));

    $response->assertStatus(403);
    expect(Document::count())->toBe(1);
});

// Signed URL Tests
test('show method returns signed URL for document download', function () {
    $document = Document::factory()->create([
        'student_id' => $this->student->id,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('guardian.students.documents.show', [$this->student, $document]));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'document',
            'url',
        ]);

    $url = $response->json('url');
    expect($url)->toContain('signature')
        ->and($url)->toContain('expires');
});

test('download with valid signature succeeds', function () {
    Storage::fake('private');

    $document = Document::factory()->create([
        'student_id' => $this->student->id,
        'file_path' => 'documents/test.jpg',
    ]);

    // Create fake file
    Storage::disk('private')->put($document->file_path, 'fake content');

    // Generate signed URL
    $signedUrl = URL::temporarySignedRoute(
        'guardian.students.documents.download',
        now()->addMinutes(5),
        ['student' => $this->student->id, 'document' => $document->id]
    );

    $response = $this->actingAs($this->user)->get($signedUrl);

    $response->assertStatus(200);
});

test('download with expired signature fails', function () {
    $document = Document::factory()->create([
        'student_id' => $this->student->id,
    ]);

    // Generate expired signed URL (past time)
    $expiredUrl = URL::temporarySignedRoute(
        'guardian.students.documents.download',
        now()->subMinutes(5),
        ['student' => $this->student->id, 'document' => $document->id]
    );

    $response = $this->actingAs($this->user)->get($expiredUrl);

    $response->assertStatus(403)
        ->assertJson(['message' => 'Invalid or expired download link.']);
});

test('download with tampered signature fails', function () {
    Storage::fake('private');

    $document = Document::factory()->create([
        'student_id' => $this->student->id,
        'file_path' => 'documents/test.jpg',
    ]);

    // Create fake file
    Storage::disk('private')->put($document->file_path, 'fake content');

    // Generate signed URL
    $signedUrl = URL::temporarySignedRoute(
        'guardian.students.documents.download',
        now()->addMinutes(5),
        ['student' => $this->student->id, 'document' => $document->id]
    );

    // Tamper with URL by modifying the signature parameter
    $tamperedUrl = preg_replace('/signature=[^&]+/', 'signature=tampered', $signedUrl);

    $response = $this->actingAs($this->user)->get($tamperedUrl);

    $response->assertStatus(403);
});

// Audit Logging Tests
test('document access is logged', function () {
    $document = Document::factory()->create([
        'student_id' => $this->student->id,
    ]);

    Activity::truncate(); // Clear existing activities

    $this->actingAs($this->user)
        ->getJson(route('guardian.students.documents.show', [$this->student, $document]));

    $activity = Activity::latest()->first();

    expect($activity)->not->toBeNull()
        ->and($activity->description)->toBe('Document accessed')
        ->and($activity->subject_type)->toBe(Document::class)
        ->and($activity->subject_id)->toBe($document->id)
        ->and($activity->properties['action'])->toBe('viewed')
        ->and($activity->properties)->toHaveKey('ip_address')
        ->and($activity->properties)->toHaveKey('user_agent');
});

test('document download is logged', function () {
    Storage::fake('private');

    $document = Document::factory()->create([
        'student_id' => $this->student->id,
        'file_path' => 'documents/test.jpg',
    ]);

    // Create fake file
    Storage::disk('private')->put($document->file_path, 'fake content');

    Activity::truncate(); // Clear existing activities

    // Generate signed URL
    $signedUrl = URL::temporarySignedRoute(
        'guardian.students.documents.download',
        now()->addMinutes(5),
        ['student' => $this->student->id, 'document' => $document->id]
    );

    $this->actingAs($this->user)->get($signedUrl);

    $activity = Activity::latest()->first();

    expect($activity)->not->toBeNull()
        ->and($activity->description)->toBe('Document downloaded')
        ->and($activity->properties['action'])->toBe('downloaded')
        ->and($activity->properties)->toHaveKey('ip_address');
});

// File Storage Security Tests
test('uploaded files are stored with random filenames', function () {
    $file = UploadedFile::fake()->image('my-document.jpg');

    $response = $this->actingAs($this->user)
        ->postJson(route('guardian.students.documents.store', $this->student), [
            'document' => $file,
            'document_type' => DocumentType::BIRTH_CERTIFICATE->value,
        ]);

    $response->assertStatus(201)
        ->assertJson(['message' => 'Document uploaded successfully.']);

    $document = Document::first();

    // Stored filename should be random, not original
    expect($document->stored_filename)->not->toBe('my-document.jpg')
        ->and(strlen($document->stored_filename))->toBeGreaterThan(20);
});

test('uploaded files are stored in private disk', function () {
    $file = UploadedFile::fake()->image('document.jpg');

    $response = $this->actingAs($this->user)
        ->postJson(route('guardian.students.documents.store', $this->student), [
            'document' => $file,
            'document_type' => DocumentType::BIRTH_CERTIFICATE->value,
        ]);

    $response->assertStatus(201)
        ->assertJson(['message' => 'Document uploaded successfully.']);

    $document = Document::first();

    // Verify file was stored in private disk
    Storage::disk('private')->assertExists($document->file_path);
});

test('deleted documents remove physical files', function () {
    Storage::fake('private');

    $file = UploadedFile::fake()->image('document.jpg');

    $response = $this->actingAs($this->user)
        ->postJson(route('guardian.students.documents.store', $this->student), [
            'document' => $file,
            'document_type' => DocumentType::BIRTH_CERTIFICATE->value,
        ]);

    $response->assertStatus(201)
        ->assertJson(['message' => 'Document uploaded successfully.']);

    $document = Document::first();
    $filePath = $document->file_path;

    // Verify file exists
    Storage::disk('private')->assertExists($filePath);

    // Delete document
    $this->actingAs($this->user)
        ->deleteJson(route('guardian.students.documents.destroy', [$this->student, $document]));

    // Verify physical file is deleted
    Storage::disk('private')->assertMissing($filePath);
});

// File Size Validation Tests
test('files larger than 50MB are rejected', function () {
    $file = UploadedFile::fake()->create('large-file.jpg', 51201); // 51MB in KB

    $response = $this->actingAs($this->user)
        ->postJson(route('guardian.students.documents.store', $this->student), [
            'document' => $file,
            'document_type' => DocumentType::BIRTH_CERTIFICATE->value,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['document']);
});

test('files up to 50MB are accepted', function () {
    $file = UploadedFile::fake()->image('large-file.jpg')->size(10240); // 10MB

    $response = $this->actingAs($this->user)
        ->postJson(route('guardian.students.documents.store', $this->student), [
            'document' => $file,
            'document_type' => DocumentType::BIRTH_CERTIFICATE->value,
        ]);

    $response->assertStatus(201);
});

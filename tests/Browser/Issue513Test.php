<?php

use App\Enums\VerificationStatus;
use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

test('document preview page loads and download link works', function () {
    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Create a super admin user
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    // Create a student
    $student = Student::factory()->create();

    // Create a real document file in storage
    Storage::disk('private')->makeDirectory((string) $student->id);
    $storedName = 'test-document-'.uniqid().'.jpg';
    $path = "{$student->id}/{$storedName}";

    // Create a simple test image
    Storage::disk('private')->put($path, UploadedFile::fake()->image('test.jpg')->getContent());

    // Create document record
    $document = Document::factory()->create([
        'student_id' => $student->id,
        'document_type' => 'birth_certificate',
        'original_filename' => 'test-document.jpg',
        'stored_filename' => $storedName,
        'file_path' => $path,
        'file_size' => 1024,
        'mime_type' => 'image/jpeg',
        'verification_status' => VerificationStatus::PENDING,
    ]);

    // Verify file exists before test
    expect(Storage::disk('private')->exists($document->file_path))->toBeTrue();

    // Login as super admin and visit document page
    actingAs($superAdmin)
        ->visit("/super-admin/documents/{$document->id}")
        ->assertPathIs("/super-admin/documents/{$document->id}")
        ->assertSee('View Document')
        ->assertSee($document->original_filename);

    // Clean up
    Storage::disk('private')->deleteDirectory((string) $student->id);
})->group('browser', 'bug', 'issue-513');

<?php

use App\Enums\DocumentType;
use App\Enums\VerificationStatus;
use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Storage::fake('private');
});

describe('Documents Migration', function () {

    test('documents table exists with all required columns', function () {
        expect(Schema::hasTable('documents'))->toBeTrue();

        // Verify all required columns exist
        $columns = [
            'id',
            'student_id',
            'document_type',
            'original_filename',
            'stored_filename',
            'file_path',
            'file_size',
            'mime_type',
            'upload_date',
            'verification_status',
            'verified_by',
            'verified_at',
            'rejection_reason',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        foreach ($columns as $column) {
            expect(Schema::hasColumn('documents', $column))
                ->toBeTrue("Column {$column} should exist in documents table");
        }
    })->group('documents', 'migration');

    test('can create document with all required fields', function () {
        $student = Student::factory()->create();
        $user = User::factory()->superAdmin()->create();

        $document = Document::create([
            'student_id' => $student->id,
            'document_type' => DocumentType::BIRTH_CERTIFICATE,
            'original_filename' => 'birth-cert.pdf',
            'stored_filename' => 'documents/birth-cert-123.pdf',
            'file_path' => 'documents/birth-cert-123.pdf',
            'file_size' => 1024000,
            'mime_type' => 'application/pdf',
            'upload_date' => now(),
            'verification_status' => VerificationStatus::PENDING,
        ]);

        expect($document->exists())->toBeTrue();
        expect($document->student_id)->toBe($student->id);
        expect($document->document_type)->toBe(DocumentType::BIRTH_CERTIFICATE);
        expect($document->original_filename)->toBe('birth-cert.pdf');
        expect($document->stored_filename)->toBe('documents/birth-cert-123.pdf');
        expect($document->file_path)->toBe('documents/birth-cert-123.pdf');
        expect($document->file_size)->toBe(1024000);
        expect($document->mime_type)->toBe('application/pdf');
        expect($document->verification_status)->toBe(VerificationStatus::PENDING);
    })->group('documents', 'migration');

    test('document type enum works correctly', function () {
        $student = Student::factory()->create();

        $birthCert = Document::factory()->create([
            'student_id' => $student->id,
            'document_type' => DocumentType::BIRTH_CERTIFICATE,
        ]);
        expect($birthCert->document_type)->toBe(DocumentType::BIRTH_CERTIFICATE);

        $reportCard = Document::factory()->create([
            'student_id' => $student->id,
            'document_type' => DocumentType::REPORT_CARD,
        ]);
        expect($reportCard->document_type)->toBe(DocumentType::REPORT_CARD);

        $form138 = Document::factory()->create([
            'student_id' => $student->id,
            'document_type' => DocumentType::FORM_138,
        ]);
        expect($form138->document_type)->toBe(DocumentType::FORM_138);

        $goodMoral = Document::factory()->create([
            'student_id' => $student->id,
            'document_type' => DocumentType::GOOD_MORAL,
        ]);
        expect($goodMoral->document_type)->toBe(DocumentType::GOOD_MORAL);
    })->group('documents', 'migration');

    test('verification status enum works correctly', function () {
        $student = Student::factory()->create();

        $pending = Document::factory()->create([
            'student_id' => $student->id,
            'verification_status' => VerificationStatus::PENDING,
        ]);
        expect($pending->verification_status)->toBe(VerificationStatus::PENDING);
        expect($pending->isPending())->toBeTrue();

        $verified = Document::factory()->create([
            'student_id' => $student->id,
            'verification_status' => VerificationStatus::VERIFIED,
        ]);
        expect($verified->verification_status)->toBe(VerificationStatus::VERIFIED);
        expect($verified->isVerified())->toBeTrue();

        $rejected = Document::factory()->create([
            'student_id' => $student->id,
            'verification_status' => VerificationStatus::REJECTED,
        ]);
        expect($rejected->verification_status)->toBe(VerificationStatus::REJECTED);
        expect($rejected->isRejected())->toBeTrue();
    })->group('documents', 'migration');

    test('document belongs to student', function () {
        $student = Student::factory()->create();
        $document = Document::factory()->create([
            'student_id' => $student->id,
        ]);

        expect($document->student)->toBeInstanceOf(Student::class);
        expect($document->student->id)->toBe($student->id);
    })->group('documents', 'migration');

    test('document can be verified by user', function () {
        $student = Student::factory()->create();
        $user = User::factory()->superAdmin()->create();
        $document = Document::factory()->create([
            'student_id' => $student->id,
            'verification_status' => VerificationStatus::PENDING,
        ]);

        $document->verify($user);
        $document->refresh();

        expect($document->verification_status)->toBe(VerificationStatus::VERIFIED);
        expect($document->verified_by)->toBe($user->id);
        expect($document->verified_at)->not->toBeNull();
        expect($document->rejection_reason)->toBeNull();
    })->group('documents', 'migration');

    test('document can be rejected by user with reason', function () {
        $student = Student::factory()->create();
        $user = User::factory()->superAdmin()->create();
        $document = Document::factory()->create([
            'student_id' => $student->id,
            'verification_status' => VerificationStatus::PENDING,
        ]);

        $document->reject($user, 'Document is not clear');
        $document->refresh();

        expect($document->verification_status)->toBe(VerificationStatus::REJECTED);
        expect($document->verified_by)->toBe($user->id);
        expect($document->verified_at)->not->toBeNull();
        expect($document->rejection_reason)->toBe('Document is not clear');
    })->group('documents', 'migration');

    test('document foreign keys work correctly', function () {
        $student = Student::factory()->create();
        $user = User::factory()->superAdmin()->create();
        $document = Document::factory()->create([
            'student_id' => $student->id,
            'verified_by' => $user->id,
            'verified_at' => now(),
        ]);

        expect($document->student)->toBeInstanceOf(Student::class);
        expect($document->student->id)->toBe($student->id);
        expect($document->verifiedBy)->toBeInstanceOf(User::class);
        expect($document->verifiedBy->id)->toBe($user->id);
    })->group('documents', 'migration');

    test('document soft deletes work correctly', function () {
        $student = Student::factory()->create();
        $document = Document::factory()->create([
            'student_id' => $student->id,
        ]);

        $documentId = $document->id;

        // Soft delete the document
        $document->delete();

        // Document should not exist in normal queries
        expect(Document::find($documentId))->toBeNull();

        // Document should exist in trashed queries
        expect(Document::withTrashed()->find($documentId))->not->toBeNull();
        expect(Document::onlyTrashed()->find($documentId))->not->toBeNull();
    })->group('documents', 'migration');
});

<?php

use App\Enums\DocumentType;
use App\Enums\VerificationStatus;
use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('documents table exists', function () {
    expect(Schema::hasTable('documents'))->toBeTrue();
});

test('documents table has required columns', function () {
    expect(Schema::hasColumns('documents', [
        'id', 'student_id', 'document_type', 'original_filename',
        'stored_filename', 'file_path', 'file_size', 'mime_type',
        'upload_date', 'verification_status', 'verified_by',
        'verified_at', 'rejection_reason', 'created_at',
        'updated_at', 'deleted_at',
    ]))->toBeTrue();
});

test('document model can be instantiated', function () {
    $document = new Document();

    expect($document)->toBeInstanceOf(Document::class);
});

test('document belongs to student', function () {
    $student = Student::factory()->create();
    $document = Document::factory()->create(['student_id' => $student->id]);

    expect($document->student)->toBeInstanceOf(Student::class)
        ->and($document->student->id)->toBe($student->id);
});

test('document belongs to verified by user', function () {
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'verified_by' => $user->id,
        'verification_status' => VerificationStatus::VERIFIED,
    ]);

    expect($document->verifiedBy)->toBeInstanceOf(User::class)
        ->and($document->verifiedBy->id)->toBe($user->id);
});

test('document type enum casts properly', function () {
    $document = Document::factory()->create([
        'document_type' => DocumentType::BIRTH_CERTIFICATE,
    ]);

    expect($document->document_type)->toBeInstanceOf(DocumentType::class)
        ->and($document->document_type)->toBe(DocumentType::BIRTH_CERTIFICATE);
});

test('verification status enum casts properly', function () {
    $document = Document::factory()->create([
        'verification_status' => VerificationStatus::PENDING,
    ]);

    expect($document->verification_status)->toBeInstanceOf(VerificationStatus::class)
        ->and($document->verification_status)->toBe(VerificationStatus::PENDING);
});

test('upload date casts to datetime', function () {
    $document = Document::factory()->create([
        'upload_date' => now(),
    ]);

    expect($document->upload_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('verified at casts to datetime', function () {
    $document = Document::factory()->create([
        'verified_at' => now(),
    ]);

    expect($document->verified_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('document has soft deletes', function () {
    $document = Document::factory()->create();

    $document->delete();

    expect($document->trashed())->toBeTrue()
        ->and(Document::withTrashed()->find($document->id))->not->toBeNull();
});

test('deleting student cascades to documents', function () {
    $student = Student::factory()->create();
    $document = Document::factory()->create(['student_id' => $student->id]);

    $student->delete();

    expect(Document::find($document->id))->toBeNull();
});

test('deleting verifier sets verified by to null', function () {
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'verified_by' => $user->id,
        'verification_status' => VerificationStatus::VERIFIED,
    ]);

    $user->delete();
    $document->refresh();

    expect($document->verified_by)->toBeNull();
});

test('document has required fields', function () {
    $document = Document::factory()->create();

    expect($document->student_id)->not->toBeNull()
        ->and($document->document_type)->not->toBeNull()
        ->and($document->original_filename)->not->toBeNull()
        ->and($document->stored_filename)->not->toBeNull()
        ->and($document->file_path)->not->toBeNull()
        ->and($document->file_size)->not->toBeNull()
        ->and($document->mime_type)->not->toBeNull()
        ->and($document->upload_date)->not->toBeNull()
        ->and($document->verification_status)->not->toBeNull();
});

test('verification status defaults to pending', function () {
    $document = Document::factory()->create([
        'verification_status' => VerificationStatus::PENDING,
    ]);

    expect($document->verification_status)->toBe(VerificationStatus::PENDING);
});

test('document can be verified', function () {
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'verification_status' => VerificationStatus::PENDING,
    ]);

    $document->verify($user);

    expect($document->verification_status)->toBe(VerificationStatus::VERIFIED)
        ->and($document->verified_by)->toBe($user->id)
        ->and($document->verified_at)->not->toBeNull()
        ->and($document->rejection_reason)->toBeNull();
});

test('document can be rejected', function () {
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'verification_status' => VerificationStatus::PENDING,
    ]);

    $reason = 'Document is not clear';
    $document->reject($user, $reason);

    expect($document->verification_status)->toBe(VerificationStatus::REJECTED)
        ->and($document->verified_by)->toBe($user->id)
        ->and($document->verified_at)->not->toBeNull()
        ->and($document->rejection_reason)->toBe($reason);
});

test('is verified method works correctly', function () {
    $document = Document::factory()->create([
        'verification_status' => VerificationStatus::VERIFIED,
    ]);

    expect($document->isVerified())->toBeTrue();

    $document->verification_status = VerificationStatus::PENDING;
    expect($document->isVerified())->toBeFalse();
});

test('is pending method works correctly', function () {
    $document = Document::factory()->create([
        'verification_status' => VerificationStatus::PENDING,
    ]);

    expect($document->isPending())->toBeTrue();

    $document->verification_status = VerificationStatus::VERIFIED;
    expect($document->isPending())->toBeFalse();
});

test('is rejected method works correctly', function () {
    $document = Document::factory()->create([
        'verification_status' => VerificationStatus::REJECTED,
    ]);

    expect($document->isRejected())->toBeTrue();

    $document->verification_status = VerificationStatus::PENDING;
    expect($document->isRejected())->toBeFalse();
});

test('human file size attribute formats correctly', function () {
    $document = Document::factory()->create(['file_size' => 1024]); // 1 KB
    expect($document->human_file_size)->toBe('1 KB');

    $document = Document::factory()->create(['file_size' => 1024 * 1024]); // 1 MB
    expect($document->human_file_size)->toBe('1 MB');

    $document = Document::factory()->create(['file_size' => 500]); // 500 B
    expect($document->human_file_size)->toBe('500 B');
});

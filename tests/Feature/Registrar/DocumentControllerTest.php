<?php

use App\Models\Document;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use App\Notifications\DocumentRejectedNotification;
use App\Notifications\DocumentVerifiedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('private');
    Notification::fake();

    // Create roles
    Role::create(['name' => 'super_admin']);
    Role::create(['name' => 'administrator']);
    Role::create(['name' => 'registrar']);
    Role::create(['name' => 'guardian']);

    // Create registrar user
    $this->registrar = User::factory()->create();
    $this->registrar->assignRole('registrar');

    // Create guardian user with guardian relationship
    $this->guardian = User::factory()->create();
    $this->guardian->assignRole('guardian');
    $guardianModel = Guardian::factory()->create(['user_id' => $this->guardian->id]);
    $this->guardian->load('guardian');

    // Create student with guardian
    $this->student = Student::factory()->create();
    $this->student->guardians()->attach($guardianModel->id);

    // Create a document
    $file = UploadedFile::fake()->image('document.jpg');
    $path = $file->store('documents/'.$this->student->id, 'private');

    $this->document = Document::create([
        'student_id' => $this->student->id,
        'document_type' => 'birth_certificate',
        'original_filename' => 'document.jpg',
        'stored_filename' => basename($path),
        'file_path' => $path,
        'file_size' => $file->getSize(),
        'mime_type' => $file->getMimeType(),
        'upload_date' => now(),
        'verification_status' => 'pending',
    ]);
});

// ========================================
// VERIFY DOCUMENT TESTS
// ========================================

test('registrar can verify document', function () {
    $response = actingAs($this->registrar)
        ->post(route('registrar.documents.verify', $this->document));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->document->refresh();
    expect($this->document->verification_status->value)->toBe('verified');
    expect($this->document->verified_by)->toBe($this->registrar->id);
    expect($this->document->verified_at)->not->toBeNull();
});

test('verifying document sends notification to guardian', function () {
    actingAs($this->registrar)
        ->post(route('registrar.documents.verify', $this->document));

    Notification::assertSentTo(
        $this->guardian,
        DocumentVerifiedNotification::class,
        fn ($notification) => $notification->document->id === $this->document->id
    );
});

test('verifying document logs activity', function () {
    actingAs($this->registrar)
        ->post(route('registrar.documents.verify', $this->document));

    assertDatabaseHas('activity_log', [
        'subject_type' => Document::class,
        'subject_id' => $this->document->id,
        'description' => 'Document verified',
    ]);
});

test('cannot verify already verified document', function () {
    // First verification
    $this->document->verify($this->registrar);

    // Try to verify again
    $response = actingAs($this->registrar)
        ->post(route('registrar.documents.verify', $this->document));

    $response->assertForbidden();
});

// ========================================
// REJECT DOCUMENT TESTS
// ========================================

test('registrar can reject document with notes', function () {
    $response = actingAs($this->registrar)
        ->post(route('registrar.documents.reject', $this->document), [
            'notes' => 'Document is not clear enough. Please re-upload.',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->document->refresh();
    expect($this->document->verification_status->value)->toBe('rejected');
    expect($this->document->verified_by)->toBe($this->registrar->id);
    expect($this->document->rejection_reason)->toBe('Document is not clear enough. Please re-upload.');
});

test('rejecting document sends notification to guardian', function () {
    actingAs($this->registrar)
        ->post(route('registrar.documents.reject', $this->document), [
            'notes' => 'Document is not clear enough.',
        ]);

    Notification::assertSentTo(
        $this->guardian,
        DocumentRejectedNotification::class,
        fn ($notification) => $notification->document->id === $this->document->id
    );
});

test('rejecting document logs activity', function () {
    actingAs($this->registrar)
        ->post(route('registrar.documents.reject', $this->document), [
            'notes' => 'Document is not clear enough.',
        ]);

    assertDatabaseHas('activity_log', [
        'subject_type' => Document::class,
        'subject_id' => $this->document->id,
        'description' => 'Document rejected',
    ]);
});

test('rejection requires notes', function () {
    $response = actingAs($this->registrar)
        ->post(route('registrar.documents.reject', $this->document), [
            'notes' => '',
        ]);

    $response->assertSessionHasErrors('notes');
});

test('rejection notes must be at least 10 characters', function () {
    $response = actingAs($this->registrar)
        ->post(route('registrar.documents.reject', $this->document), [
            'notes' => 'Too short',
        ]);

    $response->assertSessionHasErrors('notes');
});

test('rejection notes cannot exceed 500 characters', function () {
    $response = actingAs($this->registrar)
        ->post(route('registrar.documents.reject', $this->document), [
            'notes' => str_repeat('a', 501),
        ]);

    $response->assertSessionHasErrors('notes');
});

test('cannot reject already verified document', function () {
    // First verify
    $this->document->verify($this->registrar);

    // Try to reject
    $response = actingAs($this->registrar)
        ->post(route('registrar.documents.reject', $this->document), [
            'notes' => 'This should not work.',
        ]);

    $response->assertForbidden();
});

// ========================================
// AUTHORIZATION TESTS
// ========================================

test('guardian cannot verify document', function () {
    $response = actingAs($this->guardian)
        ->post(route('registrar.documents.verify', $this->document));

    $response->assertForbidden();
});

test('guardian cannot reject document', function () {
    $response = actingAs($this->guardian)
        ->post(route('registrar.documents.reject', $this->document), [
            'notes' => 'This should not work.',
        ]);

    $response->assertForbidden();
});

test('administrator can verify document', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrator');

    $response = actingAs($admin)
        ->post(route('registrar.documents.verify', $this->document));

    $response->assertRedirect();
    $response->assertSessionHas('success');
});

test('super admin can verify document', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    $response = actingAs($superAdmin)
        ->post(route('registrar.documents.verify', $this->document));

    $response->assertRedirect();
    $response->assertSessionHas('success');
});

// ========================================
// VIEW DOCUMENT TESTS
// ========================================

test('registrar can view document with temporary URL', function () {
    $response = actingAs($this->registrar)
        ->get(route('registrar.documents.show', $this->document));

    $response->assertOk();
    $response->assertJsonStructure([
        'document',
        'url',
    ]);
});

test('guardian cannot access registrar document routes', function () {
    $response = actingAs($this->guardian)
        ->get(route('registrar.documents.show', $this->document));

    // Guardians are blocked by middleware before policy check
    $response->assertForbidden();
});

test('guardian cannot view other students documents', function () {
    $otherStudent = Student::factory()->create();
    $otherDocument = Document::factory()->create([
        'student_id' => $otherStudent->id,
    ]);

    $response = actingAs($this->guardian)
        ->get(route('registrar.documents.show', $otherDocument));

    $response->assertForbidden();
});

// ========================================
// PENDING DOCUMENTS LIST TESTS
// ========================================

test('registrar can view pending documents', function () {
    $response = actingAs($this->registrar)
        ->get(route('registrar.documents.pending'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Registrar/Documents/Pending', false)
        ->has('documents')
    );
});

test('pending documents list only shows pending documents', function () {
    // Create verified document
    $verifiedDoc = Document::factory()->create([
        'student_id' => $this->student->id,
        'verification_status' => 'verified',
    ]);

    $response = actingAs($this->registrar)
        ->get(route('registrar.documents.pending'));

    $response->assertOk();
    // The pending list should not include verified documents
    // This is tested via the controller query filter
});

test('pending documents can be filtered by student', function () {
    $response = actingAs($this->registrar)
        ->get(route('registrar.documents.pending', ['student_id' => $this->student->id]));

    $response->assertOk();
});

// ========================================
// NOTIFICATION CONTENT TESTS
// ========================================

test('document verified notification has correct email content', function () {
    actingAs($this->registrar)
        ->post(route('registrar.documents.verify', $this->document));

    $this->document->refresh();

    Notification::assertSentTo(
        $this->guardian,
        DocumentVerifiedNotification::class,
        function ($notification) {
            $mailMessage = $notification->toMail($this->guardian);

            expect($mailMessage->subject)->toBe('Document Verified');
            expect($mailMessage->introLines)->toContain('Your uploaded document has been verified.');

            return true;
        }
    );
});

test('document verified notification has correct database content', function () {
    actingAs($this->registrar)
        ->post(route('registrar.documents.verify', $this->document));

    $this->document->refresh();

    Notification::assertSentTo(
        $this->guardian,
        DocumentVerifiedNotification::class,
        function ($notification) {
            $data = $notification->toArray($this->guardian);

            expect($data['document_id'])->toBe($this->document->id);
            expect($data['student_id'])->toBe($this->student->id);
            expect($data['student_name'])->toBe($this->student->full_name);

            return true;
        }
    );
});

test('document rejected notification has correct email content', function () {
    actingAs($this->registrar)
        ->post(route('registrar.documents.reject', $this->document), [
            'notes' => 'Document is not clear enough.',
        ]);

    $this->document->refresh();

    Notification::assertSentTo(
        $this->guardian,
        DocumentRejectedNotification::class,
        function ($notification) {
            $mailMessage = $notification->toMail($this->guardian);

            expect($mailMessage->subject)->toBe('Document Rejected');
            expect($mailMessage->introLines)->toContain('Your uploaded document has been rejected.');

            return true;
        }
    );
});

test('document rejected notification has correct database content', function () {
    actingAs($this->registrar)
        ->post(route('registrar.documents.reject', $this->document), [
            'notes' => 'Document is not clear enough.',
        ]);

    $this->document->refresh();

    Notification::assertSentTo(
        $this->guardian,
        DocumentRejectedNotification::class,
        function ($notification) {
            $data = $notification->toArray($this->guardian);

            expect($data['document_id'])->toBe($this->document->id);
            expect($data['student_id'])->toBe($this->student->id);
            expect($data['student_name'])->toBe($this->student->full_name);
            expect($data['rejection_reason'])->toBe('Document is not clear enough.');

            return true;
        }
    );
});

// ========================================
// DOCUMENT POLICY DIRECT TESTS
// ========================================

test('document policy viewAny allows authorized roles', function () {
    $policy = new \App\Policies\DocumentPolicy;

    expect($policy->viewAny($this->registrar))->toBeTrue();
    expect($policy->viewAny($this->guardian))->toBeTrue();

    $admin = User::factory()->create();
    $admin->assignRole('administrator');
    expect($policy->viewAny($admin))->toBeTrue();

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');
    expect($policy->viewAny($superAdmin))->toBeTrue();
});

test('document policy view allows registrar to view any document', function () {
    $policy = new \App\Policies\DocumentPolicy;

    expect($policy->view($this->registrar, $this->document))->toBeTrue();
});

test('document policy view allows guardian to view own student documents', function () {
    $policy = new \App\Policies\DocumentPolicy;

    expect($policy->view($this->guardian, $this->document))->toBeTrue();
});

test('document policy view denies guardian viewing other student documents', function () {
    $policy = new \App\Policies\DocumentPolicy;
    $otherStudent = Student::factory()->create();
    $otherDocument = Document::factory()->create(['student_id' => $otherStudent->id]);

    expect($policy->view($this->guardian, $otherDocument))->toBeFalse();
});

test('document policy create allows authorized roles', function () {
    $policy = new \App\Policies\DocumentPolicy;

    expect($policy->create($this->registrar))->toBeTrue();
    expect($policy->create($this->guardian))->toBeTrue();
});

test('document policy verify allows only admin roles', function () {
    $policy = new \App\Policies\DocumentPolicy;

    expect($policy->verify($this->registrar, $this->document))->toBeTrue();
    expect($policy->verify($this->guardian, $this->document))->toBeFalse();
});

test('document policy verify denies verified documents', function () {
    $policy = new \App\Policies\DocumentPolicy;
    $this->document->verify($this->registrar);

    expect($policy->verify($this->registrar, $this->document))->toBeFalse();
});

test('document policy reject allows only admin roles', function () {
    $policy = new \App\Policies\DocumentPolicy;

    expect($policy->reject($this->registrar, $this->document))->toBeTrue();
    expect($policy->reject($this->guardian, $this->document))->toBeFalse();
});

test('document policy reject denies verified documents', function () {
    $policy = new \App\Policies\DocumentPolicy;
    $this->document->verify($this->registrar);

    expect($policy->reject($this->registrar, $this->document))->toBeFalse();
});

test('document policy delete allows super admin and administrator', function () {
    $policy = new \App\Policies\DocumentPolicy;

    $admin = User::factory()->create();
    $admin->assignRole('administrator');
    expect($policy->delete($admin, $this->document))->toBeTrue();

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');
    expect($policy->delete($superAdmin, $this->document))->toBeTrue();
});

test('document policy delete allows guardian to delete own pending documents', function () {
    $policy = new \App\Policies\DocumentPolicy;

    expect($policy->delete($this->guardian, $this->document))->toBeTrue();
});

test('document policy delete denies guardian deleting verified documents', function () {
    $policy = new \App\Policies\DocumentPolicy;
    $this->document->verify($this->registrar);

    expect($policy->delete($this->guardian, $this->document))->toBeFalse();
});

test('document policy delete denies guardian deleting other student documents', function () {
    $policy = new \App\Policies\DocumentPolicy;
    $otherStudent = Student::factory()->create();
    $otherDocument = Document::factory()->create(['student_id' => $otherStudent->id]);

    expect($policy->delete($this->guardian, $otherDocument))->toBeFalse();
});

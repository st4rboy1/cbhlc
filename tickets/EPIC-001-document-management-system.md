# Ticket #001: Document Management System

## Priority: Critical (Must Have)

## Related SRS Requirements

- **FR-2.2:** System shall support document upload (Birth Certificate, Report Cards, Form 138, Good Moral Certificate)
- **FR-2.3:** System shall accept JPEG and PNG file formats with maximum 50MB file size
- **FR-4.4:** System shall maintain audit trail of all application actions
- **Section 8.2.2:** DOCUMENT entity (Supporting Entities in ERD)

## Current Status

❌ **NOT IMPLEMENTED**

No document management system exists in the current codebase:

- No `documents` migration file
- No `Document` model
- No document upload controllers or routes
- No document verification functionality

## Required Implementation

### 1. Database Layer

Create migration: `create_documents_table.php`

```php
Schema::create('documents', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained()->onDelete('cascade');
    $table->enum('document_type', [
        'birth_certificate',
        'report_card',
        'form_138',
        'good_moral_certificate',
        'other'
    ]);
    $table->string('original_filename');
    $table->string('stored_filename');
    $table->string('file_path', 500);
    $table->integer('file_size'); // in bytes
    $table->string('mime_type', 100);
    $table->timestamp('upload_date');
    $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
    $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
    $table->timestamp('verified_at')->nullable();
    $table->text('verification_notes')->nullable();
    $table->timestamps();
});
```

### 2. Model Layer

Create `app/Models/Document.php`:

- Relationships: `student()`, `verifiedBy()`
- Scopes: `pending()`, `verified()`, `rejected()`
- Methods: `verify()`, `reject()`, `getUrl()`
- File storage using Laravel's Storage facade

### 3. Backend Layer

**Controllers:**

- `Guardian/DocumentController.php` - Upload, view, delete documents
- `Registrar/DocumentController.php` - Verify/reject documents
- `SuperAdmin/DocumentController.php` - Full document management

**Routes:**

```php
// Guardian routes
Route::post('/students/{student}/documents', [GuardianDocumentController::class, 'store']);
Route::delete('/documents/{document}', [GuardianDocumentController::class, 'destroy']);
Route::get('/documents/{document}', [GuardianDocumentController::class, 'show']);

// Registrar routes
Route::post('/documents/{document}/verify', [RegistrarDocumentController::class, 'verify']);
Route::post('/documents/{document}/reject', [RegistrarDocumentController::class, 'reject']);
```

**Validation:**

- File types: JPEG, PNG only
- Max file size: 50MB
- Required document types per grade level
- Mime type validation

### 4. Frontend Layer

**Guardian Pages:**

- `/resources/js/pages/guardian/students/documents/index.tsx` - List documents
- `/resources/js/pages/guardian/students/documents/upload.tsx` - Upload UI with drag-and-drop

**Registrar Pages:**

- Document verification interface in enrollment show page
- Document preview/download functionality
- Verification status indicators

**Components:**

- `DocumentUpload` - Drag-and-drop upload component
- `DocumentCard` - Display document with status badge
- `DocumentVerificationDialog` - Verify/reject modal
- `DocumentPreview` - Image preview component

### 5. Storage Configuration

**File Storage:**

- Use Laravel Storage with local/S3 driver
- Directory structure: `documents/{student_id}/{document_type}/{filename}`
- Secure file access with signed URLs
- Automatic file cleanup on document deletion

### 6. Security Requirements

- Validate file mime types on server-side
- Scan uploaded files for malware (if possible)
- Use signed URLs for file access
- Prevent direct file access via web server
- Implement rate limiting on uploads

## Acceptance Criteria

✅ Guardian can upload documents for their students
✅ System validates file type (JPEG, PNG only) and size (max 50MB)
✅ Registrar can view and verify/reject documents
✅ Document verification status is tracked
✅ Files are securely stored and accessed
✅ Audit trail logs all document actions
✅ Documents can be previewed without downloading
✅ Old documents are properly cleaned up when deleted

## Testing Requirements

- Unit tests for Document model methods
- Feature tests for upload, verify, reject workflows
- File upload validation tests
- Storage integration tests
- Security tests for unauthorized access

## Estimated Effort

**High Priority:** 3-5 days

## Dependencies

- Requires storage configuration (local or S3)
- May require file scanning library for security
- Requires proper permission setup for document access

## Notes

- Consider implementing document templates/examples for users
- Add document upload reminders in enrollment workflow
- Consider OCR for automatic document verification (future enhancement)
- Implement document expiration dates if needed

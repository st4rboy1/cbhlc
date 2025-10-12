# Ticket #002: Implement Document Upload Backend

**Epic:** [EPIC-001 Document Management System](./EPIC-001-document-management-system.md)

**Type:** Story
**Priority:** Critical
**Estimated Effort:** 1 day
**Status:** ✅ Completed (Core functionality implemented, auth tests pending)
**Assignee:** Claude

## Description

Implement backend API for document upload, including validation, storage, and database record creation for guardians to upload student documents.

## Acceptance Criteria

- [ ] `Guardian/DocumentController` created with `store` method
- [ ] Route added: `POST /guardian/students/{student}/documents`
- [ ] File validation: JPEG, PNG, max 50MB
- [ ] Files stored securely in `storage/app/documents/{student_id}/`
- [ ] Database record created with metadata
- [ ] Returns JSON response with document info
- [ ] Error handling for invalid files, storage failures
- [ ] Proper authorization (guardian owns student)

## Implementation Details

### Controller Method

```php
public function store(StoreDocumentRequest $request, Student $student)
{
    // Authorize guardian owns student
    $this->authorize('uploadDocument', $student);

    // Handle file upload
    $file = $request->file('document');
    $originalName = $file->getClientOriginalName();
    $storedName = Str::random(40) . '.' . $file->extension();

    // Store file
    $path = $file->storeAs(
        "documents/{$student->id}",
        $storedName,
        'private'
    );

    // Create database record
    $document = $student->documents()->create([
        'document_type' => $request->document_type,
        'original_filename' => $originalName,
        'stored_filename' => $storedName,
        'file_path' => $path,
        'file_size' => $file->getSize(),
        'mime_type' => $file->getMimeType(),
        'upload_date' => now(),
        'verification_status' => 'pending',
    ]);

    return response()->json([
        'message' => 'Document uploaded successfully',
        'document' => $document,
    ]);
}
```

### Validation Rules

```php
class StoreDocumentRequest extends FormRequest
{
    public function rules()
    {
        return [
            'document' => [
                'required',
                'file',
                'mimes:jpeg,jpg,png',
                'max:51200', // 50MB in KB
            ],
            'document_type' => [
                'required',
                'in:birth_certificate,report_card,form_138,good_moral_certificate,other'
            ],
        ];
    }
}
```

### Route

```php
Route::post('/guardian/students/{student}/documents', [GuardianDocumentController::class, 'store'])
    ->name('guardian.students.documents.store');
```

## Testing Requirements

- [ ] Feature test: successful upload
- [ ] Feature test: validation errors (wrong type, too large)
- [ ] Feature test: authorization (guardian must own student)
- [ ] Feature test: file stored in correct location
- [ ] Feature test: database record created
- [ ] Unit test: file naming logic

## Dependencies

- [TICKET-001](./TICKET-001-create-document-model-migration.md) - Document model must exist
- Storage configuration in `config/filesystems.php`

## Notes

- Use 'private' disk for security
- Generate random filenames to prevent conflicts
- Store original filename for user reference

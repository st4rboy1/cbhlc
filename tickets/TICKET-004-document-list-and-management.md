# Ticket #004: Document List and Management UI

**Epic:** [EPIC-001 Document Management System](./EPIC-001-document-management-system.md)

**Type:** Story
**Priority:** Critical
**Estimated Effort:** 1 day
**Status:** ✅ Completed
**Assignee:** Claude

## Description

Create UI for guardians to view, manage, and delete their uploaded student documents with verification status indicators.

## Acceptance Criteria

- [ ] Document list page created at `/guardian/students/{student}/documents`
- [ ] Display all documents for a student
- [ ] Show document type, filename, upload date, file size
- [ ] Display verification status with badge (pending, verified, rejected)
- [ ] Download document button
- [ ] Delete document button (if pending)
- [ ] Empty state when no documents uploaded
- [ ] Responsive design (mobile-friendly)

## Implementation Details

### Page Location

`resources/js/pages/guardian/students/documents/index.tsx`

### Backend Routes

```php
// Get documents
Route::get('/guardian/students/{student}/documents', [GuardianDocumentController::class, 'index'])
    ->name('guardian.students.documents.index');

// Delete document
Route::delete('/guardian/documents/{document}', [GuardianDocumentController::class, 'destroy'])
    ->name('guardian.documents.destroy');

// Download document
Route::get('/guardian/documents/{document}/download', [GuardianDocumentController::class, 'download'])
    ->name('guardian.documents.download');
```

### Controller Methods

```php
public function index(Student $student)
{
    $this->authorize('view', $student);

    $documents = $student->documents()
        ->latest('upload_date')
        ->get();

    return Inertia::render('Guardian/Students/Documents/Index', [
        'student' => $student,
        'documents' => $documents,
    ]);
}

public function destroy(Document $document)
{
    $this->authorize('delete', $document);

    // Only allow deletion if pending
    if ($document->verification_status !== 'pending') {
        return back()->withErrors([
            'document' => 'Cannot delete a verified or rejected document.',
        ]);
    }

    // Delete file from storage
    Storage::disk('private')->delete($document->file_path);

    // Delete database record
    $document->delete();

    return back()->with('success', 'Document deleted successfully.');
}

public function download(Document $document)
{
    $this->authorize('download', $document);

    return Storage::disk('private')->download(
        $document->file_path,
        $document->original_filename
    );
}
```

### Component Structure

```tsx
<DocumentList>
    {documents.map((doc) => (
        <DocumentCard key={doc.id}>
            <DocumentIcon type={doc.document_type} />
            <DocumentInfo>
                <DocumentName>{doc.original_filename}</DocumentName>
                <DocumentMeta>
                    {formatFileSize(doc.file_size)} • {formatDate(doc.upload_date)}
                </DocumentMeta>
            </DocumentInfo>
            <DocumentStatus status={doc.verification_status} />
            <DocumentActions>
                <DownloadButton />
                {doc.verification_status === 'pending' && <DeleteButton />}
            </DocumentActions>
        </DocumentCard>
    ))}
</DocumentList>
```

### Status Badges

- **Pending:** Yellow badge, "Pending Verification"
- **Verified:** Green badge with checkmark, "Verified"
- **Rejected:** Red badge with X, "Rejected" (show verification_notes)

## Testing Requirements

- [ ] Page renders with documents
- [ ] Empty state shows correctly
- [ ] Download button works
- [ ] Delete button works for pending documents
- [ ] Delete disabled for verified/rejected documents
- [ ] Status badges display correctly
- [ ] Responsive layout works on mobile
- [ ] Authorization tests (can't access other students' documents)

## Dependencies

- [TICKET-002](./TICKET-002-implement-document-upload-backend.md) - Backend API
- [TICKET-003](./TICKET-003-build-document-upload-ui.md) - Upload component can be included on this page
- Document Policy for authorization

## Notes

- Add confirmation dialog before delete
- Show verification notes for rejected documents
- Consider pagination if many documents
- Add filter by document type

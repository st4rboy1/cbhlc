# Ticket #005: Document Verification Workflow for Registrar

**Epic:** [EPIC-001 Document Management System](./EPIC-001-document-management-system.md)

**Type:** Story
**Priority:** Critical
**Estimated Effort:** 1.5 days
**Assignee:** TBD

## Description

Implement document verification workflow allowing registrars to review, verify, or reject student documents with preview functionality.

## Acceptance Criteria

- [ ] Registrar can view pending documents
- [ ] Registrar can preview document images
- [ ] Registrar can verify documents
- [ ] Registrar can reject documents with notes
- [ ] Verification status updates in database
- [ ] Guardian receives notification on status change
- [ ] Verification tracked in audit log
- [ ] Document verification integrated into enrollment review page

## Implementation Details

### Backend Routes

```php
// Registrar document routes
Route::prefix('registrar')->name('registrar.')->group(function () {
    Route::get('/documents/pending', [RegistrarDocumentController::class, 'pending'])
        ->name('documents.pending');
    Route::get('/documents/{document}', [RegistrarDocumentController::class, 'show'])
        ->name('documents.show');
    Route::post('/documents/{document}/verify', [RegistrarDocumentController::class, 'verify'])
        ->name('documents.verify');
    Route::post('/documents/{document}/reject', [RegistrarDocumentController::class, 'reject'])
        ->name('documents.reject');
});
```

### Controller Methods

```php
public function verify(Document $document)
{
    $this->authorize('verify', $document);

    $document->update([
        'verification_status' => 'verified',
        'verified_by' => auth()->id(),
        'verified_at' => now(),
    ]);

    // Log activity
    activity()
        ->performedOn($document)
        ->withProperties([
            'document_type' => $document->document_type,
            'student_id' => $document->student_id,
        ])
        ->log('Document verified');

    // Notify guardian
    $document->student->guardian->notify(
        new DocumentVerifiedNotification($document)
    );

    return back()->with('success', 'Document verified successfully.');
}

public function reject(RejectDocumentRequest $request, Document $document)
{
    $this->authorize('reject', $document);

    $document->update([
        'verification_status' => 'rejected',
        'verified_by' => auth()->id(),
        'verified_at' => now(),
        'verification_notes' => $request->notes,
    ]);

    // Log activity
    activity()
        ->performedOn($document)
        ->withProperties([
            'document_type' => $document->document_type,
            'student_id' => $document->student_id,
            'rejection_reason' => $request->notes,
        ])
        ->log('Document rejected');

    // Notify guardian
    $document->student->guardian->notify(
        new DocumentRejectedNotification($document)
    );

    return back()->with('success', 'Document rejected.');
}

public function show(Document $document)
{
    $this->authorize('view', $document);

    // Generate temporary signed URL for file viewing
    $url = Storage::disk('private')->temporaryUrl(
        $document->file_path,
        now()->addMinutes(5)
    );

    return response()->json([
        'document' => $document,
        'url' => $url,
    ]);
}
```

### Validation Request

```php
class RejectDocumentRequest extends FormRequest
{
    public function rules()
    {
        return [
            'notes' => 'required|string|min:10|max:500',
        ];
    }

    public function messages()
    {
        return [
            'notes.required' => 'Please provide a reason for rejection.',
            'notes.min' => 'Rejection reason must be at least 10 characters.',
        ];
    }
}
```

### Frontend Components

**Document Verification Dialog**
`resources/js/components/documents/document-verification-dialog.tsx`

```tsx
interface DocumentVerificationDialogProps {
    document: Document;
    open: boolean;
    onClose: () => void;
    onVerify: () => void;
    onReject: (notes: string) => void;
}
```

**Features:**

- Image preview in modal
- Zoom in/out controls
- Verify button (green)
- Reject button (red) with notes textarea
- Document metadata display
- Student information display

### Integration into Enrollment Review

Add documents section to enrollment show page:
`resources/js/pages/registrar/enrollments/show.tsx`

Display:

- All uploaded documents
- Verification status for each
- Quick verify/reject actions
- Warning if required documents missing

## Testing Requirements

- [ ] Feature test: verify document
- [ ] Feature test: reject document with notes
- [ ] Feature test: authorization (only registrar can verify)
- [ ] Feature test: notifications sent
- [ ] Feature test: activity logged
- [ ] UI test: verification dialog works
- [ ] UI test: image preview works
- [ ] Integration test: workflow in enrollment context

## Dependencies

- [TICKET-001](./TICKET-001-create-document-model-migration.md) - Document model
- [TICKET-004](./TICKET-004-document-list-and-management.md) - Document display
- Notification system for guardian alerts
- Activity log package (Spatie Activity Log)

## Notes

- Use signed URLs for secure file access
- Add keyboard shortcuts (V for verify, R for reject)
- Consider bulk verification for multiple documents
- Add verification statistics to registrar dashboard

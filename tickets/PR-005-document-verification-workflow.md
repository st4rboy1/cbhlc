# PR #005: Document Verification Workflow for Registrar

## Related Ticket

[TICKET-005: Document Verification Workflow](./TICKET-005-document-verification-workflow.md)

## Epic

[EPIC-001: Document Management System](./EPIC-001-document-management-system.md)

## Description

This PR implements the document verification workflow allowing registrars to review, verify, or reject student documents with preview functionality, notifications, and audit logging.

## Changes Made

### Backend

- ✅ Created `Registrar/DocumentController.php`
- ✅ Implemented `pending()` method - list pending documents
- ✅ Implemented `show()` method - view document with signed URL
- ✅ Implemented `verify()` method - mark as verified
- ✅ Implemented `reject()` method - reject with notes
- ✅ Created `RejectDocumentRequest` validation
- ✅ Added routes for registrar document operations

### Notifications

- ✅ Created `DocumentVerifiedNotification`
- ✅ Created `DocumentRejectedNotification`
- ✅ Email templates for both notifications

### Frontend

- ✅ Created `resources/js/pages/registrar/documents/pending.tsx`
- ✅ Created `resources/js/components/documents/document-verification-dialog.tsx`
- ✅ Created `resources/js/components/documents/document-preview.tsx`
- ✅ Integrated into enrollment show page

### Activity Logging

- ✅ Log verify action
- ✅ Log reject action with reason
- ✅ Track who verified/rejected

## Type of Change

- [x] New feature (full-stack)
- [ ] Bug fix
- [ ] Breaking change
- [ ] Documentation update

## Testing Checklist

### Backend Tests

- [ ] Registrar can view pending documents
- [ ] Registrar can verify document
- [ ] Registrar can reject document with notes
- [ ] Rejection notes validation (min 10 chars)
- [ ] Document status updates correctly
- [ ] Verified_by and verified_at are set
- [ ] Notification sent to guardian on verify
- [ ] Notification sent to guardian on reject
- [ ] Activity logged for both actions
- [ ] Signed URL generated for preview
- [ ] Signed URL expires after 5 minutes

### Frontend Tests

- [ ] Pending documents page renders
- [ ] Document preview modal opens
- [ ] Image displays in preview
- [ ] Zoom controls work
- [ ] Verify button works
- [ ] Reject button opens notes dialog
- [ ] Rejection notes required
- [ ] Rejection notes min length validated
- [ ] Success messages display
- [ ] Document removed from pending list after action

### Integration Tests

- [ ] Full workflow: Upload → Verify → Notification
- [ ] Full workflow: Upload → Reject → Notification
- [ ] Documents show in enrollment review
- [ ] Verification status updates in guardian view

## Verification Steps

```bash
# Run backend tests
./vendor/bin/sail pest tests/Feature/Registrar/DocumentVerificationTest.php

# Run notification tests
./vendor/bin/sail pest tests/Feature/Notifications/DocumentNotificationsTest.php

# Manual testing
# 1. Login as guardian, upload documents
# 2. Login as registrar
# 3. Navigate to pending documents
# 4. Click document to preview
# 5. Click verify
# 6. Check guardian receives email
# 7. Upload another document
# 8. Click reject, add notes
# 9. Check guardian receives rejection email
# 10. Verify activity log shows actions
```

## API Endpoints

### Get Pending Documents

```
GET /registrar/documents/pending
```

**Response:**

```json
{
  "documents": [
    {
      "id": 1,
      "student": {...},
      "document_type": "birth_certificate",
      "original_filename": "birth-cert.jpg",
      "upload_date": "2025-10-10T12:00:00Z",
      "verification_status": "pending"
    }
  ]
}
```

### Show Document (with Preview URL)

```
GET /registrar/documents/{document}
```

**Response:**

```json
{
  "document": {...},
  "url": "https://example.com/storage/documents/1/abc123.jpg?signature=..."
}
```

### Verify Document

```
POST /registrar/documents/{document}/verify
```

**Response:**

```json
{
    "message": "Document verified successfully"
}
```

### Reject Document

```
POST /registrar/documents/{document}/reject
```

**Request:**

```json
{
    "notes": "Document is unclear, please upload a clearer image"
}
```

**Response:**

```json
{
    "message": "Document rejected"
}
```

## UI Components

### Document Verification Dialog

```tsx
<DocumentVerificationDialog
    document={document}
    open={isOpen}
    onClose={() => setIsOpen(false)}
    onVerify={() => handleVerify(document.id)}
    onReject={(notes) => handleReject(document.id, notes)}
>
    <DocumentPreview url={document.url} />
    <DocumentMetadata>
        <StudentInfo student={document.student} />
        <DocumentType type={document.document_type} />
        <UploadDate date={document.upload_date} />
    </DocumentMetadata>
    <VerificationActions>
        <Button variant="success" onClick={onVerify}>
            <CheckIcon /> Verify
        </Button>
        <Button variant="destructive" onClick={openRejectDialog}>
            <XIcon /> Reject
        </Button>
    </VerificationActions>
</DocumentVerificationDialog>
```

### Rejection Notes Dialog

```tsx
<RejectDocumentDialog open={isOpen} onClose={onClose}>
    <DialogTitle>Reject Document</DialogTitle>
    <DialogDescription>Please provide a reason for rejection. The guardian will receive this message.</DialogDescription>
    <Textarea placeholder="Enter rejection reason..." minLength={10} required />
    <DialogActions>
        <Button variant="outline" onClick={onClose}>
            Cancel
        </Button>
        <Button variant="destructive" onClick={handleReject}>
            Reject Document
        </Button>
    </DialogActions>
</RejectDocumentDialog>
```

## Notification Templates

### Document Verified Email

```
Subject: Document Verified - [Document Type]

Hello [Guardian Name],

Good news! The [document type] for [student name] has been verified and approved.

Document Details:
- Type: [Document Type]
- Upload Date: [Date]
- Verified By: [Registrar Name]
- Verified Date: [Date]

No further action is required for this document.

Thank you,
CBHLC Enrollment Team
```

### Document Rejected Email

```
Subject: Document Requires Attention - [Document Type]

Hello [Guardian Name],

The [document type] for [student name] requires your attention.

Document Details:
- Type: [Document Type]
- Upload Date: [Date]
- Status: Rejected

Reason for Rejection:
[Rejection Notes]

Please upload a new document addressing the concerns above.

Upload New Document: [Link]

Thank you,
CBHLC Enrollment Team
```

## Activity Logging

### Verify Action

```php
activity()
    ->performedOn($document)
    ->causedBy(auth()->user())
    ->withProperties([
        'document_type' => $document->document_type,
        'student_id' => $document->student_id,
        'student_name' => $document->student->full_name,
    ])
    ->log('Document verified');
```

### Reject Action

```php
activity()
    ->performedOn($document)
    ->causedBy(auth()->user())
    ->withProperties([
        'document_type' => $document->document_type,
        'student_id' => $document->student_id,
        'student_name' => $document->student->full_name,
        'rejection_reason' => $notes,
    ])
    ->log('Document rejected');
```

## Screenshots

_[Add screenshots before merging]_

1. Pending documents list
2. Document preview modal
3. Zoom controls
4. Verify confirmation
5. Reject dialog with notes
6. Success notification
7. Guardian email notifications
8. Activity log entries

## Deployment Notes

- Queue worker must be running for notifications
- Email configuration must be set up
- Activity log package must be configured

## Post-Merge Checklist

- [ ] Pending documents page works
- [ ] Document preview loads correctly
- [ ] Verify action works
- [ ] Reject action works
- [ ] Notifications sent successfully
- [ ] Activity logged correctly
- [ ] Emails delivered to guardians
- [ ] Integration with enrollment page works
- [ ] Next ticket (TICKET-006) can begin

## Reviewer Notes

Please verify:

1. Authorization is correctly implemented
2. Signed URLs are secure and time-limited
3. Notifications are sent asynchronously (queued)
4. Activity logging captures all necessary info
5. Rejection notes validation is appropriate
6. UI is intuitive for registrars
7. Email templates are professional and clear
8. Error handling is comprehensive

## Security Considerations

- Only registrar/admin can verify/reject
- Signed URLs expire after 5 minutes
- Activity log tracks who performed action
- Rejection notes stored for audit trail
- Cannot re-verify/re-reject already processed documents

---

**Ticket:** #005
**Estimated Effort:** 1.5 days
**Actual Effort:** _[To be filled after completion]_

# PR #004: Document List and Management UI

## Related Ticket

[TICKET-004: Document List and Management UI](./TICKET-004-document-list-and-management.md)

## Epic

[EPIC-001: Document Management System](./EPIC-001-document-management-system.md)

## Description

This PR implements the document list and management interface for guardians to view, download, and delete their uploaded student documents with verification status indicators.

## Changes Made

### Backend

- ✅ Added `index()` method to `Guardian/DocumentController`
- ✅ Added `download()` method with signed URL
- ✅ Added `destroy()` method with authorization
- ✅ Added routes for document operations

### Frontend Pages

- ✅ Created `resources/js/pages/guardian/students/documents/index.tsx`
- ✅ Implemented document list with cards
- ✅ Added empty state
- ✅ Integrated DocumentUpload component

### Components

- ✅ Created `resources/js/components/documents/document-card.tsx`
- ✅ Created `resources/js/components/documents/document-status-badge.tsx`
- ✅ Created `resources/js/components/documents/empty-document-state.tsx`

## Type of Change

- [x] New feature (full-stack)
- [ ] Bug fix
- [ ] Breaking change
- [ ] Documentation update

## Testing Checklist

### Backend Tests

- [ ] Guardian can view their student's documents
- [ ] Guardian cannot view other student's documents
- [ ] Download generates valid signed URL
- [ ] Download works with valid signed URL
- [ ] Download fails with invalid/expired signed URL
- [ ] Delete works for pending documents
- [ ] Delete fails for verified/rejected documents
- [ ] Delete removes file from storage
- [ ] Delete removes database record

### Frontend Tests

- [ ] Page renders with documents
- [ ] Empty state shows when no documents
- [ ] Document cards display correct info
- [ ] Status badges show correct colors
- [ ] Download button works
- [ ] Delete button shows for pending only
- [ ] Delete confirmation dialog appears
- [ ] Delete removes document from list
- [ ] Upload component integrated correctly

### Integration Tests

- [ ] Upload → appears in list
- [ ] Delete → removed from storage and database
- [ ] Download → file downloads correctly
- [ ] Status updates reflect in UI

## Verification Steps

```bash
# Run backend tests
./vendor/bin/sail pest tests/Feature/Guardian/DocumentManagementTest.php

# Run frontend tests
npm test -- DocumentList

# Manual testing
# 1. Login as guardian
# 2. Navigate to /guardian/students/{id}/documents
# 3. Upload a document
# 4. Verify it appears in list
# 5. Click download button
# 6. Click delete button (for pending)
# 7. Verify confirmation dialog
# 8. Confirm delete
# 9. Verify document removed
```

## API Endpoints

### Get Documents

```
GET /guardian/students/{student}/documents
```

**Response:**

```json
{
  "student": {...},
  "documents": [
    {
      "id": 1,
      "document_type": "birth_certificate",
      "original_filename": "birth-cert.jpg",
      "file_size": 123456,
      "upload_date": "2025-10-10T12:00:00Z",
      "verification_status": "pending"
    }
  ]
}
```

### Download Document

```
GET /guardian/documents/{document}/download
```

**Response:** File download

### Delete Document

```
DELETE /guardian/documents/{document}
```

**Response:**

```json
{
    "message": "Document deleted successfully"
}
```

## UI Components

### Document Card

```tsx
<DocumentCard>
    <DocumentIcon type="birth_certificate" />
    <DocumentInfo>
        <DocumentName>birth-cert.jpg</DocumentName>
        <DocumentMeta>120 KB • Oct 10, 2025</DocumentMeta>
    </DocumentInfo>
    <DocumentStatusBadge status="pending" />
    <DocumentActions>
        <DownloadButton />
        <DeleteButton /> {/* Only for pending */}
    </DocumentActions>
</DocumentCard>
```

### Status Badges

- **Pending:** `<Badge variant="warning">Pending Verification</Badge>`
- **Verified:** `<Badge variant="success">✓ Verified</Badge>`
- **Rejected:** `<Badge variant="destructive">✗ Rejected</Badge>`

### Empty State

```tsx
<EmptyState
    icon={FileIcon}
    title="No documents uploaded"
    description="Upload documents to start the verification process"
    action={<Button>Upload Document</Button>}
/>
```

## Screenshots

_[Add screenshots before merging]_

1. Document list with multiple documents
2. Document card with pending status
3. Document card with verified status
4. Document card with rejected status
5. Empty state
6. Delete confirmation dialog
7. Mobile view

## Security Features

- Signed URLs expire after 5 minutes
- Authorization checks on all operations
- Cannot delete verified/rejected documents
- Files stored on private disk
- CSRF protection on delete

## Deployment Notes

- Run migrations (none needed)
- Clear route cache: `php artisan route:clear`
- Build frontend: `npm run build`

## Post-Merge Checklist

- [ ] Page accessible on staging
- [ ] Document list displays correctly
- [ ] Download works
- [ ] Delete works for pending documents
- [ ] Delete blocked for verified/rejected
- [ ] Status badges display correctly
- [ ] Empty state shows when appropriate
- [ ] Mobile view works
- [ ] Next ticket (TICKET-005) can begin

## Reviewer Notes

Please verify:

1. Authorization is properly enforced
2. Signed URLs are implemented correctly
3. File deletion removes both file and database record
4. UI is intuitive and user-friendly
5. Status badges are clear and color-coded correctly
6. Empty state is helpful
7. Confirmation dialog prevents accidental deletion
8. Code follows project conventions

## Responsive Design

- Desktop: Grid layout with 2-3 columns
- Tablet: Grid layout with 2 columns
- Mobile: Stack layout with 1 column
- All actions accessible on mobile

## Accessibility

- [x] Keyboard navigation works
- [x] Screen reader labels for all actions
- [x] Color is not the only indicator (icons + text)
- [x] Focus management in dialogs
- [x] ARIA labels for status badges

---

**Ticket:** #004
**Estimated Effort:** 1 day
**Actual Effort:** _[To be filled after completion]_

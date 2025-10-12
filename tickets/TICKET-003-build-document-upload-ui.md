# Ticket #003: Build Document Upload UI Component

**Epic:** [EPIC-001 Document Management System](./EPIC-001-document-management-system.md)

**Type:** Story
**Priority:** Critical
**Estimated Effort:** 1 day
**Status:** ✅ Completed
**Assignee:** Claude

## Description

Create React component for document upload with drag-and-drop functionality and progress indicators for guardians uploading student documents.

## Acceptance Criteria

- [ ] `DocumentUpload` component created
- [ ] Drag-and-drop file upload works
- [ ] Click to browse file works
- [ ] File type validation on frontend
- [ ] File size validation on frontend (50MB max)
- [ ] Upload progress indicator shown
- [ ] Success/error toast notifications
- [ ] Document type selector (dropdown)
- [ ] Preview uploaded image before submit
- [ ] Component integrated into student enrollment flow

## Implementation Details

### Component Location

`resources/js/components/documents/document-upload.tsx`

### Props Interface

```tsx
interface DocumentUploadProps {
    studentId: number;
    documentType: string;
    onSuccess?: (document: Document) => void;
    onError?: (error: string) => void;
}
```

### Key Features

- Use HTML5 drag-and-drop API
- Show file preview for images
- Display file size in human-readable format
- Progress bar during upload
- Disable upload button during processing
- Clear file after successful upload

### Example Usage

```tsx
<DocumentUpload
    studentId={student.id}
    documentType="birth_certificate"
    onSuccess={(doc) => {
        toast.success('Document uploaded successfully!');
        refreshDocuments();
    }}
    onError={(error) => {
        toast.error(error);
    }}
/>
```

### Validation Messages

- "Please select a JPEG or PNG file"
- "File size must be less than 50MB"
- "Document uploaded successfully"
- "Failed to upload document. Please try again."

## Testing Requirements

- [ ] Component renders correctly
- [ ] Drag-and-drop works
- [ ] File picker works
- [ ] Validation messages display
- [ ] Upload progress shows
- [ ] Success callback fires
- [ ] Error handling works
- [ ] Accessibility (keyboard navigation, ARIA labels)

## Dependencies

- [TICKET-002](./TICKET-002-implement-document-upload-backend.md) - Backend API must exist
- shadcn/ui components: Dialog, Button, Progress, Toast
- File upload library or custom implementation

## Notes

- Consider using `react-dropzone` library for drag-and-drop
- Show image preview using FileReader API
- Handle multiple file selection (optional)
- Add file replacement functionality

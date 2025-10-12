# PR #003: Build Document Upload UI Component

## Related Ticket

[TICKET-003: Build Document Upload UI Component](./TICKET-003-build-document-upload-ui.md)

## Epic

[EPIC-001: Document Management System](./EPIC-001-document-management-system.md)

## Description

This PR implements a React component for document upload with drag-and-drop functionality, progress indicators, and comprehensive validation feedback for guardians uploading student documents.

## Changes Made

### Components

- ✅ Created `resources/js/components/documents/document-upload.tsx`
- ✅ Implemented drag-and-drop file upload
- ✅ Added click-to-browse fallback
- ✅ Implemented upload progress indicator
- ✅ Added file preview for images
- ✅ Implemented client-side validation (type, size)
- ✅ Added document type selector dropdown

### Utilities

- ✅ Created `resources/js/lib/file-utils.ts` for file size formatting
- ✅ Added file validation helper functions

### Integration

- ✅ Integrated component into enrollment workflow
- ✅ Added to student profile page

## Type of Change

- [x] New feature (frontend component)
- [ ] Bug fix
- [ ] Breaking change
- [ ] Documentation update

## Testing Checklist

### Component Tests

- [ ] Component renders correctly
- [ ] Drag-and-drop works
- [ ] Click to browse works
- [ ] File type validation displays error for invalid types
- [ ] File size validation displays error for files over 50MB
- [ ] Image preview shows for valid images
- [ ] Upload progress shows during upload
- [ ] Success callback fires on successful upload
- [ ] Error callback fires on failed upload
- [ ] Component disabled during upload

### User Interaction Tests

- [ ] User can drag file onto component
- [ ] User can click to select file
- [ ] User can select document type from dropdown
- [ ] User sees preview before upload
- [ ] User sees progress during upload
- [ ] User sees success message after upload
- [ ] User sees error message on failure
- [ ] User can upload another file after success

### Accessibility Tests

- [ ] Keyboard navigation works (Tab, Enter)
- [ ] Screen reader announces file selection
- [ ] Error messages are announced
- [ ] File input has proper ARIA labels
- [ ] Focus management works correctly

## Verification Steps

```bash
# Run component tests
npm test -- DocumentUpload

# Manual testing in browser
# 1. Navigate to student profile page
# 2. Click "Upload Document" button
# 3. Try drag-and-drop
# 4. Try click-to-browse
# 5. Try invalid file types (PDF, TXT)
# 6. Try file over 50MB
# 7. Upload valid JPEG/PNG
# 8. Verify progress indicator
# 9. Verify success message
```

## Dependencies

- [PR-002](./PR-002-implement-document-upload-backend.md) - Backend API must exist

## Code Examples

### Component Usage

```tsx
import { DocumentUpload } from '@/components/documents/document-upload';

<DocumentUpload
    studentId={student.id}
    documentType="birth_certificate"
    onSuccess={(document) => {
        toast.success('Document uploaded successfully!');
        refreshDocuments();
    }}
    onError={(error) => {
        toast.error(error);
    }}
/>;
```

### Props Interface

```tsx
interface DocumentUploadProps {
    studentId: number;
    documentType: string;
    onSuccess?: (document: Document) => void;
    onError?: (error: string) => void;
    maxSize?: number; // bytes, default 52428800 (50MB)
    acceptedTypes?: string[]; // default ['image/jpeg', 'image/png']
}
```

## UI/UX Features

### Upload States

- **Idle:** "Drag and drop file here, or click to browse"
- **Dragging:** Blue border, "Drop file here"
- **Uploading:** Progress bar with percentage
- **Success:** Green checkmark, success message
- **Error:** Red X, error message

### Validation Messages

- Invalid type: "Please select a JPEG or PNG file"
- Too large: "File size must be less than 50MB"
- Upload error: "Failed to upload document. Please try again."

### Image Preview

- Shows thumbnail of selected image
- Displays filename and file size
- Option to change selection before upload

## Screenshots

_[Add screenshots before merging]_

1. Idle state with drag-and-drop area
2. File selected with preview
3. Upload progress
4. Success state
5. Error state

## Styling

- Uses Tailwind CSS utilities
- Uses shadcn/ui components (Button, Progress, Card)
- Consistent with application design system
- Responsive design (mobile-friendly)

## Documentation

- Added component README
- Added props documentation
- Added usage examples
- Updated Storybook (if applicable)

## Deployment Notes

- No backend changes
- Ensure frontend assets are built: `npm run build`
- No environment variables needed

## Post-Merge Checklist

- [ ] Component renders correctly on staging
- [ ] Drag-and-drop works in all browsers
- [ ] File upload completes successfully
- [ ] Validation messages display correctly
- [ ] Mobile view works correctly
- [ ] No console errors
- [ ] Next ticket (TICKET-004) can begin

## Reviewer Notes

Please verify:

1. Component follows React best practices
2. TypeScript types are correct
3. Accessibility is implemented correctly
4. Error handling is comprehensive
5. UI/UX is intuitive and user-friendly
6. Code is reusable and well-structured
7. No unnecessary re-renders
8. Props are properly validated

## Browser Compatibility

- [x] Chrome (latest)
- [x] Firefox (latest)
- [x] Safari (latest)
- [x] Edge (latest)
- [x] Mobile Safari
- [x] Mobile Chrome

---

**Ticket:** #003
**Estimated Effort:** 1 day
**Actual Effort:** _[To be filled after completion]_

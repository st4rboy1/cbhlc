# PR #002: Implement Document Upload Backend

## Related Ticket

[TICKET-002: Implement Document Upload Backend](./TICKET-002-implement-document-upload-backend.md)

## Epic

[EPIC-001: Document Management System](./EPIC-001-document-management-system.md)

## Description

This PR implements the backend API for document upload functionality, allowing guardians to securely upload student documents (JPEG/PNG, max 50MB) with proper validation, storage, and database record creation.

## Changes Made

### Controller

- ✅ Created `app/Http/Controllers/Guardian/DocumentController.php`
- ✅ Implemented `store()` method with file upload handling
- ✅ Added authorization check (guardian owns student)
- ✅ Implemented secure file storage with random filenames
- ✅ Created database record with metadata

### Validation

- ✅ Created `app/Http/Requests/Guardian/StoreDocumentRequest.php`
- ✅ File type validation (JPEG, PNG only)
- ✅ File size validation (max 50MB)
- ✅ Document type validation (enum values)

### Routes

- ✅ Added `POST /guardian/students/{student}/documents` route
- ✅ Applied auth middleware
- ✅ Applied role:guardian middleware

### Policy

- ✅ Created `app/Policies/DocumentPolicy.php`
- ✅ Implemented `uploadDocument()` authorization method
- ✅ Registered policy in `AuthServiceProvider`

### Storage Configuration

- ✅ Verified `private` disk configuration in `config/filesystems.php`
- ✅ Ensured secure file storage (not publicly accessible)

## Type of Change

- [x] New feature (backend API)
- [ ] Bug fix
- [ ] Breaking change
- [ ] Documentation update

## Testing Checklist

### Feature Tests

- [ ] Authorized guardian can upload document
- [ ] Unauthorized user cannot upload document
- [ ] Guardian cannot upload for other guardian's student
- [ ] File validation rejects invalid types (PDF, TXT, etc.)
- [ ] File validation rejects files over 50MB
- [ ] Document type validation works
- [ ] File is stored in correct location
- [ ] Database record is created with correct data
- [ ] Original filename is preserved in database
- [ ] Returns JSON response with document info

### Unit Tests

- [ ] StoreDocumentRequest validation rules work
- [ ] DocumentPolicy authorization logic correct
- [ ] File naming logic generates unique names

### Manual Testing

```bash
# Test with Postman/curl
curl -X POST http://localhost/guardian/students/1/documents \
  -H "Authorization: Bearer {token}" \
  -F "document=@/path/to/test.jpg" \
  -F "document_type=birth_certificate"

# Should return 200 with document JSON
# Should create file in storage/app/private/documents/1/
# Should create database record
```

## Verification Steps

```bash
# Run feature tests
./vendor/bin/sail pest tests/Feature/Guardian/DocumentUploadTest.php

# Test file upload manually
./vendor/bin/sail artisan tinker
>>> $user = User::role('guardian')->first();
>>> $student = $user->students->first();
>>> auth()->login($user);
>>> // Upload via HTTP test or Postman

# Verify file stored
./vendor/bin/sail exec laravel.test ls -la storage/app/private/documents/1/

# Verify database record
./vendor/bin/sail artisan tinker
>>> Document::latest()->first();
```

## Dependencies

- [PR-001](./PR-001-create-document-model-migration.md) - Document model must exist

## Breaking Changes

None

## Security Considerations

- Files stored on `private` disk (not publicly accessible)
- Random filename generation prevents conflicts and guessing
- Authorization check ensures guardian owns student
- File type validation on server-side
- File size limit enforced server-side
- MIME type validation (not just extension)

## API Documentation

### Endpoint

```
POST /guardian/students/{student}/documents
```

### Headers

```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

### Request Body

```
document: file (required, jpeg/png, max 50MB)
document_type: string (required, one of: birth_certificate, report_card, form_138, good_moral_certificate, other)
```

### Response (Success - 200)

```json
{
    "message": "Document uploaded successfully",
    "document": {
        "id": 1,
        "student_id": 1,
        "document_type": "birth_certificate",
        "original_filename": "birth-cert.jpg",
        "file_size": 123456,
        "verification_status": "pending",
        "upload_date": "2025-10-10T12:00:00.000000Z",
        "created_at": "2025-10-10T12:00:00.000000Z"
    }
}
```

### Response (Validation Error - 422)

```json
{
    "message": "The document must be a file of type: jpeg, png.",
    "errors": {
        "document": ["The document must be a file of type: jpeg, png."]
    }
}
```

### Response (Unauthorized - 403)

```json
{
    "message": "This action is unauthorized."
}
```

## Screenshots

N/A (Backend API)

## Documentation

- Added docblocks to controller methods
- Added validation rule descriptions
- Updated API documentation

## Deployment Notes

- Ensure `storage/app/private/documents` directory exists and is writable
- Verify `private` disk configuration in production
- No database migrations needed (uses existing documents table)

## Post-Merge Checklist

- [ ] API endpoint accessible on staging
- [ ] File upload works on staging
- [ ] Files stored securely (not publicly accessible)
- [ ] Authorization works correctly
- [ ] Validation messages are user-friendly
- [ ] Next ticket (TICKET-003) can begin

## Reviewer Notes

Please verify:

1. Authorization logic is correct and secure
2. File validation is comprehensive (type, size, MIME)
3. File storage is secure (private disk, random names)
4. Error handling is appropriate
5. Code follows Laravel conventions
6. Tests cover all scenarios (success, validation errors, authorization)

---

**Ticket:** #002
**Estimated Effort:** 1 day
**Actual Effort:** _[To be filled after completion]_

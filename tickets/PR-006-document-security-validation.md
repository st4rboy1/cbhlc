# PR #006: Document Security and Advanced Validation

## Related Ticket

[TICKET-006: Document Security and Advanced Validation](./TICKET-006-document-security-validation.md)

## Epic

[EPIC-001: Document Management System](./EPIC-001-document-management-system.md)

## Description

This PR enhances document upload security and validation with server-side MIME type verification, file content checking, rate limiting, comprehensive authorization policies, and access audit logging.

## Changes Made

### Enhanced Validation

- ✅ Updated `StoreDocumentRequest` with advanced validation
- ✅ Added server-side MIME type verification (not just extension)
- ✅ Added file content verification using `getimagesize()`
- ✅ Added file sanitization for filenames

### Security Policies

- ✅ Completed `DocumentPolicy` with all authorization methods
- ✅ Implemented `view()`, `uploadDocument()`, `delete()`, `verify()`, `reject()`
- ✅ Registered policy in `AuthServiceProvider`

### Rate Limiting

- ✅ Added `document-uploads` rate limiter (5 uploads per minute)
- ✅ Applied to upload route

### Signed URLs

- ✅ Implemented temporary signed URLs for document access
- ✅ Added `download()` method with signature validation
- ✅ 5-minute expiration on download links

### Audit Logging

- ✅ Log all document uploads
- ✅ Log all document views/downloads
- ✅ Log verification actions
- ✅ Track IP addresses and user agents

### File Storage Security

- ✅ Verified private disk configuration
- ✅ Added .htaccess rules to prevent direct access
- ✅ Ensured no public URL generation

## Type of Change

- [x] Security enhancement
- [x] Bug fix (validation bypass)
- [ ] Breaking change
- [ ] Documentation update

## Testing Checklist

### Validation Tests

- [ ] Rejects files with fake JPEG extension but different content
- [ ] Rejects non-image files renamed to .jpg
- [ ] Accepts valid JPEG files
- [ ] Accepts valid PNG files
- [ ] Rejects files over 50MB
- [ ] Rejects corrupted image files
- [ ] Sanitizes malicious filenames

### Authorization Tests

- [ ] Guardian can upload for their student
- [ ] Guardian cannot upload for other's student
- [ ] Guardian can view their documents
- [ ] Guardian cannot view others' documents
- [ ] Guardian can delete pending documents only
- [ ] Registrar can view all documents
- [ ] Registrar can verify/reject documents
- [ ] Student cannot upload documents

### Rate Limiting Tests

- [ ] 5 uploads succeed
- [ ] 6th upload fails with 429
- [ ] Rate limit resets after 1 minute
- [ ] Rate limit per user, not global

### Signed URL Tests

- [ ] Valid signed URL allows download
- [ ] Invalid signature rejects download
- [ ] Expired signature rejects download
- [ ] Cannot modify URL parameters
- [ ] Signed URL works only for authorized user

### Audit Logging Tests

- [ ] Upload action logged
- [ ] Download action logged
- [ ] Verification action logged
- [ ] IP address captured
- [ ] User agent captured

## Verification Steps

```bash
# Run security tests
./vendor/bin/sail pest tests/Feature/Security/DocumentSecurityTest.php

# Test validation
./vendor/bin/sail pest tests/Unit/Validation/DocumentValidationTest.php

# Test rate limiting
for i in {1..6}; do
  curl -X POST http://localhost/guardian/students/1/documents \
    -H "Authorization: Bearer {token}" \
    -F "document=@test.jpg" \
    -F "document_type=birth_certificate"
done
# 6th request should return 429

# Test signed URLs
./vendor/bin/sail artisan tinker
>>> $doc = Document::first();
>>> $url = URL::temporarySignedRoute('documents.download', now()->addMinutes(5), ['document' => $doc->id]);
>>> // Test URL in browser
>>> // Modify signature in URL - should fail
>>> // Wait 5 minutes - should fail

# Test file content validation
# Create fake JPEG (text file renamed)
echo "This is not an image" > fake.jpg
# Upload should fail

# Check activity log
./vendor/bin/sail artisan tinker
>>> Activity::where('subject_type', 'App\\Models\\Document')->latest()->get();
```

## Enhanced Validation Logic

### MIME Type Verification

```php
'document' => [
    'required',
    'file',
    'mimes:jpeg,jpg,png',
    'max:51200',
    function ($attribute, $value, $fail) {
        // Verify actual file content, not just extension
        $mimeType = $value->getMimeType();
        $allowedMimes = ['image/jpeg', 'image/png'];

        if (!in_array($mimeType, $allowedMimes)) {
            $fail('The file must be a valid image (JPEG or PNG).');
        }

        // Check if file is actually an image
        try {
            $image = getimagesize($value->path());
            if ($image === false) {
                $fail('The file is not a valid image.');
            }
        } catch (\Exception $e) {
            $fail('The file could not be validated.');
        }
    },
],
```

### Filename Sanitization

```php
private function sanitizeFilename(string $filename): string
{
    // Remove special characters
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);

    // Limit length
    $filename = substr($filename, 0, 255);

    return $filename;
}
```

## Security Policy

### DocumentPolicy

```php
class DocumentPolicy
{
    public function view(User $user, Document $document): bool
    {
        // Guardian can view their student's documents
        if ($user->hasRole('guardian')) {
            return $document->student->guardian_id === $user->id;
        }

        // Registrar and admin can view all
        return $user->hasAnyRole(['registrar', 'administrator', 'super_admin']);
    }

    public function uploadDocument(User $user, Student $student): bool
    {
        // Only guardian who owns the student
        return $user->hasRole('guardian') && $student->guardian_id === $user->id;
    }

    public function delete(User $user, Document $document): bool
    {
        // Only guardian who owns, and only if pending
        return $user->hasRole('guardian')
            && $document->student->guardian_id === $user->id
            && $document->verification_status === 'pending';
    }

    public function verify(User $user, Document $document): bool
    {
        return $user->hasAnyRole(['registrar', 'administrator', 'super_admin']);
    }

    public function reject(User $user, Document $document): bool
    {
        return $user->hasAnyRole(['registrar', 'administrator', 'super_admin']);
    }
}
```

## Rate Limiting Configuration

```php
// RouteServiceProvider
RateLimiter::for('document-uploads', function (Request $request) {
    return Limit::perMinute(5)
        ->by($request->user()?->id ?: $request->ip())
        ->response(function () {
            return response()->json([
                'message' => 'Too many upload attempts. Please try again later.'
            ], 429);
        });
});

// Route
Route::post('/guardian/students/{student}/documents', [GuardianDocumentController::class, 'store'])
    ->middleware('throttle:document-uploads');
```

## Audit Logging

### Upload Logging

```php
activity()
    ->performedOn($document)
    ->causedBy(auth()->user())
    ->withProperties([
        'action' => 'uploaded',
        'document_type' => $document->document_type,
        'file_size' => $document->file_size,
        'mime_type' => $document->mime_type,
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ])
    ->log('Document uploaded');
```

### Download Logging

```php
activity()
    ->performedOn($document)
    ->causedBy(auth()->user())
    ->withProperties([
        'action' => 'downloaded',
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ])
    ->log('Document downloaded');
```

## Security Attack Vectors Mitigated

1. **File Type Spoofing:** Server-side MIME check prevents renamed files
2. **Malicious Files:** Content verification ensures actual image files
3. **Brute Force Uploads:** Rate limiting prevents abuse
4. **Unauthorized Access:** Policy enforcement at multiple layers
5. **Direct File Access:** Private disk + signed URLs
6. **URL Tampering:** Signature validation
7. **Session Hijacking:** Activity logging tracks suspicious behavior

## Deployment Notes

- Clear route cache: `php artisan route:clear`
- Clear policy cache: `php artisan cache:clear`
- Ensure private disk is configured
- Verify rate limiter is active
- Check activity log storage

## Post-Merge Checklist

- [ ] File content validation works
- [ ] Rate limiting prevents abuse
- [ ] Signed URLs work correctly
- [ ] Policy authorization enforced
- [ ] Activity logging captures all actions
- [ ] No direct file access possible
- [ ] Security tests pass
- [ ] Document management epic complete!

## Reviewer Notes

Please verify:

1. All security attack vectors are addressed
2. Validation cannot be bypassed
3. Rate limiting configuration is appropriate
4. Policy logic is correct and secure
5. Audit logging is comprehensive
6. No information leakage in error messages
7. Signed URLs are properly secured
8. Code follows security best practices

## Security Checklist

- [x] Server-side validation (not just client-side)
- [x] MIME type verification
- [x] File content verification
- [x] Rate limiting
- [x] Authorization policies
- [x] Signed URLs with expiration
- [x] Private file storage
- [x] Audit logging
- [x] Input sanitization
- [x] No direct file access
- [x] CSRF protection (Laravel default)
- [x] SQL injection prevention (Eloquent)
- [x] XSS prevention (React + Laravel)

---

**Ticket:** #006
**Estimated Effort:** 1 day
**Actual Effort:** _[To be filled after completion]_
**Epic Status:** ✅ COMPLETE - Document Management System

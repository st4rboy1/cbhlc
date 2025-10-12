# Ticket #006: Document Security and Advanced Validation

**Epic:** [EPIC-001 Document Management System](./EPIC-001-document-management-system.md)

**Type:** Story
**Priority:** High
**Estimated Effort:** 1 day
**Assignee:** TBD

## Description

Implement security measures and advanced validation for document uploads including server-side mime type verification, file sanitization, and access control.

## Acceptance Criteria

- [ ] Server-side mime type validation (not just extension)
- [ ] File content verification (actual image check)
- [ ] Rate limiting on document uploads
- [ ] Signed URLs for document access with expiration
- [ ] Authorization policies for document access
- [ ] Prevent direct file access via URL guessing
- [ ] File size limits enforced server-side
- [ ] Audit logging for all document access

## Implementation Details

### Enhanced Validation Rule

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
            'document_type' => [
                'required',
                'in:birth_certificate,report_card,form_138,good_moral_certificate,other'
            ],
        ];
    }
}
```

### Document Policy

Create `app/Policies/DocumentPolicy.php`:

```php
class DocumentPolicy
{
    public function view(User $user, Document $document): bool
    {
        // Guardian can view their student's documents
        if ($user->hasRole('guardian')) {
            return $document->student->guardian_id === $user->id;
        }

        // Registrar and admin can view all documents
        return $user->hasAnyRole(['registrar', 'administrator', 'super_admin']);
    }

    public function uploadDocument(User $user, Student $student): bool
    {
        // Only guardian who owns the student
        return $user->hasRole('guardian') && $student->guardian_id === $user->id;
    }

    public function delete(User $user, Document $document): bool
    {
        // Only guardian who owns the document, and only if pending
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

### Signed URL for Secure Access

```php
public function show(Document $document)
{
    $this->authorize('view', $document);

    // Generate signed URL valid for 5 minutes
    $url = URL::temporarySignedRoute(
        'documents.download',
        now()->addMinutes(5),
        ['document' => $document->id]
    );

    // Log access
    activity()
        ->performedOn($document)
        ->withProperties([
            'action' => 'viewed',
            'ip_address' => request()->ip(),
        ])
        ->log('Document accessed');

    return response()->json([
        'document' => $document,
        'url' => $url,
    ]);
}

public function download(Request $request, Document $document)
{
    if (!$request->hasValidSignature()) {
        abort(403, 'Invalid or expired download link.');
    }

    $this->authorize('view', $document);

    // Log download
    activity()
        ->performedOn($document)
        ->withProperties([
            'action' => 'downloaded',
            'ip_address' => request()->ip(),
        ])
        ->log('Document downloaded');

    return Storage::disk('private')->download(
        $document->file_path,
        $document->original_filename
    );
}
```

### Rate Limiting

Add to `app/Providers/RouteServiceProvider.php`:

```php
RateLimiter::for('document-uploads', function (Request $request) {
    return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip())
        ->response(function () {
            return response()->json([
                'message' => 'Too many upload attempts. Please try again later.'
            ], 429);
        });
});
```

Apply to route:

```php
Route::post('/guardian/students/{student}/documents', [GuardianDocumentController::class, 'store'])
    ->middleware('throttle:document-uploads')
    ->name('guardian.students.documents.store');
```

### File Storage Security

Update `config/filesystems.php`:

```php
'disks' => [
    'documents' => [
        'driver' => 'local',
        'root' => storage_path('app/documents'),
        'throw' => false,
        'visibility' => 'private', // Important: prevent public access
    ],
],
```

### Prevent Direct Access

Add to `.htaccess` or web server config:

```apache
# Prevent direct access to storage directory
<Directory /path/to/storage>
    Require all denied
</Directory>
```

### File Sanitization

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

## Testing Requirements

- [ ] Test mime type validation with fake images
- [ ] Test rate limiting (6 requests should fail)
- [ ] Test authorization (unauthorized access blocked)
- [ ] Test signed URL expiration
- [ ] Test signed URL tampering
- [ ] Test file content validation
- [ ] Test audit logging
- [ ] Security test: attempt direct file access
- [ ] Security test: attempt path traversal

## Dependencies

- [TICKET-002](./TICKET-002-implement-document-upload-backend.md) - Base upload functionality
- Spatie Activity Log for audit trail
- DocumentPolicy registered in AuthServiceProvider

## Notes

- Consider adding virus scanning (ClamAV) for production
- Monitor failed upload attempts for suspicious activity
- Regular audit of document access logs
- Consider encrypting files at rest for sensitive documents
- Add CSRF protection (already handled by Laravel)

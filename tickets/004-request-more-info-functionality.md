# Request More Info Functionality for Admins

**Status:** Not Started
**Priority:** Medium
**Type:** Feature
**Estimated Effort:** 10-12 hours

## Description

Add ability for admins/registrars to request additional information or documents from guardians during enrollment review, instead of immediately rejecting.

## Referenced In

- GUARDIAN_USER_JOURNEY.md (Step 6, line 274)

## Requirements

### Database Changes

Add fields to `enrollments` table:

```php
Schema::table('enrollments', function (Blueprint $table) {
    $table->timestamp('info_requested_at')->nullable();
    $table->foreignId('info_requested_by')->nullable()->constrained('users');
    $table->text('info_request_message')->nullable();
    $table->timestamp('info_provided_at')->nullable();
    $table->text('info_response_message')->nullable();
});
```

### Backend

- [ ] Migration to add columns
- [ ] New routes:
    - `POST /super-admin/enrollments/{enrollment}/request-info`
    - `POST /registrar/enrollments/{enrollment}/request-info`
    - `POST /guardian/enrollments/{enrollment}/provide-info`
- [ ] Request validation (message required, 10-1000 characters)
- [ ] Controller methods in `SuperAdmin\EnrollmentController` and `Registrar\EnrollmentController`
- [ ] Guardian response controller method
- [ ] Email notification: `App\Mail\EnrollmentInfoRequested`
- [ ] Email notification: `App\Mail\EnrollmentInfoProvided`

### Admin Interface

**File:** `resources/js/pages/super-admin/enrollments/show.tsx`

Add "Request More Info" button:

```typescript
{enrollment.status === 'pending' && !enrollment.info_requested_at && (
    <Button variant="outline" onClick={() => setShowRequestInfoModal(true)}>
        <MessageSquare className="mr-2 h-4 w-4" />
        Request More Info
    </Button>
)}
```

Modal component for request:

```typescript
<Dialog open={showRequestInfoModal} onOpenChange={setShowRequestInfoModal}>
    <DialogContent>
        <DialogHeader>
            <DialogTitle>Request Additional Information</DialogTitle>
            <DialogDescription>
                Ask the guardian to provide more details or upload additional documents.
            </DialogDescription>
        </DialogHeader>
        <form onSubmit={handleRequestInfo}>
            <div className="space-y-4">
                <div>
                    <Label htmlFor="message">Message to Guardian</Label>
                    <Textarea
                        id="message"
                        value={requestMessage}
                        onChange={(e) => setRequestMessage(e.target.value)}
                        placeholder="Please explain what additional information is needed..."
                        rows={5}
                        required
                    />
                </div>
            </div>
            <DialogFooter>
                <Button type="button" variant="outline" onClick={() => setShowRequestInfoModal(false)}>
                    Cancel
                </Button>
                <Button type="submit">Send Request</Button>
            </DialogFooter>
        </form>
    </DialogContent>
</Dialog>
```

### Guardian Interface

**File:** `resources/js/pages/guardian/enrollments/show.tsx`

Display info request:

```typescript
{enrollment.info_requested_at && !enrollment.info_provided_at && (
    <Card className="border-yellow-200 bg-yellow-50">
        <CardHeader>
            <CardTitle className="flex items-center gap-2 text-yellow-800">
                <AlertCircle className="h-5 w-5" />
                Additional Information Requested
            </CardTitle>
        </CardHeader>
        <CardContent>
            <p className="mb-4 text-yellow-800">{enrollment.info_request_message}</p>
            <p className="mb-2 text-sm text-yellow-700">
                Requested on: {formatDate(enrollment.info_requested_at)}
            </p>
            <Button onClick={() => setShowProvideInfoModal(true)}>
                Provide Information
            </Button>
        </CardContent>
    </Card>
)}
```

Response modal:

```typescript
<Dialog open={showProvideInfoModal} onOpenChange={setShowProvideInfoModal}>
    <DialogContent>
        <DialogHeader>
            <DialogTitle>Provide Additional Information</DialogTitle>
            <DialogDescription>
                Respond to the admin's request for more information.
            </DialogDescription>
        </DialogHeader>
        <div className="mb-4 rounded bg-muted p-4">
            <p className="text-sm font-medium">Admin's Request:</p>
            <p className="text-sm">{enrollment.info_request_message}</p>
        </div>
        <form onSubmit={handleProvideInfo}>
            <div className="space-y-4">
                <div>
                    <Label htmlFor="response">Your Response</Label>
                    <Textarea
                        id="response"
                        value={responseMessage}
                        onChange={(e) => setResponseMessage(e.target.value)}
                        placeholder="Provide the requested information..."
                        rows={5}
                        required
                    />
                </div>
                <div>
                    <Label>Upload Additional Documents (Optional)</Label>
                    <Input
                        type="file"
                        multiple
                        accept=".pdf,.jpg,.jpeg,.png"
                        onChange={handleFileUpload}
                    />
                </div>
            </div>
            <DialogFooter>
                <Button type="button" variant="outline" onClick={() => setShowProvideInfoModal(false)}>
                    Cancel
                </Button>
                <Button type="submit">Submit Response</Button>
            </DialogFooter>
        </form>
    </DialogContent>
</Dialog>
```

### Controller Implementation

**File:** `app/Http/Controllers/SuperAdmin/EnrollmentController.php`

```php
public function requestInfo(Request $request, Enrollment $enrollment)
{
    $request->validate([
        'message' => 'required|string|min:10|max:1000',
    ]);

    if ($enrollment->status !== EnrollmentStatus::PENDING) {
        return back()->withErrors(['error' => 'Can only request info for pending enrollments.']);
    }

    $enrollment->update([
        'info_requested_at' => now(),
        'info_requested_by' => Auth::id(),
        'info_request_message' => $request->message,
    ]);

    // Send email to guardian
    $guardian = $enrollment->guardian;
    if ($guardian && $guardian->user && $guardian->user->email) {
        Mail::to($guardian->user->email)
            ->queue(new EnrollmentInfoRequested($enrollment));
    }

    return back()->with('success', 'Information request sent to guardian.');
}
```

**File:** `app/Http/Controllers/Guardian/EnrollmentController.php`

```php
public function provideInfo(Request $request, Enrollment $enrollment)
{
    // Authorization check
    $guardian = Guardian::where('user_id', Auth::id())->firstOrFail();
    if ($enrollment->guardian_id !== $guardian->id) {
        abort(404);
    }

    $request->validate([
        'response' => 'required|string|min:10|max:2000',
        'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
    ]);

    $enrollment->update([
        'info_provided_at' => now(),
        'info_response_message' => $request->response,
    ]);

    // Handle document uploads if any
    if ($request->hasFile('documents')) {
        foreach ($request->file('documents') as $file) {
            Document::create([
                'student_id' => $enrollment->student_id,
                'document_type' => DocumentType::OTHER,
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $file->hashName(),
                'file_path' => $file->store('documents', 'private'),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'upload_date' => now(),
            ]);
        }
    }

    // Notify admin
    $adminEmail = $enrollment->infoRequestedBy?->email;
    if ($adminEmail) {
        Mail::to($adminEmail)
            ->queue(new EnrollmentInfoProvided($enrollment));
    }

    return back()->with('success', 'Your response has been submitted.');
}
```

### Email Templates

**File:** `app/Mail/EnrollmentInfoRequested.php`

```php
<?php

namespace App\Mail;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnrollmentInfoRequested extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Enrollment $enrollment
    ) {}

    public function build()
    {
        return $this->subject('Additional Information Required - Enrollment Application')
            ->markdown('emails.enrollments.info-requested');
    }
}
```

**File:** `resources/views/emails/enrollments/info-requested.blade.php`

```blade
@component('mail::message')
# Additional Information Required

Hello,

We have reviewed your enrollment application for **{{ $enrollment->student->first_name }} {{ $enrollment->student->last_name }}** and need some additional information to continue processing.

**Message from Registrar:**
{{ $enrollment->info_request_message }}

**Enrollment Details:**
- Enrollment ID: {{ $enrollment->enrollment_id }}
- Student: {{ $enrollment->student->first_name }} {{ $enrollment->student->last_name }}
- Grade Level: {{ $enrollment->grade_level }}
- School Year: {{ $enrollment->school_year }}

Please log in to your account to provide the requested information.

@component('mail::button', ['url' => route('guardian.enrollments.show', $enrollment)])
View Enrollment
@endcomponent

If you have any questions, please contact our registrar's office.

Thank you,<br>
{{ config('app.name') }}
@endcomponent
```

## Acceptance Criteria

- [ ] Admin can request info from enrollment review page
- [ ] Guardian receives email notification
- [ ] Guardian sees request prominently on enrollment page
- [ ] Guardian can provide text response
- [ ] Guardian can upload additional documents (optional)
- [ ] Admin sees response notification via email
- [ ] Admin can view response on enrollment page
- [ ] Enrollment remains in PENDING status during info exchange
- [ ] Audit trail maintained (who requested, when, who responded, when)
- [ ] UI clearly shows info request status
- [ ] Can handle multiple info request cycles

## Testing Checklist

- [ ] Admin can request info for pending enrollment
- [ ] Cannot request info for approved/rejected enrollments
- [ ] Email sent to guardian immediately
- [ ] Guardian sees info request on enrollment page
- [ ] Guardian can respond with message
- [ ] Guardian can upload documents
- [ ] Response email sent to admin
- [ ] Admin sees response on enrollment page
- [ ] Multiple admins can see the exchange
- [ ] Info request history maintained
- [ ] Validation works for required fields
- [ ] File upload validation works
- [ ] Works for both super-admin and registrar roles

## Priority

**Medium** - Mentioned in workflow but workaround exists (can use rejection with reason)

## Dependencies

None - can be implemented independently

## Notes

- Consider adding comment thread instead of single request/response
- May want to limit number of info request cycles
- Consider adding deadline for guardian response
- Future: In-app notifications in addition to email
- Consider making documents mandatory vs optional based on request

# Enrollment Certificate Download

**Status:** Not Started
**Priority:** High
**Type:** Feature
**Estimated Effort:** 6-8 hours

## Description

Add functionality for guardians to download official enrollment certificates for their enrolled students.

## Referenced In

- GUARDIAN_USER_JOURNEY.md (Step 11, lines 558, 591)
- Mentioned as available download but not implemented

## Requirements

### Backend

- [ ] New route: `GET /guardian/enrollments/{enrollment}/certificate`
- [ ] Controller method in `Guardian\EnrollmentController`
- [ ] Authorization: Guardian must own the student
- [ ] Only available for `status = ENROLLED`
- [ ] Generate PDF certificate

### PDF Content

**Certificate Should Include:**

- **Header**: "CERTIFICATE OF ENROLLMENT"
- **School Logo**: Centered at top
- **School Information**: Name, address, contact
- **Certificate Body**:

    ```
    This is to certify that

    [STUDENT FULL NAME]
    Student ID: [STUDENT_ID]

    is officially enrolled in

    Grade Level: [GRADE]
    School Year: [SCHOOL_YEAR]

    Enrollment Date: [APPROVED_AT]
    Enrollment ID: [ENROLLMENT_ID]
    ```

- **Signature Section**:
    - Registrar signature line
    - Date issued
    - Official school seal (if available)
- **Footer**:
    - "This is a computer-generated certificate"
    - Certificate number for verification

### Design Specifications

- **Paper Size**: A4 or Letter (configurable)
- **Orientation**: Portrait
- **Margins**: 1 inch all sides
- **Font**: Professional serif font (Times New Roman, Garamond)
- **Border**: Decorative border around certificate
- **Colors**: School colors if defined in settings

## Implementation Plan

### 1. Create Certificate View Template

**File:** `resources/views/pdf/enrollment-certificate.blade.php`

```blade
<!DOCTYPE html>
<html>
<head>
    <title>Enrollment Certificate</title>
    <style>
        @page { margin: 1in; }
        body {
            font-family: 'Times New Roman', serif;
            text-align: center;
        }
        .certificate-border {
            border: 10px double #333;
            padding: 40px;
            min-height: 8in;
        }
        .school-logo {
            height: 100px;
            margin-bottom: 20px;
        }
        .certificate-title {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 40px;
        }
        .student-name {
            font-size: 28px;
            font-weight: bold;
            text-decoration: underline;
            margin: 30px 0;
        }
        .details {
            font-size: 18px;
            line-height: 2;
        }
        .signature-section {
            margin-top: 80px;
            display: flex;
            justify-content: space-around;
        }
        .signature-line {
            border-top: 2px solid #000;
            width: 200px;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="certificate-border">
        @if(isset($settings['school_logo']))
            <img src="{{ $settings['school_logo'] }}" class="school-logo" alt="School Logo">
        @endif

        <h1 class="certificate-title">CERTIFICATE OF ENROLLMENT</h1>

        <p class="details">This is to certify that</p>

        <p class="student-name">
            {{ $enrollment->student->first_name }}
            {{ $enrollment->student->middle_name }}
            {{ $enrollment->student->last_name }}
        </p>

        <p class="details">Student ID: {{ $enrollment->student->student_id }}</p>

        <p class="details">is officially enrolled in</p>

        <p class="details">
            <strong>Grade Level:</strong> {{ $enrollment->grade_level }}<br>
            <strong>School Year:</strong> {{ $enrollment->school_year }}
        </p>

        <p class="details" style="margin-top: 40px;">
            Enrollment Date: {{ $enrollment->approved_at?->format('F d, Y') }}<br>
            Enrollment ID: {{ $enrollment->enrollment_id }}
        </p>

        <div class="signature-section">
            <div>
                <div class="signature-line">Registrar</div>
            </div>
            <div>
                <div class="signature-line">Date Issued</div>
                <p>{{ now()->format('F d, Y') }}</p>
            </div>
        </div>

        <p style="margin-top: 60px; font-size: 10px; font-style: italic;">
            This is a computer-generated certificate.<br>
            Certificate No: {{ $enrollment->enrollment_id }}
        </p>
    </div>
</body>
</html>
```

### 2. Add Controller Method

**File:** `app/Http/Controllers/Guardian/EnrollmentController.php`

```php
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Download enrollment certificate PDF
 */
public function downloadCertificate(Enrollment $enrollment)
{
    // Get Guardian model for authenticated user
    $guardian = Guardian::where('user_id', Auth::id())->firstOrFail();

    // Check if this enrollment belongs to guardian's student
    $hasAccess = GuardianStudent::where('guardian_id', $guardian->id)
        ->where('student_id', $enrollment->student_id)
        ->exists();

    if (!$hasAccess) {
        abort(404);
    }

    // Only allow certificate download for enrolled students
    if ($enrollment->status !== EnrollmentStatus::ENROLLED) {
        return back()->withErrors([
            'error' => 'Certificate is only available for enrolled students.'
        ]);
    }

    $enrollment->load('student');
    $settings = Setting::pluck('value', 'key');

    $pdf = Pdf::loadView('pdf.enrollment-certificate', [
        'enrollment' => $enrollment,
        'settings' => $settings,
    ])
    ->setPaper('a4', 'portrait');

    return $pdf->download("enrollment-certificate-{$enrollment->enrollment_id}.pdf");
}
```

### 3. Add Route

**File:** `routes/web.php`

```php
Route::get('/guardian/enrollments/{enrollment}/certificate',
    [Guardian\EnrollmentController::class, 'downloadCertificate'])
    ->name('guardian.enrollments.certificate')
    ->middleware(['auth', 'verified', 'role:guardian']);
```

### 4. Update Frontend - Enrollment Show Page

**File:** `resources/js/pages/guardian/enrollments/show.tsx`

Add download button in the action buttons section:

```typescript
{enrollment.status === 'enrolled' && (
    <Button variant="outline" asChild>
        <a href={`/guardian/enrollments/${enrollment.id}/certificate`} download>
            <Download className="mr-2 h-4 w-4" />
            Download Certificate
        </a>
    </Button>
)}
```

## Acceptance Criteria

- [ ] Guardian can download certificate from enrollment show page
- [ ] PDF is professionally formatted with school branding
- [ ] Download button appears only for enrolled students
- [ ] Filename: `enrollment-certificate-{enrollment_id}.pdf`
- [ ] Guardian authorization check prevents unauthorized access
- [ ] Certificate includes all required information
- [ ] Certificate has professional design with border
- [ ] School logo displays if available
- [ ] Date formats are consistent and readable

## Testing Checklist

- [ ] Guardian with enrolled student can download certificate
- [ ] Certificate not available for pending enrollments
- [ ] Certificate not available for rejected enrollments
- [ ] Guardian cannot download other student's certificate (404)
- [ ] PDF renders correctly with all fields
- [ ] School logo appears if configured
- [ ] All student information correct
- [ ] Enrollment details accurate
- [ ] Professional appearance suitable for official use
- [ ] Works on different browsers
- [ ] File downloads with correct filename

## Security Considerations

- [ ] Guardian authorization enforced
- [ ] Only accessible for ENROLLED status
- [ ] Cannot access other guardian's student certificates
- [ ] Returns 404 instead of 403 (security through obscurity)

## Priority

**High** - Referenced in user journey documentation as available feature

## Dependencies

- Depends on ticket #001 (Backend PDF Generation) being completed first
- Requires DomPDF or similar library installed

## Notes

- Consider adding QR code for verification (optional enhancement)
- May want to add certificate serial number for tracking
- Could cache generated PDFs for performance
- Future: Add admin ability to regenerate/void certificates

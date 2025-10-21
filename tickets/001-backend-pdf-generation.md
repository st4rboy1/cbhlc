# Backend PDF Generation for Invoices

**Status:** Not Started
**Priority:** Medium
**Type:** Enhancement
**Estimated Effort:** 4-6 hours

## Description

Currently, invoice PDF downloads use the browser's print-to-PDF functionality. This should be replaced with backend PDF generation for more professional and consistent output.

## Current Implementation

- **Location:** `resources/js/pages/shared/invoice.tsx` (lines 126-130)
- **Method:** `handleDownloadPDF()` calls `handlePrint()` which opens browser print dialog
- **Issue:** Inconsistent rendering across browsers, no control over output

## Proposed Solution

Implement backend PDF generation using one of:

- **DomPDF**: Pure PHP solution (easiest integration)
- **Snappy**: wkhtmltopdf wrapper (better rendering quality)
- **Laravel-PDF**: Wrapper package for Laravel

### Recommended: Barryvdh/Laravel-DomPDF

```bash
composer require barryvdh/laravel-dompdf
```

## Implementation Plan

### 1. Install Package

```bash
./vendor/bin/sail composer require barryvdh/laravel-dompdf
```

### 2. Create PDF View Template

- **File:** `resources/views/pdf/invoice.blade.php`
- Copy HTML structure from React component
- Use inline CSS for proper rendering

### 3. Add Controller Method

**File:** `app/Http/Controllers/InvoiceController.php`

```php
public function downloadPdf(Request $request, Enrollment $invoice)
{
    // Authorization check (same as show method)

    $invoice->load(['student', 'guardian']);
    $settings = Setting::pluck('value', 'key');

    $pdf = PDF::loadView('pdf.invoice', [
        'enrollment' => $invoice,
        'invoiceNumber' => $invoice->enrollment_id,
        'currentDate' => now()->format('F d, Y'),
        'settings' => $settings,
    ]);

    return $pdf->download("invoice-{$invoice->enrollment_id}.pdf");
}
```

### 4. Add Route

**File:** `routes/web.php`

```php
Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])
    ->name('invoices.pdf');
```

### 5. Update Frontend

**File:** `resources/js/pages/shared/invoice.tsx`

```typescript
const handleDownloadPDF = () => {
    window.location.href = `/invoices/${enrollment.id}/pdf`;
};
```

## Acceptance Criteria

- [ ] New route: `GET /invoices/{enrollment}/pdf`
- [ ] Controller method generates PDF server-side
- [ ] PDF includes all invoice details with proper formatting
- [ ] PDF downloads automatically (Content-Disposition: attachment)
- [ ] Maintains current print functionality as alternative
- [ ] Guardian authorization enforced
- [ ] Admin users can download any invoice PDF
- [ ] Proper filename: `invoice-{enrollment_id}.pdf`

## Testing Checklist

- [ ] Guardian can download their own student's invoice PDF
- [ ] Guardian cannot download other student's invoice PDF (404)
- [ ] Admin can download any invoice PDF
- [ ] PDF renders correctly with all fields
- [ ] PDF styling matches invoice design
- [ ] School logo displays correctly
- [ ] Payment status badge shows correctly
- [ ] All fee breakdowns display properly

## References

- GUARDIAN_USER_JOURNEY.md (Step 8, line 379)
- Comment in code suggests this improvement (invoice.tsx:126-130)

## Dependencies

None - can be implemented independently

## Notes

- Keep browser print functionality as backup/alternative
- Consider adding configuration for PDF paper size (A4/Letter)
- May want to add watermark for unpaid invoices
- Consider caching PDFs for performance (optional)

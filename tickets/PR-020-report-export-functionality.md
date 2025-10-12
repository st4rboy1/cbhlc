# PR #020: Report Export Functionality (PDF, Excel, CSV)

## Related Ticket

[TICKET-020: Report Export Functionality](./TICKET-020-report-export-functionality.md)

## Epic

[EPIC-003: Comprehensive Reporting System](./EPIC-003-reporting-system.md)

## Description

This PR implements comprehensive export functionality for all report types with support for PDF (using DomPDF), Excel (using Laravel Excel), and CSV formats. Includes proper formatting, school branding, and optimized performance for large datasets with background job processing.

## Changes Made

### Backend Packages

- ✅ Installed `barryvdh/laravel-dompdf`
- ✅ Installed `maatwebsite/excel`
- ✅ Published Excel config

### Backend Export Classes

- ✅ Created `App/Exports/EnrollmentStatisticsExport.php`
- ✅ Created `App/Exports/StudentDemographicsExport.php`
- ✅ Created `App/Exports/ClassRosterExport.php`
- ✅ Created `App/Exports/FinancialReportExport.php`

### Backend Jobs

- ✅ Created `App/Jobs/GenerateReportExport.php` for large datasets
- ✅ Created `App/Notifications/ExportReadyNotification.php`

### Database

- ✅ Created `create_exports_table.php` migration

### Frontend Components

- ✅ Created `resources/js/components/reports/export-button.tsx`
- ✅ Format dropdown (PDF, Excel, CSV)
- ✅ Loading states
- ✅ Download link handling

### PDF Templates

- ✅ Created blade templates for each report type
- ✅ School branding
- ✅ Headers and footers
- ✅ Professional formatting

## Type of Change

- [x] New feature (full-stack)
- [ ] Bug fix
- [ ] Breaking change
- [ ] Documentation update

## Testing Checklist

### Backend Tests

- [ ] PDF export generates correctly
- [ ] Excel export generates correctly
- [ ] CSV export generates correctly
- [ ] Export respects filters
- [ ] Large exports (500+ records) are queued
- [ ] Export job processes correctly
- [ ] Notification sent when ready
- [ ] File cleanup works (24-hour expiry)
- [ ] All report types exportable

### Frontend Tests

- [ ] Export button renders
- [ ] Format dropdown works
- [ ] Loading state displays
- [ ] Download link works
- [ ] Queued export notification shows
- [ ] Error handling works

### Format Tests

- [ ] PDF formatting correct
- [ ] PDF includes school branding
- [ ] Excel formatting correct
- [ ] Excel column widths appropriate
- [ ] Excel multiple sheets work
- [ ] CSV UTF-8 encoding correct
- [ ] CSV opens correctly in Excel
- [ ] All formats downloadable

## Verification Steps

```bash
# Install packages
composer require barryvdh/laravel-dompdf
composer require maatwebsite/excel

# Run migration
./vendor/bin/sail artisan migrate

# Run tests
./vendor/bin/sail pest tests/Feature/Reports/ExportTest.php

# Manual testing:
# 1. Login as Registrar
# 2. Open any report page
# 3. Click Export button
# 4. Select PDF format
# 5. Verify download
# 6. Check PDF formatting
# 7. Repeat for Excel and CSV
# 8. Test with large dataset (500+ records)
# 9. Verify queued export notification
# 10. Check export file cleanup after 24 hours
```

## Routes

```php
Route::middleware(['auth', 'role:registrar|administrator'])
    ->prefix('registrar/reports')
    ->name('registrar.reports.')
    ->group(function () {
        Route::post('/export', [RegistrarReportController::class, 'export'])->name('export');
    });
```

## Key Implementation Details

### Export Controller Method

```php
public function export(Request $request)
{
    $request->validate([
        'report_type' => 'required|in:enrollment_statistics,student_demographics,class_roster,financial',
        'format' => 'required|in:pdf,excel,csv',
        'filters' => 'array',
    ]);

    $reportType = $request->report_type;
    $format = $request->format;
    $filters = $request->filters ?? [];

    $data = match($reportType) {
        'enrollment_statistics' => $this->getEnrollmentStatisticsData($filters),
        'student_demographics' => $this->getStudentDemographicsData($filters),
        'class_roster' => $this->getClassRosterData($filters),
        'financial' => $this->getFinancialData($filters),
    };

    // Queue large exports
    if ($this->shouldQueue($data)) {
        $export = Export::create([
            'user_id' => auth()->id(),
            'report_type' => $reportType,
            'format' => $format,
            'filters' => $filters,
            'status' => 'queued',
            'expires_at' => now()->addHours(24),
        ]);

        GenerateReportExport::dispatch($export);

        return back()->with('success', 'Your export is being generated. You will be notified when it\'s ready.');
    }

    return $this->generateExport($reportType, $format, $data);
}

private function shouldQueue($data): bool
{
    return is_countable($data) && count($data) > 500;
}
```

### Excel Export Example

```php
class EnrollmentStatisticsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function __construct(protected $data) {}

    public function collection()
    {
        return collect($this->data)->map(function ($item) {
            return [
                'school_year' => $item['school_year'],
                'grade_level' => $item['grade_level'],
                'total' => $item['total'],
                'approved' => $item['approved'],
                'pending' => $item['pending'],
                'rejected' => $item['rejected'],
                'approval_rate' => $item['approval_rate'] . '%',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'School Year',
            'Grade Level',
            'Total Applications',
            'Approved',
            'Pending',
            'Rejected',
            'Approval Rate',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 20,
            'C' => 18,
            'D' => 12,
            'E' => 12,
            'F' => 12,
            'G' => 15,
        ];
    }
}
```

### Export Job

```php
class GenerateReportExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Export $export) {}

    public function handle()
    {
        $this->export->update(['status' => 'processing']);

        try {
            $filePath = $this->generateFile();

            $this->export->update([
                'status' => 'completed',
                'file_path' => $filePath,
            ]);

            $this->export->user->notify(new ExportReadyNotification($this->export));
        } catch (\Exception $e) {
            $this->export->update(['status' => 'failed']);
            report($e);
            throw $e;
        }
    }
}
```

### Exports Table Migration

```php
Schema::create('exports', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('report_type');
    $table->string('format');
    $table->json('filters')->nullable();
    $table->enum('status', ['queued', 'processing', 'completed', 'failed'])->default('queued');
    $table->string('file_path')->nullable();
    $table->timestamp('expires_at');
    $table->timestamps();

    $table->index(['user_id', 'status']);
    $table->index('expires_at');
});
```

### File Cleanup Command

```php
php artisan schedule:run

// In App\Console\Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        Export::where('expires_at', '<', now())->each(function ($export) {
            if ($export->file_path && Storage::exists($export->file_path)) {
                Storage::delete($export->file_path);
            }
            $export->delete();
        });
    })->daily();
}
```

## Dependencies

- `barryvdh/laravel-dompdf` package
- `maatwebsite/excel` package
- Queue system configured
- File storage configured
- Notification system

## Breaking Changes

None

## Deployment Notes

- Install packages: `composer require barryvdh/laravel-dompdf maatwebsite/excel`
- Run migration: `php artisan migrate`
- Configure queue worker: `php artisan queue:work`
- Configure scheduler: Add cron job for cleanup
- Build frontend: `npm run build`

## Post-Merge Checklist

- [ ] Packages installed
- [ ] Migration run
- [ ] Export functionality works
- [ ] PDF formatting correct
- [ ] Excel formatting correct
- [ ] CSV encoding correct
- [ ] Queue processing works
- [ ] Notifications sent
- [ ] File cleanup configured
- [ ] Next ticket (TICKET-021) can begin

## Reviewer Notes

Please verify:

1. Export file formatting is professional
2. School branding appears correctly in PDFs
3. Large dataset queuing works reliably
4. File cleanup doesn't delete active exports
5. Excel column widths are appropriate
6. CSV encoding handles special characters
7. Memory usage acceptable for large exports
8. Queue jobs handle failures gracefully

## Performance Considerations

- Exports >500 records queued to background
- Chunk large datasets in job processing
- Files expire after 24 hours
- Export generation optimized with eager loading
- PDF rendering may be slow for very large datasets

## Security Considerations

- Only authorized users can export
- Export files stored securely
- Files auto-delete after 24 hours
- Export access logged
- Filters validated before export
- File paths not exposed to users

---

**Ticket:** #020
**Estimated Effort:** 2 days
**Actual Effort:** _[To be filled after completion]_

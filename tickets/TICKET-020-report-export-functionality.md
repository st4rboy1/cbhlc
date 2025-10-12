# TICKET-020: Report Export Functionality (PDF, Excel, CSV)

## Epic

[EPIC-003: Comprehensive Reporting System](./EPIC-003-reporting-system.md)

## Priority

Medium (Could Have)

## User Story

As a registrar or administrator, I want to export reports in multiple formats (PDF, Excel, CSV) so that I can share data with stakeholders, archive records, and perform further analysis in external tools.

## Related SRS Requirements

- **FR-7.5:** System shall export reports in multiple formats (PDF, Excel)

## Description

Implement comprehensive export functionality for all report types with support for PDF (using DomPDF), Excel (using Laravel Excel), and CSV formats. Include proper formatting, school branding, and optimized performance for large datasets.

## Acceptance Criteria

- ✅ All reports can be exported to PDF format
- ✅ All reports can be exported to Excel format
- ✅ All reports can be exported to CSV format
- ✅ PDF exports include school branding and headers
- ✅ Excel exports include proper formatting and column widths
- ✅ CSV exports are properly encoded (UTF-8)
- ✅ Export respects current filter selections
- ✅ Large exports (500+ records) are queued for background processing
- ✅ Users receive download link when export is ready
- ✅ Export files are automatically cleaned up after 24 hours

## Technical Requirements

### Backend

1. Install required packages:
    - `barryvdh/laravel-dompdf` for PDF generation
    - `maatwebsite/excel` for Excel and CSV exports
2. Create export classes for each report type
3. Implement queue jobs for large exports
4. Add export routes to report controllers
5. Create blade templates for PDF layouts
6. Implement file cleanup scheduler

### Frontend

1. Add export button component with format dropdown
2. Show loading state during export generation
3. Display download link when export is ready
4. Handle queued export notifications
5. Add progress indicator for large exports

### Database

- Create `exports` table to track export jobs:
    - id, user_id, report_type, format, filters, status, file_path, expires_at

## Implementation Details

### Package Installation

```bash
composer require barryvdh/laravel-dompdf
composer require maatwebsite/excel
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
```

### Export Classes

**Enrollment Statistics Excel Export:**

```php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

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

**Class Roster Excel Export:**

```php
class ClassRosterExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function __construct(
        protected Collection $students,
        protected string $gradeLevel,
        protected string $schoolYear
    ) {}

    public function collection()
    {
        return $this->students->map(function ($student, $index) {
            return [
                'number' => $index + 1,
                'student_id' => $student['student_id'],
                'full_name' => $student['full_name'],
                'birth_date' => $student['birth_date'],
                'age' => $student['age'],
                'gender' => $student['gender'],
                'guardian_name' => $student['primary_guardian']['name'],
                'guardian_phone' => $student['primary_guardian']['phone'],
                'guardian_email' => $student['primary_guardian']['email'],
                'address' => $student['address'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'Student ID',
            'Full Name',
            'Birth Date',
            'Age',
            'Gender',
            'Guardian Name',
            'Guardian Phone',
            'Guardian Email',
            'Address',
        ];
    }

    public function title(): string
    {
        return "{$this->gradeLevel} - {$this->schoolYear}";
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E8F0'],
            ],
        ]);

        return [];
    }
}
```

### PDF Blade Template

```blade
<!-- resources/views/reports/class-roster-pdf.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Class Roster - {{ $gradeLevel->level_name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Christian Bible Heritage Learning Center</h1>
        <p>Class Roster - {{ $gradeLevel->level_name }}</p>
        <p>School Year: {{ $schoolYear }}</p>
        <p>Total Students: {{ count($students) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Age/Gender</th>
                <th>Guardian</th>
                <th>Contact</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $student['student_id'] }}</td>
                <td>{{ $student['full_name'] }}</td>
                <td>{{ $student['age'] }} / {{ $student['gender'] }}</td>
                <td>{{ $student['primary_guardian']['name'] }}<br>
                    <small>{{ $student['primary_guardian']['relationship'] }}</small>
                </td>
                <td>{{ $student['primary_guardian']['phone'] }}<br>
                    <small>{{ $student['primary_guardian']['email'] }}</small>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ now()->format('F d, Y h:i A') }}</p>
    </div>
</body>
</html>
```

### Controller Export Methods

```php
public function export(Request $request)
{
    $request->validate([
        'report_type' => 'required|in:enrollment_statistics,student_demographics,class_roster',
        'format' => 'required|in:pdf,excel,csv',
        'filters' => 'array',
    ]);

    $reportType = $request->report_type;
    $format = $request->format;
    $filters = $request->filters ?? [];

    // Generate report data based on type
    $data = match($reportType) {
        'enrollment_statistics' => $this->getEnrollmentStatisticsData($filters),
        'student_demographics' => $this->getStudentDemographicsData($filters),
        'class_roster' => $this->getClassRosterData($filters),
    };

    // If large dataset, queue the export
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

    // Generate immediately for small datasets
    return $this->generateExport($reportType, $format, $data);
}

private function generateExport(string $reportType, string $format, $data)
{
    $filename = "{$reportType}_" . now()->format('Y-m-d_His');

    return match($format) {
        'pdf' => $this->exportPdf($reportType, $data, $filename),
        'excel' => $this->exportExcel($reportType, $data, $filename),
        'csv' => $this->exportCsv($reportType, $data, $filename),
    };
}

private function exportPdf(string $reportType, $data, string $filename)
{
    $pdf = PDF::loadView("reports.{$reportType}-pdf", $data);
    return $pdf->download("{$filename}.pdf");
}

private function exportExcel(string $reportType, $data, string $filename)
{
    $exportClass = match($reportType) {
        'enrollment_statistics' => new EnrollmentStatisticsExport($data),
        'student_demographics' => new StudentDemographicsExport($data),
        'class_roster' => new ClassRosterExport($data),
    };

    return Excel::download($exportClass, "{$filename}.xlsx");
}

private function exportCsv(string $reportType, $data, string $filename)
{
    $exportClass = match($reportType) {
        'enrollment_statistics' => new EnrollmentStatisticsExport($data),
        'student_demographics' => new StudentDemographicsExport($data),
        'class_roster' => new ClassRosterExport($data),
    };

    return Excel::download($exportClass, "{$filename}.csv", \Maatwebsite\Excel\Excel::CSV);
}

private function shouldQueue($data): bool
{
    // Queue if more than 500 records
    return is_countable($data) && count($data) > 500;
}
```

### Export Job

```php
namespace App\Jobs;

use App\Models\Export;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateReportExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Export $export) {}

    public function handle()
    {
        $this->export->update(['status' => 'processing']);

        try {
            // Generate export file
            $filePath = $this->generateFile();

            $this->export->update([
                'status' => 'completed',
                'file_path' => $filePath,
            ]);

            // Notify user
            $this->export->user->notify(new ExportReadyNotification($this->export));
        } catch (\Exception $e) {
            $this->export->update(['status' => 'failed']);
            report($e);
            throw $e;
        }
    }

    private function generateFile(): string
    {
        // Implementation similar to controller method
        // But saves file to storage instead of downloading
    }
}
```

### Frontend Export Button

```tsx
export function ExportButton({ reportType, currentFilters }) {
    const [isExporting, setIsExporting] = useState(false);
    const [selectedFormat, setSelectedFormat] = useState<'pdf' | 'excel' | 'csv'>('pdf');

    const handleExport = () => {
        setIsExporting(true);

        router.post(
            route('registrar.reports.export'),
            {
                report_type: reportType,
                format: selectedFormat,
                filters: currentFilters,
            },
            {
                onFinish: () => setIsExporting(false),
                onSuccess: (page) => {
                    if (page.props.flash.success) {
                        toast.success(page.props.flash.success);
                    }
                },
            },
        );
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="outline" disabled={isExporting}>
                    {isExporting ? (
                        <>
                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            Exporting...
                        </>
                    ) : (
                        <>
                            <DownloadIcon className="mr-2 h-4 w-4" />
                            Export
                        </>
                    )}
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuItem
                    onClick={() => {
                        setSelectedFormat('pdf');
                        handleExport();
                    }}
                >
                    <FileTextIcon className="mr-2 h-4 w-4" />
                    Export as PDF
                </DropdownMenuItem>
                <DropdownMenuItem
                    onClick={() => {
                        setSelectedFormat('excel');
                        handleExport();
                    }}
                >
                    <FileSpreadsheetIcon className="mr-2 h-4 w-4" />
                    Export as Excel
                </DropdownMenuItem>
                <DropdownMenuItem
                    onClick={() => {
                        setSelectedFormat('csv');
                        handleExport();
                    }}
                >
                    <FileIcon className="mr-2 h-4 w-4" />
                    Export as CSV
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
```

## Testing Requirements

### Feature Tests

```php
test('registrar can export report as PDF', function () {
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    actingAs($registrar)
        ->post(route('registrar.reports.export'), [
            'report_type' => 'enrollment_statistics',
            'format' => 'pdf',
            'filters' => [],
        ])
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});

test('large exports are queued', function () {
    Queue::fake();

    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    // Create 600 enrollments (more than threshold)
    Enrollment::factory()->count(600)->create();

    actingAs($registrar)
        ->post(route('registrar.reports.export'), [
            'report_type' => 'enrollment_statistics',
            'format' => 'excel',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    Queue::assertPushed(GenerateReportExport::class);
});
```

## Routes

```php
Route::middleware(['auth', 'role:registrar|administrator'])->prefix('registrar/reports')->name('registrar.reports.')->group(function () {
    Route::post('/export', [RegistrarReportController::class, 'export'])->name('export');
});
```

## Dependencies

- `barryvdh/laravel-dompdf` package
- `maatwebsite/excel` package
- Queue system configured
- File storage configured

## Estimated Effort

**2 days**

## Implementation Checklist

- [ ] Install PDF and Excel packages
- [ ] Create export classes for all report types
- [ ] Create PDF blade templates with branding
- [ ] Implement controller export methods
- [ ] Add queuing logic for large exports
- [ ] Create export job class
- [ ] Implement export notification
- [ ] Add exports table migration
- [ ] Create frontend ExportButton component
- [ ] Add file cleanup scheduler
- [ ] Write feature tests for all formats
- [ ] Test with large datasets (queue behavior)
- [ ] Test PDF formatting and branding
- [ ] Test Excel column widths and styling
- [ ] Test CSV encoding (special characters)
- [ ] Verify file cleanup works correctly

## Notes

- PDF exports use DejaVu Sans font for better Unicode support
- Excel exports support up to 1 million rows
- CSV uses UTF-8 BOM for Excel compatibility
- Queued exports expire after 24 hours
- Consider adding email notification with download link for queued exports
- May want to add custom export templates in future

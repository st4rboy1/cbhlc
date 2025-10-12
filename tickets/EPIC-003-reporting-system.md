# Ticket #003: Comprehensive Reporting System

## Priority: Medium (Could Have)

## Related SRS Requirements

- **FR-7.1:** System shall generate enrollment statistics reports
- **FR-7.2:** System shall produce student demographic reports
- **FR-7.3:** System shall create class roster reports
- **FR-7.4:** System shall support report filtering by date, grade, and status
- **FR-7.5:** System shall export reports in multiple formats (PDF, Excel)
- **Section 3.7:** Reporting Module

## Current Status

⚠️ **PARTIALLY IMPLEMENTED**

Current implementation:

- `StudentReportController` exists but limited functionality
- No comprehensive reporting system
- No export functionality (PDF, Excel)
- No report filtering options

## Required Implementation

### 1. Backend Layer

**Controllers:**
Create comprehensive reporting controllers:

- `Registrar/ReportController.php`
- `SuperAdmin/ReportController.php`
- `Admin/ReportController.php`

**Report Types:**

#### a) Enrollment Statistics Report

- Total enrollments per school year
- Enrollments by status (pending, approved, rejected)
- Enrollments by grade level
- Enrollment trends over time
- Approval/rejection rates
- Average processing time

#### b) Student Demographics Report

- Total students by grade level
- Gender distribution
- Age distribution
- Address/location distribution
- New vs. returning students
- Students by guardian count

#### c) Class Roster Report

- Students per grade level/section
- Contact information
- Guardian details
- Enrollment status
- Payment status

#### d) Financial Reports

- Total revenue per school year
- Payment status summary
- Outstanding balances
- Payment trends
- Revenue by grade level

#### e) Document Verification Report

- Documents pending verification
- Verification completion rates
- Document types submitted
- Average verification time

### 2. Routes

```php
// Registrar routes
Route::prefix('registrar/reports')->name('registrar.reports.')->group(function () {
    Route::get('/', [RegistrarReportController::class, 'index'])->name('index');
    Route::get('/enrollment-statistics', 'enrollmentStatistics')->name('enrollment-statistics');
    Route::get('/student-demographics', 'studentDemographics')->name('student-demographics');
    Route::get('/class-roster', 'classRoster')->name('class-roster');
    Route::get('/financial', 'financial')->name('financial');
    Route::post('/export', 'export')->name('export');
});

// Super Admin routes
Route::prefix('super-admin/reports')->name('super-admin.reports.')->group(function () {
    Route::get('/', [SuperAdminReportController::class, 'index'])->name('index');
    // Same report routes as registrar
    Route::get('/system-usage', 'systemUsage')->name('system-usage');
    Route::get('/audit-log', 'auditLog')->name('audit-log');
});
```

### 3. Export Functionality

**Install Required Packages:**

```bash
composer require barryvdh/laravel-dompdf
composer require maatwebsite/excel
```

**Export Classes:**

- `App\Exports\EnrollmentStatisticsExport`
- `App\Exports\StudentDemographicsExport`
- `App\Exports\ClassRosterExport`
- `App\Exports\FinancialReportExport`

**PDF Generation:**

- Use DomPDF for PDF generation
- Create blade templates for PDF layouts
- Support custom headers/footers with school branding

**Excel Export:**

- Use Laravel Excel package
- Support multiple sheets per workbook
- Include charts and formatting
- Auto-column width and styling

### 4. Frontend Layer

**Pages:**

- `/resources/js/pages/registrar/reports/index.tsx` - Reports dashboard
- `/resources/js/pages/registrar/reports/enrollment-statistics.tsx`
- `/resources/js/pages/registrar/reports/student-demographics.tsx`
- `/resources/js/pages/registrar/reports/class-roster.tsx`
- `/resources/js/pages/registrar/reports/financial.tsx`

**Components:**

- `ReportCard` - Report summary card
- `ReportFilter` - Advanced filtering component
- `ReportChart` - Chart visualization (using Recharts or Chart.js)
- `ExportButton` - Export dropdown (PDF, Excel, CSV)
- `DateRangePicker` - Date range selection
- `ReportTable` - Sortable, filterable data table

**Features:**

- Interactive charts and graphs
- Real-time filtering
- Drill-down capabilities
- Print-friendly views
- Export to PDF, Excel, CSV
- Save filter presets
- Schedule automated reports (future)

### 5. Report Filtering

**Filter Options:**

- Date range (from/to)
- School year
- Grade level (single or multiple)
- Enrollment status
- Payment status
- Gender
- Age range
- Document verification status

**Implementation:**

- Use Laravel Query Builder for dynamic filtering
- Frontend state management for filter persistence
- URL query parameters for shareable reports
- Saved filter presets per user

### 6. Data Visualization

**Charts and Graphs:**

- Bar charts for enrollment by grade level
- Line charts for enrollment trends
- Pie charts for status distribution
- Donut charts for payment status
- Stacked bar charts for comparative analysis

**Libraries:**

- Recharts (recommended for React)
- Or Chart.js with react-chartjs-2

### 7. Caching and Performance

**Optimization:**

- Cache report data with tags
- Invalidate cache on relevant model updates
- Queue heavy report generation
- Implement pagination for large datasets
- Use database indexes for report queries

```php
// Example caching
$enrollmentStats = Cache::tags(['reports', 'enrollments'])
    ->remember('enrollment-stats-' . $schoolYear, 3600, function () use ($schoolYear) {
        return Enrollment::generateStatistics($schoolYear);
    });
```

## Acceptance Criteria

✅ Registrar can generate enrollment statistics reports
✅ Registrar can generate student demographics reports
✅ Registrar can generate class roster reports
✅ Reports support filtering by date, grade, and status
✅ Reports can be exported to PDF format
✅ Reports can be exported to Excel format
✅ Reports can be exported to CSV format
✅ Charts and graphs visualize data effectively
✅ Reports load within 3 seconds for standard datasets
✅ Export files are properly formatted and downloadable
✅ Filters persist during session
✅ Reports are responsive and print-friendly

## Testing Requirements

- Unit tests for report generation logic
- Feature tests for report controllers
- Export tests for PDF, Excel, CSV formats
- Performance tests for large datasets
- UI tests for filtering functionality
- Browser tests for chart rendering

## Estimated Effort

**Medium Priority:** 4-6 days

## Dependencies

- Requires barryvdh/laravel-dompdf package
- Requires maatwebsite/excel package
- Requires charting library (Recharts or Chart.js)
- May require queue system for heavy reports

## Implementation Phases

**Phase 1: Core Reports (2 days)**

- Enrollment statistics
- Student demographics
- Basic filtering

**Phase 2: Export Functionality (2 days)**

- PDF export
- Excel export
- CSV export

**Phase 3: Visualizations (1-2 days)**

- Chart components
- Interactive dashboards
- Advanced filtering

**Phase 4: Optimization (1 day)**

- Caching
- Performance tuning
- Testing

## Notes

- Consider scheduled reports sent via email
- Add report scheduling functionality (future)
- Consider adding custom report builder
- Add report access audit logging
- Ensure GDPR/DPA compliance for exported data

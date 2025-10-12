# TICKET-017: Enrollment Statistics Report

## Epic

[EPIC-003: Comprehensive Reporting System](./EPIC-003-reporting-system.md)

## Priority

Medium (Could Have)

## User Story

As a registrar or administrator, I want to generate enrollment statistics reports so that I can track enrollment trends, approval rates, and processing times across school years and grade levels.

## Related SRS Requirements

- **FR-7.1:** System shall generate enrollment statistics reports
- **FR-7.4:** System shall support report filtering by date, grade, and status

## Description

Implement the enrollment statistics report with backend data aggregation, filtering capabilities, and a comprehensive frontend display showing enrollment trends, status distribution, and grade level breakdowns.

## Acceptance Criteria

- ✅ Registrar can view enrollment statistics dashboard
- ✅ Report shows total enrollments per school year
- ✅ Report displays enrollments by status (pending, approved, rejected)
- ✅ Report shows enrollments by grade level
- ✅ Report calculates approval/rejection rates
- ✅ Report shows average processing time
- ✅ Filters work for date range, school year, grade level, status
- ✅ Data loads within 3 seconds for standard datasets
- ✅ Report is responsive and print-friendly

## Technical Requirements

### Backend

1. Create `Registrar/ReportController.php` with `enrollmentStatistics()` method
2. Create `SuperAdmin/ReportController.php` with same method (full access)
3. Implement query builder for enrollment aggregation
4. Add caching with 1-hour TTL and cache tags
5. Implement filter logic for dynamic queries

### Frontend

1. Create `/resources/js/pages/registrar/reports/enrollment-statistics.tsx`
2. Create `ReportFilter` component with date range, school year, grade level, status filters
3. Create `ReportSummaryCard` component for key metrics
4. Display enrollment trends table with sortable columns
5. Implement loading states and error handling

### Database

- No new tables required
- May add indexes on `enrollments.created_at`, `enrollments.status`, `enrollments.school_year`

## Implementation Details

### Controller Method

```php
public function enrollmentStatistics(Request $request)
{
    $filters = $request->only(['school_year', 'grade_level', 'status', 'date_from', 'date_to']);

    $cacheKey = 'enrollment-stats-' . md5(json_encode($filters));

    $statistics = Cache::tags(['reports', 'enrollments'])
        ->remember($cacheKey, 3600, function () use ($filters) {
            $query = Enrollment::query();

            // Apply filters
            if (!empty($filters['school_year'])) {
                $query->where('school_year', $filters['school_year']);
            }

            // ... more filters

            return [
                'total' => $query->count(),
                'by_status' => $query->groupBy('status')->selectRaw('status, count(*) as count')->get(),
                'by_grade_level' => $query->groupBy('grade_level')->selectRaw('grade_level, count(*) as count')->get(),
                'approval_rate' => $this->calculateApprovalRate($query),
                'avg_processing_time' => $this->calculateAvgProcessingTime($query),
                'trend_data' => $this->getEnrollmentTrend($query),
            ];
        });

    return Inertia::render('Registrar/Reports/EnrollmentStatistics', [
        'statistics' => $statistics,
        'filters' => $filters,
        'schoolYears' => EnrollmentPeriod::pluck('school_year'),
        'gradeLevels' => GradeLevel::all(),
    ]);
}
```

### Frontend Page Structure

```tsx
export default function EnrollmentStatistics({ statistics, filters, schoolYears, gradeLevels }) {
    const [localFilters, setLocalFilters] = useState(filters);

    return (
        <AppLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold">Enrollment Statistics</h1>
                </div>

                {/* Filter Component */}
                <ReportFilter filters={localFilters} onFilterChange={setLocalFilters} schoolYears={schoolYears} gradeLevels={gradeLevels} />

                {/* Summary Cards */}
                <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <ReportSummaryCard title="Total Enrollments" value={statistics.total} />
                    <ReportSummaryCard title="Approval Rate" value={`${statistics.approval_rate}%`} />
                    <ReportSummaryCard title="Avg Processing Time" value={`${statistics.avg_processing_time} days`} />
                    <ReportSummaryCard title="Pending Applications" value={statistics.by_status.pending} />
                </div>

                {/* Status Distribution */}
                <Card>
                    <CardHeader>
                        <CardTitle>Enrollments by Status</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>{/* Status breakdown table */}</Table>
                    </CardContent>
                </Card>

                {/* Grade Level Distribution */}
                <Card>
                    <CardHeader>
                        <CardTitle>Enrollments by Grade Level</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>{/* Grade level breakdown table */}</Table>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
```

## Testing Requirements

### Feature Tests

```php
test('registrar can view enrollment statistics', function () {
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    Enrollment::factory()->count(50)->create();

    actingAs($registrar)
        ->get(route('registrar.reports.enrollment-statistics'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Registrar/Reports/EnrollmentStatistics')
            ->has('statistics')
            ->has('statistics.total')
            ->has('statistics.by_status')
        );
});

test('enrollment statistics respect filters', function () {
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    // Create enrollments for different school years
    Enrollment::factory()->create(['school_year' => '2024-2025']);
    Enrollment::factory()->create(['school_year' => '2025-2026']);

    actingAs($registrar)
        ->get(route('registrar.reports.enrollment-statistics', ['school_year' => '2024-2025']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('statistics.total', 1)
        );
});
```

## Routes

```php
Route::middleware(['auth', 'role:registrar|administrator'])->prefix('registrar/reports')->name('registrar.reports.')->group(function () {
    Route::get('/enrollment-statistics', [RegistrarReportController::class, 'enrollmentStatistics'])->name('enrollment-statistics');
});

Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin/reports')->name('super-admin.reports.')->group(function () {
    Route::get('/enrollment-statistics', [SuperAdminReportController::class, 'enrollmentStatistics'])->name('enrollment-statistics');
});
```

## Dependencies

- Requires `EnrollmentPeriod` model (TICKET-007)
- Uses Laravel Cache with tags
- shadcn/ui Table, Card components

## Estimated Effort

**1.5 days**

## Implementation Checklist

- [ ] Create `RegistrarReportController` with `enrollmentStatistics()` method
- [ ] Create `SuperAdminReportController` with same method
- [ ] Implement caching with tags
- [ ] Create filter logic for dynamic queries
- [ ] Add database indexes for performance
- [ ] Create frontend page component
- [ ] Create `ReportFilter` component
- [ ] Create `ReportSummaryCard` component
- [ ] Add routes for both registrar and super admin
- [ ] Write feature tests for controller methods
- [ ] Write UI tests for filtering
- [ ] Test caching behavior
- [ ] Test performance with large datasets

## Notes

- Cache should be invalidated when enrollments are created/updated
- Consider adding real-time updates with websockets in future
- Ensure proper authorization (registrar sees all, admin sees only their managed data)

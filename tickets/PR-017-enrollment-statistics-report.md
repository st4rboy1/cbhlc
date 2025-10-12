# PR #017: Enrollment Statistics Report

## Related Ticket

[TICKET-017: Enrollment Statistics Report](./TICKET-017-enrollment-statistics-report.md)

## Epic

[EPIC-003: Comprehensive Reporting System](./EPIC-003-reporting-system.md)

## Description

This PR implements the enrollment statistics report with comprehensive data aggregation, filtering capabilities, and a user-friendly dashboard showing enrollment trends, status distribution, grade level breakdowns, approval rates, and average processing times.

## Changes Made

### Backend Controllers

- ✅ Created `Registrar/ReportController.php` with `enrollmentStatistics()` method
- ✅ Created `SuperAdmin/ReportController.php` with same method
- ✅ Implemented query builder for enrollment aggregation
- ✅ Added caching with 1-hour TTL and cache tags
- ✅ Implemented dynamic filter logic

### Frontend Pages

- ✅ Created `resources/js/pages/registrar/reports/enrollment-statistics.tsx`
- ✅ Summary cards for key metrics
- ✅ Status distribution table
- ✅ Grade level breakdown table

### Frontend Components

- ✅ Created `resources/js/components/reports/report-filter.tsx`
- ✅ Created `resources/js/components/reports/report-summary-card.tsx`

### Database

- ✅ Added indexes on `enrollments.created_at`, `enrollments.status`, `enrollments.school_year`

## Type of Change

- [x] New feature (full-stack)
- [ ] Bug fix
- [ ] Breaking change
- [ ] Documentation update

## Testing Checklist

### Backend Tests

- [ ] Registrar can view enrollment statistics
- [ ] Super Admin can view enrollment statistics
- [ ] Guardian/Student cannot access report
- [ ] Statistics calculate correctly
- [ ] Filters work (school year, grade level, status, date range)
- [ ] Caching works correctly
- [ ] Cache invalidates on enrollment changes
- [ ] Performance acceptable with 1000+ enrollments

### Frontend Tests

- [ ] Page renders with statistics
- [ ] Summary cards display correct values
- [ ] Tables show data correctly
- [ ] Filters apply correctly
- [ ] Loading states work
- [ ] Error handling works
- [ ] Responsive design
- [ ] Print-friendly

### Integration Tests

- [ ] Filter combinations work correctly
- [ ] Approval rate calculates correctly
- [ ] Average processing time accurate
- [ ] School year filter works
- [ ] Grade level filter works

## Verification Steps

```bash
# Run backend tests
./vendor/bin/sail pest tests/Feature/Registrar/ReportControllerTest.php

# Manual testing:
# 1. Login as Registrar
# 2. Navigate to /registrar/reports/enrollment-statistics
# 3. Verify summary cards display
# 4. Test school year filter
# 5. Test grade level filter
# 6. Test status filter
# 7. Test date range filter
# 8. Verify tables update
# 9. Check calculations are accurate
# 10. Test on mobile device
```

## Routes

```php
// Registrar routes
Route::middleware(['auth', 'role:registrar|administrator'])
    ->prefix('registrar/reports')
    ->name('registrar.reports.')
    ->group(function () {
        Route::get('/enrollment-statistics', [RegistrarReportController::class, 'enrollmentStatistics'])
            ->name('enrollment-statistics');
    });

// Super Admin routes
Route::middleware(['auth', 'role:super_admin'])
    ->prefix('super-admin/reports')
    ->name('super-admin.reports.')
    ->group(function () {
        Route::get('/enrollment-statistics', [SuperAdminReportController::class, 'enrollmentStatistics'])
            ->name('enrollment-statistics');
    });
```

## Controller Implementation

### Registrar/ReportController.php

```php
namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\EnrollmentPeriod;
use App\Models\GradeLevel;
use App\Enums\EnrollmentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportController extends Controller
{
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

                if (!empty($filters['grade_level'])) {
                    $query->where('grade_level', $filters['grade_level']);
                }

                if (!empty($filters['status'])) {
                    $query->where('status', $filters['status']);
                }

                if (!empty($filters['date_from'])) {
                    $query->whereDate('created_at', '>=', $filters['date_from']);
                }

                if (!empty($filters['date_to'])) {
                    $query->whereDate('created_at', '<=', $filters['date_to']);
                }

                return [
                    'total' => $query->count(),
                    'by_status' => $this->getEnrollmentsByStatus($query),
                    'by_grade_level' => $this->getEnrollmentsByGradeLevel($query),
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

    private function getEnrollmentsByStatus($query)
    {
        return $query->clone()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status->value => $item->count]);
    }

    private function getEnrollmentsByGradeLevel($query)
    {
        return $query->clone()
            ->selectRaw('grade_level, count(*) as count')
            ->groupBy('grade_level')
            ->get()
            ->map(function ($item) {
                $gradeLevel = GradeLevel::where('level_code', $item->grade_level)->first();
                return [
                    'grade_level' => $item->grade_level,
                    'level_name' => $gradeLevel?->level_name ?? $item->grade_level,
                    'count' => $item->count,
                ];
            });
    }

    private function calculateApprovalRate($query)
    {
        $total = $query->clone()->count();
        if ($total === 0) {
            return 0;
        }

        $approved = $query->clone()->where('status', EnrollmentStatus::APPROVED)->count();
        return round(($approved / $total) * 100, 2);
    }

    private function calculateAvgProcessingTime($query)
    {
        $enrollments = $query->clone()
            ->whereNotNull('approved_at')
            ->get(['created_at', 'approved_at']);

        if ($enrollments->isEmpty()) {
            return 0;
        }

        $totalDays = $enrollments->sum(function ($enrollment) {
            return Carbon::parse($enrollment->created_at)
                ->diffInDays(Carbon::parse($enrollment->approved_at));
        });

        return round($totalDays / $enrollments->count(), 1);
    }

    private function getEnrollmentTrend($query)
    {
        return $query->clone()
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->take(30)
            ->get();
    }
}
```

## Frontend Implementation

### Pages/Registrar/Reports/EnrollmentStatistics.tsx

```tsx
import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ReportFilter } from '@/components/reports/report-filter';
import { ReportSummaryCard } from '@/components/reports/report-summary-card';

interface Statistics {
    total: number;
    by_status: Record<string, number>;
    by_grade_level: Array<{ grade_level: string; level_name: string; count: number }>;
    approval_rate: number;
    avg_processing_time: number;
    trend_data: Array<{ date: string; count: number }>;
}

interface Props {
    statistics: Statistics;
    filters: Record<string, any>;
    schoolYears: string[];
    gradeLevels: Array<{ level_code: string; level_name: string }>;
}

export default function EnrollmentStatistics({ statistics, filters, schoolYears, gradeLevels }: Props) {
    const [localFilters, setLocalFilters] = useState(filters);

    const applyFilters = () => {
        router.get(route('registrar.reports.enrollment-statistics'), localFilters, { preserveState: true });
    };

    return (
        <AppLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold">Enrollment Statistics</h1>
                </div>

                {/* Filter Component */}
                <ReportFilter
                    filters={localFilters}
                    onFilterChange={setLocalFilters}
                    onApply={applyFilters}
                    schoolYears={schoolYears}
                    gradeLevels={gradeLevels}
                    showStatus
                />

                {/* Summary Cards */}
                <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <ReportSummaryCard title="Total Enrollments" value={statistics.total} />
                    <ReportSummaryCard title="Approval Rate" value={`${statistics.approval_rate}%`} />
                    <ReportSummaryCard title="Avg Processing Time" value={`${statistics.avg_processing_time} days`} />
                    <ReportSummaryCard title="Pending Applications" value={statistics.by_status.pending || 0} />
                </div>

                {/* Status Distribution */}
                <Card>
                    <CardHeader>
                        <CardTitle>Enrollments by Status</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="text-right">Count</TableHead>
                                    <TableHead className="text-right">Percentage</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {Object.entries(statistics.by_status).map(([status, count]) => (
                                    <TableRow key={status}>
                                        <TableCell className="font-medium capitalize">{status}</TableCell>
                                        <TableCell className="text-right">{count}</TableCell>
                                        <TableCell className="text-right">
                                            {statistics.total > 0 ? ((count / statistics.total) * 100).toFixed(1) : 0}%
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                {/* Grade Level Distribution */}
                <Card>
                    <CardHeader>
                        <CardTitle>Enrollments by Grade Level</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Grade Level</TableHead>
                                    <TableHead className="text-right">Count</TableHead>
                                    <TableHead className="text-right">Percentage</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {statistics.by_grade_level.map((grade) => (
                                    <TableRow key={grade.grade_level}>
                                        <TableCell className="font-medium">{grade.level_name}</TableCell>
                                        <TableCell className="text-right">{grade.count}</TableCell>
                                        <TableCell className="text-right">
                                            {statistics.total > 0 ? ((grade.count / statistics.total) * 100).toFixed(1) : 0}%
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
```

### Components/Reports/ReportFilter.tsx

```tsx
import React from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

interface ReportFilterProps {
    filters: Record<string, any>;
    onFilterChange: (filters: Record<string, any>) => void;
    onApply: () => void;
    schoolYears?: string[];
    gradeLevels?: Array<{ level_code: string; level_name: string }>;
    showStatus?: boolean;
}

export function ReportFilter({ filters, onFilterChange, onApply, schoolYears = [], gradeLevels = [], showStatus = false }: ReportFilterProps) {
    const updateFilter = (key: string, value: any) => {
        onFilterChange({ ...filters, [key]: value });
    };

    const clearFilters = () => {
        onFilterChange({});
        onApply();
    };

    return (
        <Card>
            <CardContent className="pt-6">
                <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                        <Label>School Year</Label>
                        <Select value={filters.school_year || ''} onValueChange={(value) => updateFilter('school_year', value)}>
                            <SelectTrigger>
                                <SelectValue placeholder="All school years" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All school years</SelectItem>
                                {schoolYears.map((year) => (
                                    <SelectItem key={year} value={year}>
                                        {year}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    <div>
                        <Label>Grade Level</Label>
                        <Select value={filters.grade_level || ''} onValueChange={(value) => updateFilter('grade_level', value)}>
                            <SelectTrigger>
                                <SelectValue placeholder="All grade levels" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All grade levels</SelectItem>
                                {gradeLevels.map((grade) => (
                                    <SelectItem key={grade.level_code} value={grade.level_code}>
                                        {grade.level_name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    {showStatus && (
                        <div>
                            <Label>Status</Label>
                            <Select value={filters.status || ''} onValueChange={(value) => updateFilter('status', value)}>
                                <SelectTrigger>
                                    <SelectValue placeholder="All statuses" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All statuses</SelectItem>
                                    <SelectItem value="pending">Pending</SelectItem>
                                    <SelectItem value="approved">Approved</SelectItem>
                                    <SelectItem value="rejected">Rejected</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    )}

                    <div className="flex items-end gap-2">
                        <Button onClick={onApply}>Apply Filters</Button>
                        <Button variant="outline" onClick={clearFilters}>
                            Clear
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
```

### Components/Reports/ReportSummaryCard.tsx

```tsx
import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface ReportSummaryCardProps {
    title: string;
    value: string | number;
    icon?: React.ReactNode;
    trend?: {
        value: number;
        isPositive: boolean;
    };
}

export function ReportSummaryCard({ title, value, icon, trend }: ReportSummaryCardProps) {
    return (
        <Card>
            <CardHeader className="pb-2">
                <div className="flex items-center justify-between">
                    <CardTitle className="text-sm font-medium text-muted-foreground">{title}</CardTitle>
                    {icon}
                </div>
            </CardHeader>
            <CardContent>
                <div className="text-2xl font-bold">{value}</div>
                {trend && (
                    <p className={`text-xs ${trend.isPositive ? 'text-green-600' : 'text-red-600'}`}>
                        {trend.isPositive ? '↑' : '↓'} {Math.abs(trend.value)}% from last period
                    </p>
                )}
            </CardContent>
        </Card>
    );
}
```

## Database Migration

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Add indexes for performance
            $table->index('created_at');
            $table->index('status');
            $table->index('school_year');
            $table->index(['school_year', 'grade_level']);
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status']);
            $table->dropIndex(['school_year']);
            $table->dropIndex(['school_year', 'grade_level']);
        });
    }
};
```

## Cache Invalidation

Add to `App\Models\Enrollment`:

```php
protected static function booted()
{
    static::created(function () {
        Cache::tags(['reports', 'enrollments'])->flush();
    });

    static::updated(function () {
        Cache::tags(['reports', 'enrollments'])->flush();
    });

    static::deleted(function () {
        Cache::tags(['reports', 'enrollments'])->flush();
    });
}
```

## Dependencies

- Requires `EnrollmentPeriod` model (TICKET-007)
- Uses Laravel Cache with tag support
- shadcn/ui Card, Table, Select components

## Breaking Changes

None

## Deployment Notes

- Run migration: `php artisan migrate`
- Clear cache: `php artisan cache:clear`
- Build frontend: `npm run build`

## Post-Merge Checklist

- [ ] Report accessible to registrar and super admin
- [ ] Filters work correctly
- [ ] Statistics calculate accurately
- [ ] Caching improves performance
- [ ] Cache invalidates on enrollment changes
- [ ] Responsive design works
- [ ] Authorization enforced
- [ ] Next ticket (TICKET-018) can begin

## Reviewer Notes

Please verify:

1. Statistics calculations are accurate
2. Caching doesn't cause stale data issues
3. Filters apply correctly to queries
4. Performance is acceptable with large datasets
5. Authorization restricts access properly
6. UI is intuitive and informative
7. No N+1 query issues
8. Indexes improve query performance

## Performance Considerations

- Caching reduces database load (1-hour TTL)
- Indexes on created_at, status, school_year
- Query cloning prevents query mutation issues
- Pagination may be needed for trend data in future

## Security Considerations

- Only registrar and super admin can access
- Cache keys include filter hash to prevent poisoning
- No sensitive student data exposed in summary views
- Authorization enforced at route and controller level

---

**Ticket:** #017
**Estimated Effort:** 1.5 days
**Actual Effort:** _[To be filled after completion]_

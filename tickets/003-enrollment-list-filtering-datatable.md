# Enrollment List Filtering and DataTable Conversion

**Status:** Not Started
**Priority:** High
**Type:** Enhancement
**Estimated Effort:** 8-10 hours

## Description

Convert guardian enrollment list to DataTable with filtering, sorting, and search capabilities. Essential for usability when guardians have multiple children or multiple years of enrollment history.

## Referenced In

- GUARDIAN_USER_JOURNEY.md (lines 573-577)

## Current State

- **Location:** `app/Http/Controllers/Guardian/EnrollmentController.php` (index method)
- **Frontend:** `resources/js/pages/guardian/enrollments/index.tsx`
- **Current:** Simple table with pagination, no filtering or sorting
- **Issue:** Hard to find specific enrollments with multiple children/years

## Required Features

### 1. Filter by School Year

- [ ] Dropdown showing all school years with enrollments
- [ ] Option: "All School Years"
- [ ] Updates URL query parameter

### 2. Filter by Student

- [ ] Dropdown showing all guardian's students
- [ ] Option: "All Students"
- [ ] Updates URL query parameter

### 3. Filter by Status

- [ ] Dropdown: All / Pending / Enrolled / Rejected / Completed
- [ ] Updates URL query parameter

### 4. Sort by Date

- [ ] Sortable created_at column (ascending/descending)
- [ ] Default: Most recent first

### 5. Search Functionality

- [ ] Search box for student name or enrollment ID
- [ ] Debounced search (300ms delay)
- [ ] Updates URL query parameter

### 6. DataTable UI

- [ ] Use TanStack Table like student list
- [ ] Column definitions in separate file
- [ ] Responsive design
- [ ] Mobile-friendly

### 7. Clear Filters

- [ ] Button to reset all filters
- [ ] Returns to default view

## Implementation Plan

### 1. Backend - Update Controller

**File:** `app/Http/Controllers/Guardian/EnrollmentController.php`

```php
public function index(Request $request)
{
    // Get Guardian model for authenticated user
    $guardian = Guardian::where('user_id', Auth::id())->firstOrFail();

    // Get student IDs for this guardian
    $studentIds = GuardianStudent::where('guardian_id', $guardian->id)
        ->pluck('student_id');

    // Build query
    $query = Enrollment::with(['student', 'guardian'])
        ->whereIn('student_id', $studentIds);

    // Apply filters
    if ($request->filled('school_year')) {
        $query->where('school_year', $request->school_year);
    }

    if ($request->filled('student_id')) {
        $query->where('student_id', $request->student_id);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // Apply search
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search, $studentIds) {
            $q->whereIn('student_id', function($subQuery) use ($search) {
                $subQuery->select('id')
                    ->from('students')
                    ->where(function($sq) use ($search) {
                        $sq->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%");
                    });
            })
            ->orWhere('enrollment_id', 'like', "%{$search}%");
        });
    }

    // Apply sorting
    $sortBy = $request->get('sort_by', 'created_at');
    $sortOrder = $request->get('sort_order', 'desc');
    $query->orderBy($sortBy, $sortOrder);

    $enrollments = $query->paginate(10)->withQueryString();

    // Get filter options
    $schoolYears = Enrollment::whereIn('student_id', $studentIds)
        ->distinct()
        ->pluck('school_year')
        ->sort()
        ->values();

    $students = Student::whereIn('id', $studentIds)
        ->get()
        ->map(fn($s) => [
            'id' => $s->id,
            'name' => trim("{$s->first_name} {$s->last_name}"),
        ]);

    return Inertia::render('guardian/enrollments/index', [
        'enrollments' => $enrollments,
        'filters' => [
            'school_year' => $request->school_year,
            'student_id' => $request->student_id,
            'status' => $request->status,
            'search' => $request->search,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
        ],
        'schoolYears' => $schoolYears,
        'students' => $students,
    ]);
}
```

### 2. Frontend - Create Column Definitions

**File:** `resources/js/pages/guardian/enrollments/columns.tsx`

```typescript
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';
import { type ColumnDef } from '@tanstack/react-table';
import { ArrowUpDown, Eye, Pencil } from 'lucide-react';

export interface Enrollment {
    id: number;
    enrollment_id: string;
    student: {
        first_name: string;
        last_name: string;
    };
    school_year: string;
    grade_level: string;
    status: 'pending' | 'enrolled' | 'rejected' | 'completed';
    payment_status: 'pending' | 'partial' | 'paid' | 'overdue';
    created_at: string;
}

const statusColors = {
    pending: 'secondary',
    enrolled: 'default',
    rejected: 'destructive',
    completed: 'outline',
} as const;

const paymentStatusColors = {
    pending: 'secondary',
    partial: 'outline',
    paid: 'default',
    overdue: 'destructive',
} as const;

export const columns: ColumnDef<Enrollment>[] = [
    {
        accessorKey: 'enrollment_id',
        header: ({ column }) => (
            <Button
                variant="ghost"
                onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
            >
                Enrollment ID
                <ArrowUpDown className="ml-2 h-4 w-4" />
            </Button>
        ),
        cell: ({ row }) => (
            <div className="font-mono text-sm">{row.getValue('enrollment_id')}</div>
        ),
    },
    {
        id: 'student_name',
        accessorFn: (row) => `${row.student.first_name} ${row.student.last_name}`,
        header: ({ column }) => (
            <Button
                variant="ghost"
                onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
            >
                Student Name
                <ArrowUpDown className="ml-2 h-4 w-4" />
            </Button>
        ),
    },
    {
        accessorKey: 'school_year',
        header: 'School Year',
    },
    {
        accessorKey: 'grade_level',
        header: 'Grade Level',
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: ({ row }) => (
            <Badge variant={statusColors[row.getValue('status') as keyof typeof statusColors]}>
                {row.getValue('status')}
            </Badge>
        ),
    },
    {
        accessorKey: 'payment_status',
        header: 'Payment',
        cell: ({ row }) => (
            <Badge variant={paymentStatusColors[row.getValue('payment_status') as keyof typeof paymentStatusColors]}>
                {row.getValue('payment_status')}
            </Badge>
        ),
    },
    {
        accessorKey: 'created_at',
        header: ({ column }) => (
            <Button
                variant="ghost"
                onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
            >
                Date Submitted
                <ArrowUpDown className="ml-2 h-4 w-4" />
            </Button>
        ),
        cell: ({ row }) => new Date(row.getValue('created_at')).toLocaleDateString(),
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const enrollment = row.original;
            return (
                <div className="flex gap-2">
                    <Button size="sm" variant="outline" asChild>
                        <Link href={`/guardian/enrollments/${enrollment.id}`}>
                            <Eye className="mr-1 h-3 w-3" />
                            View
                        </Link>
                    </Button>
                    {enrollment.status === 'pending' && (
                        <Button size="sm" variant="outline" asChild>
                            <Link href={`/guardian/enrollments/${enrollment.id}/edit`}>
                                <Pencil className="mr-1 h-3 w-3" />
                                Edit
                            </Link>
                        </Button>
                    )}
                </div>
            );
        },
    },
];
```

### 3. Frontend - Update Index Page

**File:** `resources/js/pages/guardian/enrollments/index.tsx`

```typescript
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { router } from '@inertiajs/react';
import {
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useReactTable,
    type ColumnFiltersState,
    type SortingState,
} from '@tanstack/react-table';
import { flexRender } from '@tanstack/react-table';
import { useState } from 'react';
import { columns } from './columns';

interface Props {
    enrollments: {
        data: Enrollment[];
        links: unknown;
        meta: unknown;
    };
    filters: {
        school_year?: string;
        student_id?: number;
        status?: string;
        search?: string;
        sort_by?: string;
        sort_order?: string;
    };
    schoolYears: string[];
    students: Array<{ id: number; name: string }>;
}

export default function GuardianEnrollmentsIndex({
    enrollments,
    filters,
    schoolYears,
    students,
}: Props) {
    const [sorting, setSorting] = useState<SortingState>([]);
    const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([]);

    const table = useReactTable({
        data: enrollments.data,
        columns,
        onSortingChange: setSorting,
        onColumnFiltersChange: setColumnFilters,
        getCoreRowModel: getCoreRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
        getSortedRowModel: getSortedRowModel(),
        getFilteredRowModel: getFilteredRowModel(),
        state: {
            sorting,
            columnFilters,
        },
    });

    const applyFilters = (newFilters: Record<string, string | undefined>) => {
        const params = { ...filters, ...newFilters };
        // Remove undefined values
        Object.keys(params).forEach((key) => {
            if (params[key] === undefined || params[key] === '') {
                delete params[key];
            }
        });
        router.get('/guardian/enrollments', params, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const clearFilters = () => {
        router.get('/guardian/enrollments', {}, {
            preserveState: false,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="My Children's Enrollments" />

            <div className="px-4 py-6">
                <div className="mb-4 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">My Children's Enrollments</h1>
                    <Link href="/guardian/enrollments/create">
                        <Button>
                            <PlusCircle className="mr-2 h-4 w-4" />
                            New Enrollment
                        </Button>
                    </Link>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Enrollment Applications</CardTitle>
                        <CardDescription>Filter and view your children's enrollment applications</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {/* Filters */}
                        <div className="mb-6 grid gap-4 md:grid-cols-4">
                            <div>
                                <Label>Search</Label>
                                <Input
                                    placeholder="Student name or ID..."
                                    value={filters.search || ''}
                                    onChange={(e) => applyFilters({ search: e.target.value })}
                                />
                            </div>
                            <div>
                                <Label>School Year</Label>
                                <Select
                                    value={filters.school_year || 'all'}
                                    onValueChange={(value) =>
                                        applyFilters({ school_year: value === 'all' ? undefined : value })
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Years" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All School Years</SelectItem>
                                        {schoolYears.map((year) => (
                                            <SelectItem key={year} value={year}>
                                                {year}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <Label>Student</Label>
                                <Select
                                    value={filters.student_id?.toString() || 'all'}
                                    onValueChange={(value) =>
                                        applyFilters({ student_id: value === 'all' ? undefined : value })
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Students" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Students</SelectItem>
                                        {students.map((student) => (
                                            <SelectItem key={student.id} value={student.id.toString()}>
                                                {student.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <Label>Status</Label>
                                <Select
                                    value={filters.status || 'all'}
                                    onValueChange={(value) =>
                                        applyFilters({ status: value === 'all' ? undefined : value })
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Statuses" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Statuses</SelectItem>
                                        <SelectItem value="pending">Pending</SelectItem>
                                        <SelectItem value="enrolled">Enrolled</SelectItem>
                                        <SelectItem value="rejected">Rejected</SelectItem>
                                        <SelectItem value="completed">Completed</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div className="mb-4 flex justify-end">
                            <Button variant="outline" onClick={clearFilters}>
                                Clear Filters
                            </Button>
                        </div>

                        {/* DataTable */}
                        <Table>
                            <TableHeader>
                                {table.getHeaderGroups().map((headerGroup) => (
                                    <TableRow key={headerGroup.id}>
                                        {headerGroup.headers.map((header) => (
                                            <TableHead key={header.id}>
                                                {flexRender(
                                                    header.column.columnDef.header,
                                                    header.getContext()
                                                )}
                                            </TableHead>
                                        ))}
                                    </TableRow>
                                ))}
                            </TableHeader>
                            <TableBody>
                                {table.getRowModel().rows.map((row) => (
                                    <TableRow key={row.id}>
                                        {row.getVisibleCells().map((cell) => (
                                            <TableCell key={cell.id}>
                                                {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                            </TableCell>
                                        ))}
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>

                        {/* Pagination */}
                        <div className="mt-4 flex items-center justify-between">
                            <div className="text-sm text-muted-foreground">
                                Showing {enrollments.data.length} of {enrollments.meta.total} enrollments
                            </div>
                            {/* Add pagination buttons here */}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
```

## Acceptance Criteria

- [ ] Backend supports query parameters: `school_year`, `student_id`, `status`, `search`, `sort_by`, `sort_order`
- [ ] Frontend has filter UI above table
- [ ] Filters work correctly and return expected results
- [ ] Search works for student names and enrollment IDs
- [ ] Sorting works on all sortable columns
- [ ] Maintains pagination with filters
- [ ] URL reflects current filters (shareable links)
- [ ] Clear filters button resets to default view
- [ ] Responsive design works on mobile
- [ ] Loading states during filter changes
- [ ] Empty state when no results

## Testing Checklist

- [ ] Filter by school year shows only that year's enrollments
- [ ] Filter by student shows only that student's enrollments
- [ ] Filter by status shows only matching statuses
- [ ] Search finds enrollments by student name
- [ ] Search finds enrollments by enrollment ID
- [ ] Multiple filters work together correctly
- [ ] Clear filters resets all filters
- [ ] Sorting by date works (ascending/descending)
- [ ] Pagination works with filters applied
- [ ] URL updates with filter parameters
- [ ] Shareable URL with filters works correctly
- [ ] Back button maintains filter state
- [ ] Mobile responsive design works
- [ ] No console errors
- [ ] Performance is acceptable with 100+ enrollments

## Implementation Pattern

Follow same pattern as guardian students DataTable:

- `resources/js/pages/guardian/students/index.tsx`
- `resources/js/pages/guardian/students/columns.tsx`

## Priority

**High** - Essential for usability with multiple children or multiple years

## Dependencies

- TanStack Table v8 (already installed)
- shadcn/ui components (already available)

## Notes

- Consider adding export to Excel/CSV functionality later
- May want to add saved filter presets (future)
- Consider adding date range filter for created_at
- Mobile view should stack filters vertically

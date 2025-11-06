import { SchoolYearStatusBadge } from '@/components/status-badges';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { ColumnDef, SortingState } from '@tanstack/react-table';
import { Calendar, Plus } from 'lucide-react';
import { useEffect, useState } from 'react';

interface SchoolYear {
    id: number;
    name: string;
    start_year: number;
    end_year: number;
    start_date: string;
    end_date: string;
    status: string;
    is_active: boolean;
    enrollments_count: number;
}

interface PaginatedSchoolYears {
    data: SchoolYear[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    schoolYears: PaginatedSchoolYears & {
        filters: {
            sort_by?: string;
            sort_direction?: string;
        };
    };
    activeSchoolYear: SchoolYear | null;
}

export default function SchoolYearsIndex({ schoolYears, activeSchoolYear }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'School Years', href: '/super-admin/school-years' },
    ];

    const [sorting, setSorting] = useState<SortingState>(
        schoolYears.filters.sort_by && schoolYears.filters.sort_direction
            ? [{ id: schoolYears.filters.sort_by, desc: schoolYears.filters.sort_direction === 'desc' }]
            : [],
    );

    useEffect(() => {
        const handler = setTimeout(() => {
            router.get(
                route('super-admin.school-years.index'),
                {
                    ...schoolYears.filters,
                    sort_by: sorting.length > 0 ? sorting[0].id : undefined,
                    sort_direction: sorting.length > 0 ? (sorting[0].desc ? 'desc' : 'asc') : undefined,
                },
                { preserveState: true, replace: true },
            );
        }, 300);

        return () => clearTimeout(handler);
    }, [sorting]);

    const columns: ColumnDef<SchoolYear>[] = [
        {
            accessorKey: 'name',
            header: 'School Year',
            cell: ({ row }) => (
                <div className="flex items-center gap-2">
                    <span className="font-medium">{row.original.name}</span>
                    {row.original.is_active && (
                        <Badge variant="default" className="ml-2">
                            Active
                        </Badge>
                    )}
                </div>
            ),
        },
        {
            accessorKey: 'status',
            header: 'Status',
            cell: ({ row }) => <SchoolYearStatusBadge status={row.original.status} />,
        },
        {
            accessorKey: 'start_date',
            header: 'Start Date',
            cell: ({ row }) => new Date(row.original.start_date).toLocaleDateString(),
        },
        {
            accessorKey: 'end_date',
            header: 'End Date',
            cell: ({ row }) => new Date(row.original.end_date).toLocaleDateString(),
        },
        {
            accessorKey: 'enrollments_count',
            header: 'Enrollments',
        },
        {
            id: 'actions',
            header: 'Actions',
            cell: ({ row }) => (
                <div className="flex gap-2">
                    <Button size="sm" variant="outline" onClick={() => router.visit(`/super-admin/school-years/${row.original.id}`)}>
                        View
                    </Button>
                    <Button size="sm" variant="outline" onClick={() => router.visit(`/super-admin/school-years/${row.original.id}/edit`)}>
                        Edit
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="School Years" />
            <div className="px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">School Years</h1>
                        {activeSchoolYear && (
                            <p className="mt-1 text-sm text-muted-foreground">
                                <Calendar className="mr-1 inline h-4 w-4" />
                                Active: {activeSchoolYear.name}
                            </p>
                        )}
                    </div>
                    <Link href="/super-admin/school-years/create">
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Create School Year
                        </Button>
                    </Link>
                </div>
                <DataTable columns={columns} data={schoolYears.data} sorting={sorting} onSortingChange={setSorting} />
            </div>
        </AppLayout>
    );
}

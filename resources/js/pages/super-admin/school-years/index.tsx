import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import { Calendar, Plus } from 'lucide-react';

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
    schoolYears: PaginatedSchoolYears;
    activeSchoolYear: SchoolYear | null;
}

export default function SchoolYearsIndex({ schoolYears, activeSchoolYear }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'School Years', href: '/super-admin/school-years' },
    ];

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
            cell: ({ row }) => {
                const statusColors: Record<string, 'default' | 'secondary' | 'outline'> = {
                    upcoming: 'secondary',
                    active: 'default',
                    completed: 'outline',
                };
                const variant = statusColors[row.original.status] || 'default';
                return <Badge variant={variant}>{row.original.status.charAt(0).toUpperCase() + row.original.status.slice(1)}</Badge>;
            },
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
                <DataTable columns={columns} data={schoolYears.data} />
            </div>
        </AppLayout>
    );
}

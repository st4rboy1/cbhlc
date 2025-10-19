import { EnrollmentFilters } from '@/components/enrollment-filters';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Enrollment, type Paginated } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { columns } from './columns';

interface Props {
    enrollments: Paginated<Enrollment>;
    filters: Record<string, string>;
    statusCounts: {
        all: number;
        pending: number;
        approved: number;
        rejected: number;
        enrolled: number;
        completed: number;
    };
}

export default function EnrollmentsIndex({ enrollments, filters, statusCounts }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Enrollments', href: '/admin/enrollments' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Enrollments" />
            <div className="px-4 py-6">
                <div className="mb-4 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Admin Enrollments</h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {enrollments.total} {enrollments.total === 1 ? 'enrollment' : 'enrollments'}
                        </p>
                    </div>
                    <Button asChild className="gap-2">
                        <Link href="/admin/enrollments/create">
                            <Plus className="h-4 w-4" />
                            New Enrollment
                        </Link>
                    </Button>
                </div>

                <EnrollmentFilters filters={filters} statusCounts={statusCounts} />

                <div className="mt-6">
                    <DataTable columns={columns} data={enrollments.data} />
                </div>

                <div className="mt-4 flex items-center justify-between border-t pt-4">
                    {enrollments.prev_page_url ? (
                        <Link href={enrollments.prev_page_url} className="text-sm font-medium text-primary hover:underline">
                            Previous
                        </Link>
                    ) : (
                        <div />
                    )}
                    <div className="text-sm text-gray-500">
                        Page {enrollments.current_page} of {enrollments.last_page}
                    </div>
                    {enrollments.next_page_url ? (
                        <Link href={enrollments.next_page_url} className="text-sm font-medium text-primary hover:underline">
                            Next
                        </Link>
                    ) : (
                        <div />
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

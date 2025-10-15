import { EnrollmentHeader } from '@/components/enrollment-header';
import { EnrollmentList } from '@/components/enrollment-list';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Enrollment, type Paginated } from '@/types';
import { Head } from '@inertiajs/react';

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
                <EnrollmentHeader />
                <EnrollmentList enrollments={enrollments} filters={filters} statusCounts={statusCounts} />
            </div>
        </AppLayout>
    );
}

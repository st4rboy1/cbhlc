import { EnrollmentHeader } from '@/components/enrollment-header';
import { EnrollmentList } from '@/components/enrollment-list';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function EnrollmentsIndex() {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Enrollments', href: '/admin/enrollments' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Enrollments" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Admin Enrollments Index</h1>
                <EnrollmentHeader />
                <EnrollmentList />
            </div>
        </AppLayout>
    );
}

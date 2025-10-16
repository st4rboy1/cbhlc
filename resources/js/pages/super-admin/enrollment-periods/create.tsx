import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function EnrollmentPeriodCreate() {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Enrollment Periods', href: '/super-admin/enrollment-periods' },
        { title: 'Create', href: '/super-admin/enrollment-periods/create' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Enrollment Period" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Create Enrollment Period</h1>
                <div className="rounded bg-yellow-50 p-4 text-yellow-800">TODO: UI implementation pending (TICKET-009)</div>
            </div>
        </AppLayout>
    );
}

import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function AdminEnrollmentPeriodsIndex() {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Enrollment Periods', href: '/admin/enrollment-periods' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Enrollment Periods" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Enrollment Periods</h1>
                <p className="text-muted-foreground">This page will contain a list of enrollment periods.</p>
            </div>
        </AppLayout>
    );
}

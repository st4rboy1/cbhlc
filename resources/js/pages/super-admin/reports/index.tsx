import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function SuperAdminReports() {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Reports', href: '/super-admin/reports' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="System Reports" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">System Reports</h1>
                <p className="text-muted-foreground">This page will contain system reports and analytics.</p>
            </div>
        </AppLayout>
    );
}

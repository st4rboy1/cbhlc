import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function AdminReportsIndex() {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Reports', href: '/admin/reports' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Reports" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Reports</h1>
                <p className="text-muted-foreground">This page will contain various reports for the administrator.</p>
            </div>
        </AppLayout>
    );
}

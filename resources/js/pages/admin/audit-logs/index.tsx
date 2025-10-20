import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function AdminAuditLogsIndex() {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Audit Logs', href: '/admin/audit-logs' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Audit Logs" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Audit Logs</h1>
                <p className="text-muted-foreground">This page will contain a list of audit logs.</p>
            </div>
        </AppLayout>
    );
}

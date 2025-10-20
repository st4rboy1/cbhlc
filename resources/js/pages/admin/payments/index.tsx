import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function AdminPaymentsIndex() {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Payments', href: '/admin/payments' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Payments" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Payments</h1>
                <p className="text-muted-foreground">This page will contain a list of payments.</p>
            </div>
        </AppLayout>
    );
}

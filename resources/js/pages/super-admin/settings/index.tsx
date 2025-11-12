import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function SuperAdminSettings() {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Settings', href: '/super-admin/settings' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Account Settings" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Account Settings</h1>
                <p className="text-muted-foreground">This page will contain system and account settings.</p>
            </div>
        </AppLayout>
    );
}

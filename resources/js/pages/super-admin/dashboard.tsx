import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    stats: {
        total_students: number;
        pending_enrollments: number;
        active_users: number;
        total_revenue: number;
    };
    message: string;
}

export default function SuperAdminDashboard({ stats, message }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [{ title: 'Super Admin Dashboard', href: '/super-admin/dashboard' }];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Super Admin Dashboard" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Super Admin Dashboard</h1>
                <pre className="overflow-auto rounded bg-gray-100 p-4">{JSON.stringify({ stats, message }, null, 2)}</pre>
            </div>
        </AppLayout>
    );
}

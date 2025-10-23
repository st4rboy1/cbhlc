import { SuperAdminDashboard } from '@/components/super-admin-dashboard';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    stats: {
        // Core metrics
        total_students: number;
        active_enrollments: number;
        pending_enrollments: number;
        total_revenue: number;

        // User metrics
        total_users: number;
        total_guardians: number;

        // Enrollment metrics
        approved_enrollments: number;
        completed_enrollments: number;
        rejected_enrollments: number;

        // Payment metrics
        total_invoices: number;
        paid_invoices: number;
        partial_payments: number;
        pending_payments: number;
        total_collected: number;
        total_balance: number;
        collection_rate: number;

        // Transaction metrics
        total_payments: number;
        recent_payments_count: number;
    };
}

export default function SuperAdminDashboardPage({ stats }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [{ title: 'Super Admin Dashboard', href: '/super-admin/dashboard' }];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Super Admin Dashboard" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Super Admin Dashboard</h1>
                <SuperAdminDashboard stats={stats} />
            </div>
        </AppLayout>
    );
}

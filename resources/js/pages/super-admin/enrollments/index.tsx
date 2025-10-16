import { EnrollmentDashboard } from '@/components/enrollment-dashboard';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function EnrollmentsIndex() {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Enrollments', href: '/super-admin/enrollments' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Enrollments" />
            <div className="px-4 py-6">
                <EnrollmentDashboard />
            </div>
        </AppLayout>
    );
}

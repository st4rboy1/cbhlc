import AppLayout from '@/layouts/app-layout';
import { GuardianDashboard } from '@/pages/guardian/students/guardian-dashboard';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function GuardianStudentsIndex() {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Guardian', href: '/guardian/dashboard' },
        { title: 'Students', href: '/guardian/students' },
    ];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Students Index" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Students Index</h1>
                <GuardianDashboard />
            </div>
        </AppLayout>
    );
}

import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

// eslint-disable-next-line @typescript-eslint/no-unused-vars
export default function GuardianEnrollmentsEdit(props: { enrollment: unknown }) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Guardian', href: '/guardian/dashboard' },
        { title: 'Enrollments', href: '/guardian/enrollments' },
        { title: 'Edit Enrollment', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Enrollment" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Edit Enrollment</h1>
                <p>Edit form will be implemented here</p>
            </div>
        </AppLayout>
    );
}

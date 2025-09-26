import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function GuardianEnrollmentsShow({ enrollment }: { enrollment: unknown }) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Guardian', href: '/guardian/dashboard' },
        { title: 'Enrollments', href: '/guardian/enrollments' },
        { title: 'Enrollment Details', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Enrollment Details" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Enrollment Details</h1>
                <pre className="overflow-auto rounded bg-gray-100 p-4">{JSON.stringify(enrollment, null, 2)}</pre>
            </div>
        </AppLayout>
    );
}

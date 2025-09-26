import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    [key: string]: unknown;
}
export default function EnrollmentShow(props: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Enrollments', href: '/admin/enrollments' },
        { title: `#${enrollment?.id}`, href: `/admin/enrollments/${enrollment?.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Enrollment #${enrollment?.id}`} />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Admin Enrollment Show</h1>
                <pre className="overflow-auto rounded bg-gray-100 p-4">{JSON.stringify(props, null, 2)}</pre>
            </div>
        </AppLayout>
    );
}

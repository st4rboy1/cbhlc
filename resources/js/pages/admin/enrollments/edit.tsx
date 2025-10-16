import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    enrollment?: {
        id: number;
        student_name: string;
    };
}

export default function EnrollmentEdit({ enrollment }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Enrollments', href: '/admin/enrollments' },
        { title: `Edit #${enrollment?.id}`, href: `/admin/enrollments/${enrollment?.id}/edit` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Enrollment #${enrollment?.id}`} />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Admin Enrollment Edit</h1>
                <pre className="overflow-auto rounded bg-gray-100 p-4">{JSON.stringify({ enrollment }, null, 2)}</pre>
            </div>
        </AppLayout>
    );
}

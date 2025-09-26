import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    enrollments?: Array<{
        id: number;
        student_name: string;
        grade: string;
        status: string;
    }>;
    total?: number;
}

export default function EnrollmentsIndex({ enrollments, total }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Enrollments', href: '/admin/enrollments' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Enrollments" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Admin Enrollments Index</h1>
                <pre className="overflow-auto rounded bg-gray-100 p-4">{JSON.stringify({ enrollments, total }, null, 2)}</pre>
            </div>
        </AppLayout>
    );
}

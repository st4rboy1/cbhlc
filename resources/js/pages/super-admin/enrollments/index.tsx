import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    enrollments: Array<{
        id: number;
        student_name: string;
        grade: string;
        status: string;
    }>;
    filters: Record<string, unknown>;
}

export default function EnrollmentsIndex({ enrollments, filters }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Enrollments', href: '/super-admin/enrollments' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Enrollments" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Enrollments Index</h1>
                <pre className="overflow-auto rounded bg-gray-100 p-4">{JSON.stringify({ enrollments, filters }, null, 2)}</pre>
            </div>
        </AppLayout>
    );
}

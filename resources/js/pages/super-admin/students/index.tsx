import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    students: Array<{
        id: number;
        name: string;
        grade: string;
        status: string;
    }>;
    total: number;
}

export default function StudentsIndex({ students, total }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Students', href: '/super-admin/students' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Students" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Students Index</h1>
                <pre className="overflow-auto rounded bg-gray-100 p-4">{JSON.stringify({ students, total }, null, 2)}</pre>
            </div>
        </AppLayout>
    );
}

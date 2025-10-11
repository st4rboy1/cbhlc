import AppLayout from '@/layouts/app-layout';
import { StudentsTable } from '@/pages/admin/students/students-table';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    students?: Array<{
        id: number;
        name: string;
        grade: string;
        status: string;
    }>;
    total?: number;
}

export default function StudentsIndex({ students }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Students', href: '/admin/students' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Students" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Admin Students Index</h1>
                <StudentsTable students={students || []} />
            </div>
        </AppLayout>
    );
}

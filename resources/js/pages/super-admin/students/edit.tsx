import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    student: {
        id: string | number;
        name: string;
        grade: string;
        status: string;
    };
}

export default function StudentEdit({ student }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Students', href: '/super-admin/students' },
        { title: `Edit ${student.name}`, href: `/super-admin/students/${student.id}/edit` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${student.name}`} />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Student Edit</h1>
                <pre className="overflow-auto rounded bg-gray-100 p-4">{JSON.stringify({ student }, null, 2)}</pre>
            </div>
        </AppLayout>
    );
}

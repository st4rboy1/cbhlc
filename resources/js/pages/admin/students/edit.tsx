import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    student?: {
        id: number;
        name: string;
    };
}

export default function StudentEdit({ student }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Students', href: '/admin/students' },
        { title: `Edit ${student?.name}`, href: `/admin/students/${student?.id}/edit` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${student?.name}`} />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Admin Student Edit</h1>
                <pre className="overflow-auto rounded bg-gray-100 p-4">{JSON.stringify({ student }, null, 2)}</pre>
            </div>
        </AppLayout>
    );
}

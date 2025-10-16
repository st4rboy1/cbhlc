import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    student?: {
        id: number;
        name: string;
    };
}
export default function StudentShow({ student }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Students', href: '/admin/students' },
        { title: student?.name || 'Student', href: `/admin/students/${student?.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={student?.name || 'Student'} />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Admin Student Show</h1>
                <pre className="overflow-auto rounded bg-gray-100 p-4">{JSON.stringify({ student }, null, 2)}</pre>
            </div>
        </AppLayout>
    );
}

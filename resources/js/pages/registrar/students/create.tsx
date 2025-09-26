import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function RegistrarStudentsCreate(props: unknown) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Registrar', href: '/registrar/dashboard' },
        { title: 'Students', href: '/registrar/students' },
        { title: 'Create', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Students Create" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Students Create</h1>
                <pre className="overflow-auto rounded bg-gray-100 p-4">{JSON.stringify(props, null, 2)}</pre>
            </div>
        </AppLayout>
    );
}

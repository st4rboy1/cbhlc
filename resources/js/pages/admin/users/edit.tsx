import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    user?: {
        id: number;
        name: string;
    };
    roles?: Array<{ id: number; name: string }>;
}

export default function UserEdit({ user, roles }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Users', href: '/admin/users' },
        { title: `Edit ${user?.name}`, href: `/admin/users/${user?.id}/edit` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${user?.name}`} />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Admin User Edit</h1>
                <pre className="overflow-auto rounded bg-gray-100 p-4">{JSON.stringify({ user, roles }, null, 2)}</pre>
            </div>
        </AppLayout>
    );
}

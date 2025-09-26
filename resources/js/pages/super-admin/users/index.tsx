import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    users: Array<{
        id: number;
        name: string;
        email: string;
        role: string;
    }>;
    total: number;
}

export default function UsersIndex({ users, total }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Users', href: '/super-admin/users' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Users" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Users Index</h1>
                <pre className="overflow-auto rounded bg-gray-100 p-4">{JSON.stringify({ users, total }, null, 2)}</pre>
            </div>
        </AppLayout>
    );
}

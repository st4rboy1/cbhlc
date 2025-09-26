import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    user: {
        id: string | number;
        name: string;
        email: string;
        role: string;
        created_at: string;
        permissions: unknown[];
    };
}

export default function UserShow({ user }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Users', href: '/super-admin/users' },
        { title: user.name, href: `/super-admin/users/${user.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={user.name} />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">User Show</h1>
                <pre className="overflow-auto rounded bg-gray-100 p-4">{JSON.stringify({ user }, null, 2)}</pre>
            </div>
        </AppLayout>
    );
}

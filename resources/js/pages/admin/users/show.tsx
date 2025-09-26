import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    user?: {
        id: number;
        name: string;
    };
}

export default function UserShow({ user }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Users', href: '/admin/users' },
        { title: user?.name || 'User', href: `/admin/users/${user?.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={user?.name || 'User'} />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Admin User Show</h1>
                <pre className="overflow-auto rounded bg-gray-100 p-4">{JSON.stringify({ user }, null, 2)}</pre>
            </div>
        </AppLayout>
    );
}

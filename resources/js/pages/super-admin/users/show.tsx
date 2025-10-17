import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    user: {
        id: string | number;
        name: string;
        email: string;
        address: string;
        roles: Array<{ id: number; name: string }>;
        created_at: string;
        permissions: unknown[];
    };
}

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
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
                <h1 className="mb-4 text-2xl font-bold">User Details</h1>
                <div className="space-y-4">
                    <div>
                        <h2 className="text-lg font-semibold">Personal Information</h2>
                        <p>
                            <strong>Name:</strong> {user.name}
                        </p>
                        <p>
                            <strong>Email:</strong> {user.email}
                        </p>
                        {user.address && (
                            <p>
                                <strong>Address:</strong> {user.address}
                            </p>
                        )}
                        <p>
                            <strong>Created At:</strong> {formatDate(user.created_at)}
                        </p>
                    </div>

                    {user.roles.length > 0 && (
                        <div>
                            <h2 className="text-lg font-semibold">Roles</h2>
                            <div className="flex flex-wrap gap-1">
                                {user.roles.map((role) => (
                                    <Badge key={role.id} variant="secondary">
                                        {role.name}
                                    </Badge>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

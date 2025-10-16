import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type User } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    user: User;
}

export default function UserShow({ user }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Users', href: '/admin/users' },
        { title: user.name, href: `/admin/users/${user.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`User - ${user.name}`} />
            <div className="px-4 py-6">
                <Card>
                    <CardHeader>
                        <CardTitle>User Details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <p className="font-semibold">Name</p>
                                <p>{user.name}</p>
                            </div>
                            <div>
                                <p className="font-semibold">Email</p>
                                <p>{user.email}</p>
                            </div>
                            <div>
                                <p className="font-semibold">Role</p>
                                <p>{user.role}</p>
                            </div>
                            <div>
                                <p className="font-semibold">Created At</p>
                                <p>{new Date(user.created_at).toLocaleDateString()}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

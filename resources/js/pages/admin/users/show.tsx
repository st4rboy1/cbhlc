import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Guardian {
    id: number;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    contact_number: string | null;
    address: string | null;
    occupation: string | null;
    employer: string | null;
    emergency_contact_name: string | null;
    emergency_contact_phone: string | null;
    emergency_contact_relationship: string | null;
}

interface Props {
    user: {
        id: string | number;
        name: string;
        email: string;
        address: string;
        roles: Array<{ id: number; name: string }>;
        created_at: string;
        permissions: unknown[];
        guardian?: Guardian;
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
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Users', href: '/admin/users' },
        { title: user.name, href: `/admin/users/${user.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={user.name} />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">User Details</h1>

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card>
                        <CardHeader>
                            <CardTitle>Personal Information</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Name</p>
                                <p className="text-lg font-semibold">{user.name}</p>
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Email</p>
                                <p className="text-lg font-semibold">{user.email}</p>
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Created At</p>
                                <p className="text-lg font-semibold">{formatDate(user.created_at)}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Contact Information</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            {user.guardian ? (
                                <>
                                    {user.guardian.contact_number && (
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">Contact Number</p>
                                            <p className="text-lg font-semibold">{user.guardian.contact_number}</p>
                                        </div>
                                    )}
                                    {user.guardian.address && (
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">Address</p>
                                            <p className="text-lg font-semibold">{user.guardian.address}</p>
                                        </div>
                                    )}
                                    {user.guardian.occupation && (
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">Occupation</p>
                                            <p className="text-lg font-semibold">{user.guardian.occupation}</p>
                                        </div>
                                    )}
                                    {user.guardian.employer && (
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">Employer</p>
                                            <p className="text-lg font-semibold">{user.guardian.employer}</p>
                                        </div>
                                    )}
                                </>
                            ) : (
                                user.address && (
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Address</p>
                                        <p className="text-lg font-semibold">{user.address}</p>
                                    </div>
                                )
                            )}
                        </CardContent>
                    </Card>

                    {user.guardian && (user.guardian.emergency_contact_name || user.guardian.emergency_contact_phone) && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Emergency Contact</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4">
                                {user.guardian.emergency_contact_name && (
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Name</p>
                                        <p className="text-lg font-semibold">{user.guardian.emergency_contact_name}</p>
                                    </div>
                                )}
                                {user.guardian.emergency_contact_phone && (
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Phone</p>
                                        <p className="text-lg font-semibold">{user.guardian.emergency_contact_phone}</p>
                                    </div>
                                )}
                                {user.guardian.emergency_contact_relationship && (
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Relationship</p>
                                        <p className="text-lg font-semibold">{user.guardian.emergency_contact_relationship}</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    )}

                    {user.roles.length > 0 && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Roles</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4">
                                <div className="flex flex-wrap gap-1">
                                    {user.roles.map((role) => (
                                        <Badge key={role.id} variant="secondary">
                                            {role.name}
                                        </Badge>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

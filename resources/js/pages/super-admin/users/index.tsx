import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { UsersTable } from '@/pages/super-admin/users/users-table';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';

// From the controller, the User model has these properties
export type User = {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
    roles: { name: string }[];
};

// The controller returns a paginated response
interface PaginatedUsers {
    current_page: number;
    data: User[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: { url: string | null; label: string; active: boolean }[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

interface Props {
    users: PaginatedUsers;
    filters: {
        search: string | null;
        role: string | null;
    };
}

export default function UsersIndex({ users, filters }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Users', href: '/super-admin/users' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Users" />
            <div className="px-4 py-6">
                <div className="mb-4 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Users Index</h1>
                    <Link href={route('super-admin.users.create')}>
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Create User
                        </Button>
                    </Link>
                </div>
                <UsersTable users={users.data} filters={filters} />
            </div>
        </AppLayout>
    );
}

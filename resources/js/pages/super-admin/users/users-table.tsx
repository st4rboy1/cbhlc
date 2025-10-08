import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { type User } from './index';

// Define the props for the UsersTable component
type UsersTableProps = {
    users: User[];
};

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function getRoleVariant(role: string): 'default' | 'secondary' | 'outline' {
    switch (role) {
        case 'super_admin':
            return 'default';
        case 'administrator':
        case 'registrar':
            return 'secondary';
        default:
            return 'outline';
    }
}

function formatRoleName(role: string) {
    return role
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

export function UsersTable({ users }: UsersTableProps) {
    return (
        <Card className="overflow-hidden">
            <div className="overflow-x-auto">
                <table className="w-full">
                    <thead className="border-b bg-muted/50">
                        <tr>
                            <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">ID</th>
                            <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Name</th>
                            <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Email</th>
                            <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Role</th>
                            <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Status</th>
                            <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Created</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {users.map((user) => (
                            <tr key={user.id} className="transition-colors hover:bg-muted/30">
                                <td className="px-6 py-4 font-mono text-sm text-muted-foreground">{user.id}</td>
                                <td className="px-6 py-4 text-sm font-medium">{user.name}</td>
                                <td className="px-6 py-4 text-sm text-muted-foreground">{user.email}</td>
                                <td className="px-6 py-4">
                                    <div className="flex flex-wrap gap-1">
                                        {user.roles.map((role) => (
                                            <Badge key={role.name} variant={getRoleVariant(role.name)} className="text-xs">
                                                {formatRoleName(role.name)}
                                            </Badge>
                                        ))}
                                    </div>
                                </td>
                                <td className="px-6 py-4">
                                    {user.email_verified_at ? (
                                        <Badge variant="default" className="text-xs">
                                            Verified
                                        </Badge>
                                    ) : (
                                        <Badge variant="outline" className="text-xs">
                                            Unverified
                                        </Badge>
                                    )}
                                </td>
                                <td className="px-6 py-4 text-sm text-muted-foreground">{formatDate(user.created_at)}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </Card>
    );
}

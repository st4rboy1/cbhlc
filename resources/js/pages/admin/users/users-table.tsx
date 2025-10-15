import { useState } from 'react';

type User = {
    id: number;
    name: string;
    email: string;
    role: string;
};

type UsersTableProps = {
    users: User[];
};

export function UsersTable({ users }: UsersTableProps) {
    const [sortKey, setSortKey] = useState<keyof User>('id');
    const [sortOrder, setSortOrder] = useState<'asc' | 'desc'>('asc');

    const handleSort = (key: keyof User) => {
        if (sortKey === key) {
            setSortOrder(sortOrder === 'asc' ? 'desc' : 'asc');
        } else {
            setSortKey(key);
            setSortOrder('asc');
        }
    };

    const sortedUsers = [...users].sort((a, b) => {
        const aValue = a[sortKey];
        const bValue = b[sortKey];

        if (aValue < bValue) return sortOrder === 'asc' ? -1 : 1;
        if (aValue > bValue) return sortOrder === 'asc' ? 1 : -1;
        return 0;
    });

    return (
        <div className="overflow-hidden rounded-lg border border-border">
            <table className="w-full">
                <thead>
                    <tr className="border-b border-border bg-muted/50">
                        <th
                            className="cursor-pointer px-4 py-3 text-left text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                            onClick={() => handleSort('id')}
                        >
                            ID {sortKey === 'id' && (sortOrder === 'asc' ? '↑' : '↓')}
                        </th>
                        <th
                            className="cursor-pointer px-4 py-3 text-left text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                            onClick={() => handleSort('name')}
                        >
                            Name {sortKey === 'name' && (sortOrder === 'asc' ? '↑' : '↓')}
                        </th>
                        <th
                            className="cursor-pointer px-4 py-3 text-left text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                            onClick={() => handleSort('email')}
                        >
                            Email {sortKey === 'email' && (sortOrder === 'asc' ? '↑' : '↓')}
                        </th>
                        <th
                            className="cursor-pointer px-4 py-3 text-left text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                            onClick={() => handleSort('role')}
                        >
                            Role {sortKey === 'role' && (sortOrder === 'asc' ? '↑' : '↓')}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {sortedUsers.map((user, index) => (
                        <tr
                            key={user.id}
                            className={`border-b border-border transition-colors last:border-0 hover:bg-muted/30 ${
                                index % 2 === 0 ? 'bg-background' : 'bg-muted/10'
                            }`}
                        >
                            <td className="px-4 py-3 text-sm text-foreground">{user.id}</td>
                            <td className="px-4 py-3 text-sm font-medium text-foreground">{user.name}</td>
                            <td className="px-4 py-3 text-sm text-muted-foreground">{user.email}</td>
                            <td className="px-4 py-3">
                                <span
                                    className={`inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ${
                                        user.role === 'registrar'
                                            ? 'bg-chart-3/10 text-chart-3'
                                            : user.role === 'guardian'
                                              ? 'bg-chart-4/10 text-chart-4'
                                              : 'bg-muted text-muted-foreground'
                                    }`}
                                >
                                    {user.role}
                                </span>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

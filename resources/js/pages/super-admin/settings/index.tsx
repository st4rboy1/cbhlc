import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { ColumnDef, SortingState } from '@tanstack/react-table';
import { Eye, Plus } from 'lucide-react';
import { useEffect, useState } from 'react';

interface Setting {
    id: number;
    key: string;
    value: string | null;
    created_at: string;
    updated_at: string;
}

interface PaginatedSettings {
    data: Setting[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    settings: PaginatedSettings & {
        filters: {
            sort_by?: string;
            sort_direction?: string;
        };
    };
}

export default function SettingsIndex({ settings }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Settings', href: '/super-admin/settings' },
    ];

    const [sorting, setSorting] = useState<SortingState>(
        settings.filters?.sort_by && settings.filters?.sort_direction
            ? [{ id: settings.filters.sort_by, desc: settings.filters.sort_direction === 'desc' }]
            : [],
    );

    useEffect(() => {
        const handler = setTimeout(() => {
            router.get(
                route('super-admin.settings.index'),
                {
                    ...settings.filters,
                    sort_by: sorting.length > 0 ? sorting[0].id : undefined,
                    sort_direction: sorting.length > 0 ? (sorting[0].desc ? 'desc' : 'asc') : undefined,
                },
                { preserveState: true, replace: true },
            );
        }, 300);

        return () => clearTimeout(handler);
    }, [sorting]);

    const columns: ColumnDef<Setting>[] = [
        {
            accessorKey: 'key',
            header: 'Setting Key',
            cell: ({ row }) => <span className="font-mono text-sm font-medium">{row.original.key}</span>,
        },
        {
            accessorKey: 'value',
            header: 'Value',
            cell: ({ row }) => (
                <div className="max-w-md truncate">
                    {row.original.value ? (
                        <span className="text-sm">{row.original.value}</span>
                    ) : (
                        <Badge variant="outline" className="text-muted-foreground">
                            No value
                        </Badge>
                    )}
                </div>
            ),
        },
        {
            accessorKey: 'updated_at',
            header: 'Last Updated',
            cell: ({ row }) => new Date(row.original.updated_at).toLocaleDateString(),
        },
        {
            id: 'actions',
            header: 'Actions',
            cell: ({ row }) => (
                <div className="flex gap-2">
                    <Button size="sm" variant="outline" onClick={() => router.visit(`/super-admin/settings/${row.original.id}`)}>
                        <Eye className="mr-1 h-3 w-3" />
                        View
                    </Button>
                    <Button size="sm" variant="outline" onClick={() => router.visit(`/super-admin/settings/${row.original.id}/edit`)}>
                        Edit
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Settings" />
            <div className="px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">System Settings</h1>
                        <p className="mt-1 text-sm text-muted-foreground">Manage application configuration and settings</p>
                    </div>
                    <Link href="/super-admin/settings/create">
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Create Setting
                        </Button>
                    </Link>
                </div>
                <DataTable columns={columns} data={settings.data} sorting={sorting} onSortingChange={setSorting} />
            </div>
        </AppLayout>
    );
}

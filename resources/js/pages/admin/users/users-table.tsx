'use client';

import {
    ColumnDef,
    ColumnFiltersState,
    flexRender,
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    SortingState,
    useReactTable,
    VisibilityState,
} from '@tanstack/react-table';
import { ArrowUpDown, ChevronDown, MoreHorizontal } from 'lucide-react';
import * as React from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Link, router } from '@inertiajs/react';
import { toast } from 'sonner';
import { useDebounce } from 'use-debounce';
import { type User } from './index';

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

function ActionsCell({ user }: { user: User }) {
    const [deleteDialogOpen, setDeleteDialogOpen] = React.useState(false);

    const handleDelete = () => {
        router.delete(`/admin/users/${user.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('User deleted successfully');
                setDeleteDialogOpen(false);
            },
            onError: () => {
                toast.error('Failed to delete user. Please try again.');
            },
        });
    };

    return (
        <>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="ghost" className="h-8 w-8 p-0">
                        <span className="sr-only">Open menu</span>
                        <MoreHorizontal className="h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuLabel>Actions</DropdownMenuLabel>
                    <DropdownMenuItem onClick={() => navigator.clipboard.writeText(user.id.toString())}>Copy User ID</DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem asChild>
                        <Link href={`/admin/users/${user.id}`}>View User</Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                        <Link href={`/admin/users/${user.id}/edit`}>Edit User</Link>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem onClick={() => setDeleteDialogOpen(true)}>Delete User</DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>

            <ConfirmationDialog
                open={deleteDialogOpen}
                onOpenChange={setDeleteDialogOpen}
                onConfirm={handleDelete}
                title="Delete User?"
                description="Are you sure you want to delete this user? This action cannot be undone."
                confirmText="Delete"
                variant="destructive"
            />
        </>
    );
}

export const columns: ColumnDef<User>[] = [
    {
        id: 'select',
        header: ({ table }) => (
            <Checkbox
                checked={table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate')}
                onCheckedChange={(value) => table.toggleAllPageRowsSelected(!!value)}
                aria-label="Select all"
            />
        ),
        cell: ({ row }) => (
            <Checkbox checked={row.getIsSelected()} onCheckedChange={(value) => row.toggleSelected(!!value)} aria-label="Select row" />
        ),
        enableSorting: false,
        enableHiding: false,
    },
    {
        accessorKey: 'id',
        header: 'ID',
    },
    {
        accessorKey: 'name',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Name
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
    },
    {
        accessorKey: 'email',
        header: 'Email',
    },
    {
        accessorKey: 'roles',
        header: 'Role',
        cell: ({ row }) => {
            const roles = row.getValue('roles') as { name: string }[];
            return (
                <div className="flex flex-wrap gap-1">
                    {roles.map((role) => (
                        <Badge key={role.name} variant={getRoleVariant(role.name)} className="text-xs">
                            {formatRoleName(role.name)}
                        </Badge>
                    ))}
                </div>
            );
        },
    },
    {
        accessorKey: 'email_verified_at',
        header: 'Status',
        cell: ({ row }) => {
            const email_verified_at = row.getValue('email_verified_at');
            return (
                <>
                    {email_verified_at ? (
                        <Badge variant="default" className="text-xs">
                            Verified
                        </Badge>
                    ) : (
                        <Badge variant="outline" className="text-xs">
                            Unverified
                        </Badge>
                    )}
                </>
            );
        },
    },
    {
        accessorKey: 'created_at',
        header: 'Created At',
        cell: ({ row }) => {
            const created_at = row.getValue('created_at') as string;
            return formatDate(created_at);
        },
    },
    {
        id: 'actions',
        cell: ({ row }) => <ActionsCell user={row.original} />,
    },
];

interface UsersTableProps {
    users: User[];
    filters: {
        search: string | null;
    };
}

export function UsersTable({ users, filters }: UsersTableProps) {
    const [sorting, setSorting] = React.useState<SortingState>([]);
    const [columnFilters, setColumnFilters] = React.useState<ColumnFiltersState>([]);
    const [columnVisibility, setColumnVisibility] = React.useState<VisibilityState>({});
    const [rowSelection, setRowSelection] = React.useState({});

    const table = useReactTable({
        data: users,
        columns,
        onSortingChange: setSorting,
        onColumnFiltersChange: setColumnFilters,
        getCoreRowModel: getCoreRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
        getSortedRowModel: getSortedRowModel(),
        getFilteredRowModel: getFilteredRowModel(),
        onColumnVisibilityChange: setColumnVisibility,
        onRowSelectionChange: setRowSelection,
        state: {
            sorting,
            columnFilters,
            columnVisibility,
            rowSelection,
        },
    });

    const searchValue = (table.getColumn('name')?.getFilterValue() as string) ?? '';
    const [debouncedSearchTerm] = useDebounce(searchValue, 500);

    React.useEffect(() => {
        if (debouncedSearchTerm !== (filters.search || '')) {
            router.get(
                '/admin/users',
                { search: debouncedSearchTerm },
                {
                    preserveState: true,
                    replace: true,
                },
            );
        }
    }, [debouncedSearchTerm, filters.search]);

    return (
        <div className="w-full">
            <div className="flex items-center py-4">
                <Input
                    placeholder="Filter by name..."
                    value={searchValue}
                    onChange={(event) => table.getColumn('name')?.setFilterValue(event.target.value)}
                    className="max-w-sm"
                />
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="outline" className="ml-auto">
                            Columns <ChevronDown className="ml-2 h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        {table
                            .getAllColumns()
                            .filter((column) => column.getCanHide())
                            .map((column) => {
                                return (
                                    <DropdownMenuCheckboxItem
                                        key={column.id}
                                        className="capitalize"
                                        checked={column.getIsVisible()}
                                        onCheckedChange={(value) => column.toggleVisibility(!!value)}
                                    >
                                        {column.id}
                                    </DropdownMenuCheckboxItem>
                                );
                            })}
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
            <div className="overflow-hidden rounded-md border">
                <Table>
                    <TableHeader>
                        {table.getHeaderGroups().map((headerGroup) => (
                            <TableRow key={headerGroup.id}>
                                {headerGroup.headers.map((header) => {
                                    return (
                                        <TableHead key={header.id}>
                                            {header.isPlaceholder ? null : flexRender(header.column.columnDef.header, header.getContext())}
                                        </TableHead>
                                    );
                                })}
                            </TableRow>
                        ))}
                    </TableHeader>
                    <TableBody>
                        {table.getRowModel().rows?.length ? (
                            table.getRowModel().rows.map((row) => (
                                <TableRow key={row.id} data-state={row.getIsSelected() && 'selected'}>
                                    {row.getVisibleCells().map((cell) => (
                                        <TableCell key={cell.id}>{flexRender(cell.column.columnDef.cell, cell.getContext())}</TableCell>
                                    ))}
                                </TableRow>
                            ))
                        ) : (
                            <TableRow>
                                <TableCell colSpan={columns.length} className="h-24 text-center">
                                    No results.
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </div>
            <div className="flex items-center justify-end space-x-2 py-4">
                <div className="flex-1 text-sm text-muted-foreground">
                    {table.getFilteredSelectedRowModel().rows.length} of {table.getFilteredRowModel().rows.length} row(s) selected.
                </div>
                <div className="space-x-2">
                    <Button variant="outline" size="sm" onClick={() => table.previousPage()} disabled={!table.getCanPreviousPage()}>
                        Previous
                    </Button>
                    <Button variant="outline" size="sm" onClick={() => table.nextPage()} disabled={!table.getCanNextPage()}>
                        Next
                    </Button>
                </div>
            </div>
        </div>
    );
}

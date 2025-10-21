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
import { ChevronDown, MoreHorizontal } from 'lucide-react';
import * as React from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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

interface Enrollment {
    id: number;
    student: { first_name: string; last_name: string; student_id: string };
    guardian: { name: string };
    school_year: string;
    quarter: string;
    grade_level: string;
    status: string;
    net_amount_cents: number;
    amount_paid_cents: number;
    balance_cents: number;
    payment_status: string;
}

interface EnrollmentsTableProps {
    enrollments: Enrollment[];
}

function formatCurrency(cents: number) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(cents / 100);
}

function getStatusVariant(status: string): 'default' | 'secondary' | 'outline' | 'destructive' {
    switch (status) {
        case 'completed':
            return 'default';
        case 'enrolled':
            return 'secondary';
        case 'pending':
            return 'outline';
        case 'rejected':
            return 'destructive';
        default:
            return 'outline';
    }
}

function getPaymentStatusVariant(status: string): 'default' | 'secondary' | 'outline' | 'destructive' {
    switch (status) {
        case 'paid':
            return 'default';
        case 'partial':
            return 'secondary';
        case 'pending':
            return 'outline';
        case 'overdue':
            return 'destructive';
        default:
            return 'outline';
    }
}

function formatStatusName(status: string) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

const columns: ColumnDef<Enrollment>[] = [
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
        header: 'Enrollment ID',
    },
    {
        accessorFn: (row) => `${row.student.first_name} ${row.student.last_name}`,
        header: 'Student',
        cell: ({ row }) => {
            const student = row.original.student;
            return (
                <div>
                    <div>{`${student.first_name} ${student.last_name}`}</div>
                    <div className="text-xs text-muted-foreground">{student.student_id}</div>
                </div>
            );
        },
    },
    {
        accessorFn: (row) => row.guardian.name,
        header: 'Guardian',
        cell: ({ row }) => {
            const guardian = row.original.guardian;
            return <div>{guardian.name}</div>;
        },
    },
    {
        accessorKey: 'school_year',
        header: 'School Year',
    },
    {
        accessorKey: 'grade_level',
        header: 'Grade Level',
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: ({ row }) => (
            <Badge variant={getStatusVariant(row.getValue('status'))} className="text-xs">
                {formatStatusName(row.getValue('status'))}
            </Badge>
        ),
    },
    {
        accessorKey: 'net_amount_cents',
        header: () => <div className="text-right">Total Amount</div>,
        cell: ({ row }) => {
            const amount = parseFloat(row.getValue('net_amount_cents'));
            const formatted = formatCurrency(amount);

            return <div className="text-right font-medium">{formatted}</div>;
        },
    },
    {
        accessorKey: 'balance_cents',
        header: () => <div className="text-right">Balance</div>,
        cell: ({ row }) => {
            const amount = parseFloat(row.getValue('balance_cents'));
            if (amount === 0) {
                return <div className="text-right font-medium text-muted-foreground">—</div>;
            }
            const formatted = formatCurrency(amount);

            return <div className="text-right font-medium text-destructive">{formatted}</div>;
        },
    },
    {
        accessorKey: 'payment_status',
        header: 'Payment',
        cell: ({ row }) => (
            <Badge variant={getPaymentStatusVariant(row.getValue('payment_status'))} className="text-xs">
                {formatStatusName(row.getValue('payment_status'))}
            </Badge>
        ),
    },
    {
        id: 'actions',
        cell: ({ row }) => {
            const enrollment = row.original;

            return (
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="ghost" className="h-8 w-8 p-0">
                            <span className="sr-only">Open menu</span>
                            <MoreHorizontal className="h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuLabel>Actions</DropdownMenuLabel>
                        <DropdownMenuItem onClick={() => navigator.clipboard.writeText(enrollment.id.toString())}>
                            Copy Enrollment ID
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem>View Enrollment</DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            );
        },
    },
];

export default function EnrollmentsTable({ enrollments }: EnrollmentsTableProps) {
    const [sorting, setSorting] = React.useState<SortingState>([]);
    const [columnFilters, setColumnFilters] = React.useState<ColumnFiltersState>([]);
    const [columnVisibility, setColumnVisibility] = React.useState<VisibilityState>({});
    const [rowSelection, setRowSelection] = React.useState({});
    const [globalFilter, setGlobalFilter] = React.useState('');

    const table = useReactTable({
        data: enrollments,
        columns,
        onSortingChange: setSorting,
        onColumnFiltersChange: setColumnFilters,
        getCoreRowModel: getCoreRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
        getSortedRowModel: getSortedRowModel(),
        getFilteredRowModel: getFilteredRowModel(),
        onColumnVisibilityChange: setColumnVisibility,
        onRowSelectionChange: setRowSelection,
        onGlobalFilterChange: setGlobalFilter,
        state: {
            sorting,
            columnFilters,
            columnVisibility,
            rowSelection,
            globalFilter,
        },
    });

    return (
        <div className="w-full">
            <div className="flex items-center py-4">
                <Input
                    placeholder="Filter by student name..."
                    value={globalFilter ?? ''}
                    onChange={(event) => setGlobalFilter(event.target.value)}
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

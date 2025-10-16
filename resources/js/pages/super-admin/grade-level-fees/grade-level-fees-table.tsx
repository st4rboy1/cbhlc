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
import { ArrowUpDown, ChevronDown, Copy, Edit, MoreHorizontal, Trash } from 'lucide-react';
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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { formatCurrency } from '@/lib/utils';
import { router } from '@inertiajs/react';
import { useDebounce } from 'use-debounce';

export type GradeLevelFee = {
    id: number;
    gradeLevel: string;
    schoolYear: string;
    tuitionFee: number;
    miscellaneousFee: number;
    otherFees: number;
    totalAmount: number;
    paymentTerms: string;
    isActive: boolean;
};

export const columns: ColumnDef<GradeLevelFee>[] = [
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
        accessorKey: 'gradeLevel',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Grade Level
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
    },
    {
        accessorKey: 'schoolYear',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    School Year
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
    },
    {
        accessorKey: 'tuitionFee',
        header: () => <div className="text-right">Tuition Fee</div>,
        cell: ({ row }) => {
            const amount = parseFloat(row.getValue('tuitionFee'));
            return <div className="text-right font-medium">{formatCurrency(amount)}</div>;
        },
    },
    {
        accessorKey: 'miscellaneousFee',
        header: () => <div className="text-right">Misc. Fee</div>,
        cell: ({ row }) => {
            const amount = parseFloat(row.getValue('miscellaneousFee'));
            return <div className="text-right font-medium">{formatCurrency(amount)}</div>;
        },
    },
    {
        accessorKey: 'otherFees',
        header: () => <div className="text-right">Other Fees</div>,
        cell: ({ row }) => {
            const amount = parseFloat(row.getValue('otherFees'));
            return <div className="text-right font-medium">{formatCurrency(amount)}</div>;
        },
    },
    {
        accessorKey: 'totalAmount',
        header: () => <div className="text-right">Total</div>,
        cell: ({ row }) => {
            const amount = parseFloat(row.getValue('totalAmount'));
            return <div className="text-right font-semibold">{formatCurrency(amount)}</div>;
        },
    },
    {
        accessorKey: 'isActive',
        header: 'Status',
        cell: ({ row }) => (
            <Badge variant={row.getValue('isActive') ? 'default' : 'secondary'}>{row.getValue('isActive') ? 'Active' : 'Inactive'}</Badge>
        ),
    },
    {
        id: 'actions',
        cell: ({ row }) => {
            const fee = row.original;

            const handleDelete = () => {
                if (confirm('Are you sure you want to delete this fee structure?')) {
                    router.delete(`/super-admin/grade-level-fees/${fee.id}`);
                }
            };

            const handleDuplicate = () => {
                const newSchoolYear = prompt('Enter the school year to duplicate to (e.g., 2025-2026):');
                if (newSchoolYear) {
                    router.post(`/super-admin/grade-level-fees/${fee.id}/duplicate`, {
                        school_year: newSchoolYear,
                    });
                }
            };

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
                        <DropdownMenuItem onClick={() => router.visit(`/super-admin/grade-level-fees/${fee.id}/edit`)}>
                            <Edit className="mr-2 h-4 w-4" />
                            Edit
                        </DropdownMenuItem>
                        <DropdownMenuItem onClick={handleDuplicate}>
                            <Copy className="mr-2 h-4 w-4" />
                            Duplicate
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem onClick={handleDelete} className="text-destructive">
                            <Trash className="mr-2 h-4 w-4" />
                            Delete
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            );
        },
    },
];

interface GradeLevelFeesTableProps {
    fees: GradeLevelFee[];
    filters: {
        search: string | null;
        school_year: string | null;
        active: string | null;
    };
}

export function GradeLevelFeesTable({ fees, filters }: GradeLevelFeesTableProps) {
    const [sorting, setSorting] = React.useState<SortingState>([]);
    const [columnFilters, setColumnFilters] = React.useState<ColumnFiltersState>([]);
    const [columnVisibility, setColumnVisibility] = React.useState<VisibilityState>({});
    const [rowSelection, setRowSelection] = React.useState({});

    const table = useReactTable({
        data: fees,
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

    const searchValue = (table.getColumn('gradeLevel')?.getFilterValue() as string) ?? '';
    const [debouncedSearchTerm] = useDebounce(searchValue, 500);

    const [schoolYear, setSchoolYear] = React.useState(filters.school_year || '');
    const [activeFilter, setActiveFilter] = React.useState(filters.active || 'all');

    React.useEffect(() => {
        if (debouncedSearchTerm !== (filters.search || '')) {
            router.get(
                '/super-admin/grade-level-fees',
                {
                    search: debouncedSearchTerm || undefined,
                    school_year: schoolYear || undefined,
                    active: activeFilter !== 'all' ? activeFilter : undefined,
                },
                {
                    preserveState: true,
                    replace: true,
                    preserveScroll: true,
                },
            );
        }
    }, [debouncedSearchTerm, filters.search]);

    const handleFilterChange = () => {
        router.get(
            '/super-admin/grade-level-fees',
            {
                search: debouncedSearchTerm || undefined,
                school_year: schoolYear || undefined,
                active: activeFilter !== 'all' ? activeFilter : undefined,
            },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
            },
        );
    };

    return (
        <div className="w-full">
            <div className="flex items-center gap-4 py-4">
                <Input
                    placeholder="Search grade level..."
                    value={searchValue}
                    onChange={(event) => table.getColumn('gradeLevel')?.setFilterValue(event.target.value)}
                    className="max-w-sm"
                />
                <Input
                    placeholder="School year (e.g., 2024-2025)"
                    value={schoolYear}
                    onChange={(e) => setSchoolYear(e.target.value)}
                    onBlur={handleFilterChange}
                    onKeyDown={(e) => e.key === 'Enter' && handleFilterChange()}
                    className="max-w-sm"
                />
                <Select
                    value={activeFilter}
                    onValueChange={(value) => {
                        setActiveFilter(value);
                        setTimeout(handleFilterChange, 0);
                    }}
                >
                    <SelectTrigger className="w-[180px]">
                        <SelectValue placeholder="Filter by status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All</SelectItem>
                        <SelectItem value="true">Active</SelectItem>
                        <SelectItem value="false">Inactive</SelectItem>
                    </SelectContent>
                </Select>
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
                                    No grade level fees found.
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

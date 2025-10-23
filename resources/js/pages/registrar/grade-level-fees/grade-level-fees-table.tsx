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
import { ArrowUpDown, Copy, Edit, MoreHorizontal, Trash } from 'lucide-react';
import * as React from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
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
        accessorKey: 'gradeLevel',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Grade Level
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => <div className="font-medium">{row.getValue('gradeLevel')}</div>,
    },
    {
        accessorKey: 'schoolYear',
        header: 'School Year',
        cell: ({ row }) => <div>{row.getValue('schoolYear')}</div>,
    },
    {
        accessorKey: 'tuitionFee',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Tuition Fee
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => <div>{formatCurrency(row.getValue('tuitionFee'))}</div>,
    },
    {
        accessorKey: 'miscellaneousFee',
        header: 'Misc. Fee',
        cell: ({ row }) => <div>{formatCurrency(row.getValue('miscellaneousFee'))}</div>,
    },
    {
        accessorKey: 'otherFees',
        header: 'Other Fees',
        cell: ({ row }) => <div>{formatCurrency(row.getValue('otherFees'))}</div>,
    },
    {
        accessorKey: 'totalAmount',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Total
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => <div className="font-semibold">{formatCurrency(row.getValue('totalAmount'))}</div>,
    },
    {
        accessorKey: 'isActive',
        header: 'Status',
        cell: ({ row }) => {
            const isActive = row.getValue('isActive') as boolean;
            return <Badge variant={isActive ? 'default' : 'secondary'}>{isActive ? 'Active' : 'Inactive'}</Badge>;
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const fee = row.original;

            const handleDelete = () => {
                if (confirm('Are you sure you want to delete this fee structure?')) {
                    router.delete(`/registrar/grade-level-fees/${fee.id}`);
                }
            };

            const handleDuplicate = () => {
                const newSchoolYear = prompt('Enter the school year to duplicate to (e.g., 2025-2026):');
                if (newSchoolYear) {
                    router.post(`/registrar/grade-level-fees/${fee.id}/duplicate`, {
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
                        <DropdownMenuItem onClick={() => router.visit(`/registrar/grade-level-fees/${fee.id}/edit`)}>
                            <Edit className="mr-2 h-4 w-4" />
                            Edit
                        </DropdownMenuItem>
                        <DropdownMenuItem onClick={handleDuplicate}>
                            <Copy className="mr-2 h-4 w-4" />
                            Duplicate
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem onClick={handleDelete} className="text-red-600">
                            <Trash className="mr-2 h-4 w-4" />
                            Delete
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            );
        },
    },
];

interface SchoolYear {
    id: number;
    name: string;
    status: string;
    is_active: boolean;
}

interface GradeLevelFeesTableProps {
    fees: GradeLevelFee[];
    filters: {
        search: string | null;
        school_year: string | null;
        active: string | null;
    };
    schoolYears: SchoolYear[];
}

export function GradeLevelFeesTable({ fees, filters, schoolYears }: GradeLevelFeesTableProps) {
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

    const [gradeLevelFilter, setGradeLevelFilter] = React.useState(filters.search || '');
    const [schoolYear, setSchoolYear] = React.useState(filters.school_year || '');
    const [activeFilter, setActiveFilter] = React.useState(filters.active || '');

    const applyFilters = (gradeLevel?: string, year?: string, active?: string) => {
        router.get(
            '/registrar/grade-level-fees',
            {
                search: (gradeLevel ?? gradeLevelFilter) || undefined,
                school_year: (year ?? schoolYear) || undefined,
                active: (active ?? activeFilter) || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    return (
        <div className="w-full">
            <div className="flex items-center gap-4 py-4">
                <Input
                    placeholder="Search grade level..."
                    value={gradeLevelFilter}
                    onChange={(e) => setGradeLevelFilter(e.target.value)}
                    onBlur={(e) => applyFilters(e.target.value, undefined, undefined)}
                    onKeyDown={(e) => e.key === 'Enter' && applyFilters(gradeLevelFilter, undefined, undefined)}
                    className="max-w-sm"
                />
                <Select
                    value={schoolYear || 'all'}
                    onValueChange={(value) => {
                        const newValue = value === 'all' ? '' : value;
                        setSchoolYear(newValue);
                        applyFilters(undefined, newValue, undefined);
                    }}
                >
                    <SelectTrigger className="w-[200px]">
                        <SelectValue placeholder="Filter by school year" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All School Years</SelectItem>
                        {schoolYears.map((sy) => (
                            <SelectItem key={sy.id} value={sy.name}>
                                {sy.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Select
                    value={activeFilter || 'all'}
                    onValueChange={(value) => {
                        const newValue = value === 'all' ? '' : value;
                        setActiveFilter(newValue);
                        applyFilters(undefined, undefined, newValue);
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
            </div>
            <div className="rounded-md border">
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
                <div className="flex-1 text-sm text-muted-foreground">{table.getFilteredRowModel().rows.length} row(s) total.</div>
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

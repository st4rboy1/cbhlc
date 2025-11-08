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

import { EnrollmentStatusBadge, PaymentStatusBadge } from '@/components/status-badges';
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

interface Student {
    id: number;
    student_id: string;
    first_name: string;
    last_name: string;
    middle_name: string;
    birthdate: string;
    gender: string;
    grade_level: string;
    contact_number: string;
    address: string;
    enrollments: {
        enrollment_id: string;
        school_year: string;
        grade_level: string;
        status: string;
        payment_status: string;
        balance: number;
        net_amount: number;
    }[];
}

interface StudentsTableProps {
    students: Student[];
}

function formatCurrency(amount: number) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount);
}

export function calculateAge(birthdate: string) {
    const birth = new Date(birthdate);
    const today = new Date();
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    return age;
}

function ActionsCell({ student }: { student: Student }) {
    const [deleteDialogOpen, setDeleteDialogOpen] = React.useState(false);

    const handleDelete = () => {
        router.delete(`/registrar/students/${student.id}`, {
            onSuccess: () => {
                toast.success('Student deleted successfully');
                setDeleteDialogOpen(false);
            },
            onError: () => {
                toast.error('Failed to delete student. Student may have enrollments.');
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
                    <DropdownMenuItem onClick={() => navigator.clipboard.writeText(student.student_id)}>Copy Student ID</DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem asChild>
                        <Link href={`/registrar/students/${student.id}`}>View Student</Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                        <Link href={`/registrar/students/${student.id}/edit`}>Edit Student</Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                        <Link href={`/registrar/students/${student.id}/enrollments`}>View Enrollments</Link>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem onClick={() => setDeleteDialogOpen(true)}>Delete Student</DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>

            <ConfirmationDialog
                open={deleteDialogOpen}
                onOpenChange={setDeleteDialogOpen}
                onConfirm={handleDelete}
                title="Delete Student?"
                description="Are you sure you want to delete this student? This action cannot be undone."
                confirmText="Delete"
                variant="destructive"
            />
        </>
    );
}

export const columns: ColumnDef<Student>[] = [
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
        accessorKey: 'student_id',
        header: 'Student ID',
    },
    {
        accessorKey: 'name',
        header: 'Name',
        accessorFn: (row) => `${row.first_name} ${row.middle_name ? row.middle_name.charAt(0) + '. ' : ''}${row.last_name}`,
        cell: ({ row }) => {
            const student = row.original;
            return (
                <div>
                    <div>{`${student.first_name} ${student.middle_name ? student.middle_name.charAt(0) + '. ' : ''}${student.last_name}`}</div>
                    <div className="text-xs text-muted-foreground">{student.address}</div>
                </div>
            );
        },
        filterFn: (row, id, value) => {
            const student = row.original;
            const fullName =
                `${student.first_name} ${student.middle_name ? student.middle_name.charAt(0) + '. ' : ''}${student.last_name}`.toLowerCase();
            return fullName.includes(value.toLowerCase());
        },
    },
    {
        accessorKey: 'birthdate',
        header: 'Age',
        cell: ({ row }) => {
            const birthdate = row.getValue('birthdate') as string;
            return <div>{calculateAge(birthdate)}</div>;
        },
    },
    {
        accessorKey: 'gender',
        header: 'Gender',
    },
    {
        accessorKey: 'grade_level',
        header: 'Current Grade',
        cell: ({ row }) => {
            const gradeLevel = row.getValue('grade_level') as string;
            return <div>{gradeLevel || <span className="text-muted-foreground">Not assigned</span>}</div>;
        },
    },
    {
        accessorKey: 'contact_number',
        header: 'Contact',
    },
    {
        accessorKey: 'enrollments',
        header: 'Latest Enrollment',
        cell: ({ row }) => {
            const enrollments = row.getValue('enrollments') as {
                enrollment_id: string;
                school_year: string;
                grade_level: string;
                status: string;
                payment_status: string;
            }[];
            const latestEnrollment = enrollments[0];
            return (
                <div>
                    {latestEnrollment ? (
                        <div className="flex flex-col gap-2">
                            <div className="text-xs text-muted-foreground">
                                {latestEnrollment.enrollment_id} • {latestEnrollment.school_year}
                            </div>
                            <div className="flex gap-2">
                                <EnrollmentStatusBadge status={latestEnrollment.status} />
                                <PaymentStatusBadge status={latestEnrollment.payment_status} />
                            </div>
                        </div>
                    ) : (
                        <span className="text-xs text-muted-foreground">No enrollments</span>
                    )}
                </div>
            );
        },
    },
    {
        accessorKey: 'balance',
        header: () => <div className="text-right">Balance</div>,
        cell: ({ row }) => {
            const latestEnrollment = row.original.enrollments[0];
            const amount = latestEnrollment ? latestEnrollment.balance : 0;

            if (!Number.isFinite(amount)) {
                return <div className="text-right font-medium">N/A</div>;
            }

            const formatted = formatCurrency(amount);

            return <div className="text-right font-medium">{formatted}</div>;
        },
    },
    {
        id: 'actions',
        cell: ({ row }) => <ActionsCell student={row.original} />,
    },
];

export function StudentsTable({ students }: StudentsTableProps) {
    const [sorting, setSorting] = React.useState<SortingState>([]);
    const [columnFilters, setColumnFilters] = React.useState<ColumnFiltersState>([]);
    const [columnVisibility, setColumnVisibility] = React.useState<VisibilityState>({});
    const [rowSelection, setRowSelection] = React.useState({});

    const table = useReactTable({
        data: students,
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

    return (
        <div className="w-full">
            <div className="flex items-center py-4">
                <Input
                    placeholder="Filter by name..."
                    value={(table.getColumn('name')?.getFilterValue() as string) ?? ''}
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

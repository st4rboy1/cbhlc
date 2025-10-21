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
import { router } from '@inertiajs/react';

// Dialog components for the modal
import InputError from '@/components/input-error';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

interface Enrollment {
    id: number;
    student: { first_name: string; last_name: string; student_id: string };
    guardian: { first_name: string; last_name: string; user?: { email: string } };
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

export function formatCurrency(cents: number) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(cents / 100);
}

export function getStatusVariant(status: string): 'default' | 'secondary' | 'outline' | 'destructive' {
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

export function getPaymentStatusVariant(status: string): 'default' | 'secondary' | 'outline' | 'destructive' {
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

export function formatStatusName(status: string) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

// Define a type for the functions that will be passed to the columns
interface ColumnActionProps {
    onApproveClick: (enrollment: Enrollment) => void;
    onRejectClick: (enrollment: Enrollment) => void;
    onUpdatePaymentStatusClick: (enrollment: Enrollment) => void;
}

export const createColumns = ({ onApproveClick, onRejectClick, onUpdatePaymentStatusClick }: ColumnActionProps): ColumnDef<Enrollment>[] => [
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
        accessorKey: 'student',
        header: 'Student',
        accessorFn: (row) => `${row.student.first_name} ${row.student.last_name}`,
        cell: ({ row }) => {
            const student = row.original.student;
            return (
                <div>
                    <div>{`${student.first_name} ${student.last_name}`}</div>
                    <div className="text-xs text-muted-foreground">{student.student_id}</div>
                </div>
            );
        },
        filterFn: (row, id, value) => {
            const student = row.original.student;
            const fullName = `${student.first_name} ${student.last_name}`.toLowerCase();
            return fullName.includes(value.toLowerCase());
        },
    },
    {
        accessorKey: 'guardian',
        header: 'Guardian',
        cell: ({ row }) => {
            const guardian = row.getValue('guardian') as { first_name: string; last_name: string; user?: { email: string } };
            return <div>{`${guardian.first_name} ${guardian.last_name}`}</div>;
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

            const handleCompleteClick = (enrollment: Enrollment) => {
                if (confirm(`Are you sure you want to complete enrollment ${enrollment.id}?`)) {
                    router.post(`/registrar/enrollments/${enrollment.id}/complete`);
                }
            };

            const handleConfirmPaymentClick = (enrollment: Enrollment) => {
                if (confirm(`Are you sure you want to confirm payment for enrollment ${enrollment.id}?`)) {
                    router.post(`/registrar/enrollments/${enrollment.id}/confirm-payment`);
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
                        <DropdownMenuItem onClick={() => navigator.clipboard.writeText(enrollment.id.toString())}>
                            Copy Enrollment ID
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem onClick={() => (window.location.href = `/registrar/enrollments/${enrollment.id}`)}>
                            View Enrollment
                        </DropdownMenuItem>
                        {enrollment.status === 'pending' && (
                            <DropdownMenuItem onClick={() => onApproveClick(enrollment)}>Approve Enrollment</DropdownMenuItem>
                        )}
                        {enrollment.status === 'pending' && (
                            <DropdownMenuItem onClick={() => onRejectClick(enrollment)}>Reject Enrollment</DropdownMenuItem>
                        )}
                        {enrollment.status === 'approved' && enrollment.payment_status === 'paid' && (
                            <DropdownMenuItem onClick={() => handleCompleteClick(enrollment)}>Complete Enrollment</DropdownMenuItem>
                        )}
                        {enrollment.payment_status === 'pending' && (
                            <DropdownMenuItem onClick={() => handleConfirmPaymentClick(enrollment)}>Confirm Payment</DropdownMenuItem>
                        )}
                        <DropdownMenuItem onClick={() => onUpdatePaymentStatusClick(enrollment)}>Update Payment Status</DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            );
        },
    },
];

export function EnrollmentsTable({ enrollments }: EnrollmentsTableProps) {
    const [sorting, setSorting] = React.useState<SortingState>([]);
    const [columnFilters, setColumnFilters] = React.useState<ColumnFiltersState>([]);
    const [columnVisibility, setColumnVisibility] = React.useState<VisibilityState>({});
    const [rowSelection, setRowSelection] = React.useState({});

    // State for Approve Enrollment Modal
    const [showApproveModal, setShowApproveModal] = React.useState(false);
    const [enrollmentToApprove, setEnrollmentToApprove] = React.useState<Enrollment | null>(null);
    const [approveRemarks, setApproveRemarks] = React.useState('');
    const [approveErrors, setApproveErrors] = React.useState<Record<string, string>>({});

    // State for Reject Enrollment Modal
    const [showRejectModal, setShowRejectModal] = React.useState(false);
    const [enrollmentToReject, setEnrollmentToReject] = React.useState<Enrollment | null>(null);
    const [rejectReason, setRejectReason] = React.useState('');
    const [rejectErrors, setRejectErrors] = React.useState<Record<string, string>>({});

    // State for Update Payment Status Modal
    const [showUpdatePaymentStatusModal, setShowUpdatePaymentStatusModal] = React.useState(false);
    const [enrollmentToUpdatePaymentStatus, setEnrollmentToUpdatePaymentStatus] = React.useState<Enrollment | null>(null);
    const [amountPaid, setAmountPaid] = React.useState('');
    const [paymentStatus, setPaymentStatus] = React.useState('');
    const [updatePaymentRemarks, setUpdatePaymentRemarks] = React.useState('');
    const [updatePaymentErrors, setUpdatePaymentErrors] = React.useState<Record<string, string>>({});

    const handleApproveClick = React.useCallback((enrollment: Enrollment) => {
        setEnrollmentToApprove(enrollment);
        setApproveRemarks(''); // Clear previous remarks
        setApproveErrors({}); // Clear previous errors
        setShowApproveModal(true);
    }, []);

    const handleApproveSubmit = () => {
        if (!enrollmentToApprove) return;

        router.post(
            `/registrar/enrollments/${enrollmentToApprove.id}/approve`,
            { remarks: approveRemarks },
            {
                onSuccess: () => {
                    setShowApproveModal(false);
                    setEnrollmentToApprove(null);
                    setApproveRemarks('');
                    setApproveErrors({});
                },
                onError: (errors) => {
                    setApproveErrors(errors);
                },
            },
        );
    };

    const handleRejectClick = React.useCallback((enrollment: Enrollment) => {
        setEnrollmentToReject(enrollment);
        setRejectReason(''); // Clear previous reason
        setRejectErrors({}); // Clear previous errors
        setShowRejectModal(true);
    }, []);

    const handleRejectSubmit = () => {
        if (!enrollmentToReject) return;

        router.post(
            `/registrar/enrollments/${enrollmentToReject.id}/reject`,
            { reason: rejectReason },
            {
                onSuccess: () => {
                    setShowRejectModal(false);
                    setEnrollmentToReject(null);
                    setRejectReason('');
                    setRejectErrors({});
                },
                onError: (errors) => {
                    setRejectErrors(errors);
                },
            },
        );
    };

    const handleUpdatePaymentStatusClick = React.useCallback((enrollment: Enrollment) => {
        setEnrollmentToUpdatePaymentStatus(enrollment);
        setAmountPaid((enrollment.amount_paid_cents / 100).toFixed(2));
        setPaymentStatus(enrollment.payment_status);
        setUpdatePaymentRemarks('');
        setUpdatePaymentErrors({});
        setShowUpdatePaymentStatusModal(true);
    }, []);
    const handleUpdatePaymentStatusSubmit = () => {
        if (!enrollmentToUpdatePaymentStatus) return;

        router.put(
            `/registrar/enrollments/${enrollmentToUpdatePaymentStatus.id}/payment-status`,
            {
                amount_paid: parseFloat(amountPaid) * 100,
                payment_status: paymentStatus,
                remarks: updatePaymentRemarks,
            },
            {
                onSuccess: () => {
                    setShowUpdatePaymentStatusModal(false);
                    setEnrollmentToUpdatePaymentStatus(null);
                    setAmountPaid('');
                    setPaymentStatus('');
                    setUpdatePaymentRemarks('');
                    setUpdatePaymentErrors({});
                },
                onError: (errors) => {
                    setUpdatePaymentErrors(errors);
                },
            },
        );
    };

    const columns = React.useMemo(
        () =>
            createColumns({
                onApproveClick: handleApproveClick,
                onRejectClick: handleRejectClick,
                onUpdatePaymentStatusClick: handleUpdatePaymentStatusClick,
            }),
        [handleApproveClick, handleRejectClick, handleUpdatePaymentStatusClick],
    );

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
                    placeholder="Filter by student name..."
                    value={(table.getColumn('student')?.getFilterValue() as string) ?? ''}
                    onChange={(event) => table.getColumn('student')?.setFilterValue(event.target.value)}
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

            {/* Approve Enrollment Modal */}
            <Dialog open={showApproveModal} onOpenChange={setShowApproveModal}>
                <DialogContent className="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>Approve Enrollment Application</DialogTitle>
                        <DialogDescription>
                            Provide any remarks for approving enrollment #{enrollmentToApprove?.id} - {enrollmentToApprove?.student.first_name}{' '}
                            {enrollmentToApprove?.student.last_name}.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="remarks">Remarks (Optional)</Label>
                            <Textarea
                                id="remarks"
                                placeholder="Enter remarks..."
                                value={approveRemarks}
                                onChange={(e) => setApproveRemarks(e.target.value)}
                                className={approveErrors.remarks ? 'border-destructive' : ''}
                            />
                            <InputError message={approveErrors.remarks} className="mt-0" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setShowApproveModal(false)}>
                            Cancel
                        </Button>
                        <Button onClick={handleApproveSubmit}>Approve</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Reject Enrollment Modal */}
            <Dialog open={showRejectModal} onOpenChange={setShowRejectModal}>
                <DialogContent className="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>Reject Enrollment Application</DialogTitle>
                        <DialogDescription>
                            Provide a reason for rejecting enrollment #{enrollmentToReject?.id} - {enrollmentToReject?.student.first_name}{' '}
                            {enrollmentToReject?.student.last_name}.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="reason">Reason</Label>
                            <Textarea
                                id="reason"
                                placeholder="Enter rejection reason..."
                                value={rejectReason}
                                onChange={(e) => setRejectReason(e.target.value)}
                                className={rejectErrors.reason ? 'border-destructive' : ''}
                            />
                            <InputError message={rejectErrors.reason} className="mt-0" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setShowRejectModal(false)}>
                            Cancel
                        </Button>
                        <Button onClick={handleRejectSubmit} disabled={!rejectReason.trim()}>
                            Reject
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Update Payment Status Modal */}
            <Dialog open={showUpdatePaymentStatusModal} onOpenChange={setShowUpdatePaymentStatusModal}>
                <DialogContent className="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>Update Payment Status</DialogTitle>
                        <DialogDescription>
                            Update payment status for enrollment #{enrollmentToUpdatePaymentStatus?.id} -{' '}
                            {enrollmentToUpdatePaymentStatus?.student.first_name} {enrollmentToUpdatePaymentStatus?.student.last_name}.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="amount_paid">Amount Paid</Label>
                            <Input
                                id="amount_paid"
                                type="number"
                                step="0.01"
                                placeholder="Enter amount paid..."
                                value={amountPaid}
                                onChange={(e) => setAmountPaid(e.target.value)}
                                className={updatePaymentErrors.amount_paid ? 'border-destructive' : ''}
                            />
                            <InputError message={updatePaymentErrors.amount_paid} className="mt-0" />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="payment_status">Payment Status</Label>
                            <Select value={paymentStatus} onValueChange={setPaymentStatus}>
                                <SelectTrigger className={updatePaymentErrors.payment_status ? 'border-destructive' : ''}>
                                    <SelectValue placeholder="Select payment status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="pending">Pending</SelectItem>
                                    <SelectItem value="partial">Partial</SelectItem>
                                    <SelectItem value="paid">Paid</SelectItem>
                                    <SelectItem value="overdue">Overdue</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError message={updatePaymentErrors.payment_status} className="mt-0" />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="update_remarks">Remarks (Optional)</Label>
                            <Textarea
                                id="update_remarks"
                                placeholder="Enter remarks..."
                                value={updatePaymentRemarks}
                                onChange={(e) => setUpdatePaymentRemarks(e.target.value)}
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setShowUpdatePaymentStatusModal(false)}>
                            Cancel
                        </Button>
                        <Button onClick={handleUpdatePaymentStatusSubmit}>Update</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
}

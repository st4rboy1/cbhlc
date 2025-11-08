import InputError from '@/components/input-error';
import { EnrollmentStatusBadge, PaymentStatusBadge } from '@/components/status-badges';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Link, router } from '@inertiajs/react';
import { type ColumnDef } from '@tanstack/react-table';
import { ArrowUpDown, MoreHorizontal } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

export interface Student {
    id: number;
    student_id: string;
    first_name: string;
    last_name: string;
}

export interface Guardian {
    id: number;
    first_name: string;
    last_name: string;
    user: {
        name: string;
        email: string;
    };
}

export interface Enrollment {
    id: number;
    enrollment_id: string;
    student: Student;
    guardian: Guardian;
    grade_level: string;
    school_year: string;
    status: string;
    payment_status: string;
    net_amount_cents: number;
    balance_cents: number;
    created_at: string;
}

function ActionsCell({ enrollment }: { enrollment: Enrollment }) {
    const [showApproveModal, setShowApproveModal] = useState(false);

    const [showRejectModal, setShowRejectModal] = useState(false);
    const [rejectReason, setRejectReason] = useState('');
    const [rejectErrors, setRejectErrors] = useState<Record<string, string>>({});

    const [showUpdatePaymentStatusModal, setShowUpdatePaymentStatusModal] = useState(false);
    const [amountPaid, setAmountPaid] = useState('');
    const [paymentStatus, setPaymentStatus] = useState('');
    const [updatePaymentRemarks, setUpdatePaymentRemarks] = useState('');
    const [updatePaymentErrors, setUpdatePaymentErrors] = useState<Record<string, string>>({});

    const [showUpdateEnrollmentStatusModal, setShowUpdateEnrollmentStatusModal] = useState(false);
    const [enrollmentStatus, setEnrollmentStatus] = useState('');
    const [updateStatusRemarks, setUpdateStatusRemarks] = useState('');
    const [updateStatusErrors, setUpdateStatusErrors] = useState<Record<string, string>>({});

    const handleApproveClick = () => {
        setShowApproveModal(true);
    };

    const handleApproveSubmit = () => {
        router.post(
            `/registrar/enrollments/${enrollment.id}/approve`,
            {},
            {
                onSuccess: () => {
                    setShowApproveModal(false);
                    toast.success('Enrollment approved successfully.');
                },
                onError: (errors) => {
                    console.error(errors);
                    toast.error('Failed to approve enrollment.');
                },
            },
        );
    };

    const handleRejectClick = () => {
        setRejectReason('');
        setRejectErrors({});
        setShowRejectModal(true);
    };

    const handleRejectSubmit = () => {
        router.post(
            `/registrar/enrollments/${enrollment.id}/reject`,
            { reason: rejectReason },
            {
                onSuccess: () => {
                    setShowRejectModal(false);
                    toast.success('Enrollment rejected successfully.');
                },
                onError: (errors) => {
                    setRejectErrors(errors);
                },
            },
        );
    };

    const handleUpdatePaymentStatusClick = () => {
        setAmountPaid((enrollment.net_amount_cents / 100).toFixed(2));
        setPaymentStatus(enrollment.payment_status);
        setUpdatePaymentRemarks('');
        setUpdatePaymentErrors({});
        setShowUpdatePaymentStatusModal(true);
    };

    const handleUpdatePaymentStatusSubmit = () => {
        router.put(
            `/registrar/enrollments/${enrollment.id}/payment-status`,
            {
                amount_paid: parseFloat(amountPaid) * 100,
                payment_status: paymentStatus,
                remarks: updatePaymentRemarks,
            },
            {
                onSuccess: () => {
                    setShowUpdatePaymentStatusModal(false);
                    toast.success('Payment status updated successfully.');
                },
                onError: (errors) => {
                    setUpdatePaymentErrors(errors);
                },
            },
        );
    };

    const handleUpdateEnrollmentStatusClick = () => {
        setEnrollmentStatus(enrollment.status);
        setUpdateStatusRemarks('');
        setUpdateStatusErrors({});
        setShowUpdateEnrollmentStatusModal(true);
    };

    const handleUpdateEnrollmentStatusSubmit = () => {
        router.put(
            `/registrar/enrollments/${enrollment.id}/status`,
            {
                status: enrollmentStatus,
                remarks: updateStatusRemarks,
            },
            {
                onSuccess: () => {
                    setShowUpdateEnrollmentStatusModal(false);
                    toast.success('Enrollment status updated successfully.');
                },
                onError: (errors) => {
                    setUpdateStatusErrors(errors);
                },
            },
        );
    };

    const handleCompleteClick = () => {
        if (confirm(`Are you sure you want to complete enrollment ${enrollment.id}?`)) {
            router.post(`/registrar/enrollments/${enrollment.id}/complete`);
        }
    };

    const handleConfirmPaymentClick = () => {
        if (confirm(`Are you sure you want to confirm payment for enrollment ${enrollment.id}?`)) {
            router.post(`/registrar/enrollments/${enrollment.id}/confirm-payment`);
        }
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
                    <DropdownMenuItem onClick={() => (window.location.href = `/registrar/enrollments/${enrollment.id}`)}>
                        View Enrollment
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    {enrollment.status === 'pending' && <DropdownMenuItem onClick={handleApproveClick}>Approve Enrollment</DropdownMenuItem>}
                    {enrollment.status === 'pending' && <DropdownMenuItem onClick={handleRejectClick}>Reject Enrollment</DropdownMenuItem>}
                    {enrollment.status === 'approved' && enrollment.payment_status === 'paid' && (
                        <DropdownMenuItem onClick={handleCompleteClick}>Complete Enrollment</DropdownMenuItem>
                    )}
                    {enrollment.payment_status === 'pending' && (
                        <DropdownMenuItem onClick={handleConfirmPaymentClick}>Confirm Payment</DropdownMenuItem>
                    )}
                    <DropdownMenuItem onClick={handleUpdatePaymentStatusClick}>Update Payment Status</DropdownMenuItem>
                    <DropdownMenuItem onClick={handleUpdateEnrollmentStatusClick}>Update Enrollment Status</DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>

            {/* Approve Enrollment Modal */}
            <Dialog open={showApproveModal} onOpenChange={setShowApproveModal}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Approve Enrollment</DialogTitle>
                        <DialogDescription>Are you sure you want to approve this enrollment?</DialogDescription>
                    </DialogHeader>
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
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Reject Enrollment</DialogTitle>
                        <DialogDescription>Please provide a reason for rejecting this enrollment.</DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <Textarea id="reason" placeholder="Rejection reason" value={rejectReason} onChange={(e) => setRejectReason(e.target.value)} />
                        <InputError message={rejectErrors.reason} />
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setShowRejectModal(false)}>
                            Cancel
                        </Button>
                        <Button variant="destructive" onClick={handleRejectSubmit}>
                            Reject
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Update Payment Status Modal */}
            <Dialog open={showUpdatePaymentStatusModal} onOpenChange={setShowUpdatePaymentStatusModal}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Update Payment Status</DialogTitle>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="amount_paid">Amount Paid</Label>
                            <Input id="amount_paid" type="number" value={amountPaid} onChange={(e) => setAmountPaid(e.target.value)} />
                            <InputError message={updatePaymentErrors.amount_paid} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="payment_status">Payment Status</Label>
                            <Select value={paymentStatus} onValueChange={setPaymentStatus}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="pending">Pending</SelectItem>
                                    <SelectItem value="partial">Partial</SelectItem>
                                    <SelectItem value="paid">Paid</SelectItem>
                                    <SelectItem value="overdue">Overdue</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError message={updatePaymentErrors.payment_status} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="update_remarks">Remarks</Label>
                            <Textarea
                                id="update_remarks"
                                placeholder="Remarks"
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

            {/* Update Enrollment Status Modal */}
            <Dialog open={showUpdateEnrollmentStatusModal} onOpenChange={setShowUpdateEnrollmentStatusModal}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Update Enrollment Status</DialogTitle>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="enrollment_status">Enrollment Status</Label>
                            <Select value={enrollmentStatus} onValueChange={setEnrollmentStatus}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="pending">Pending Review</SelectItem>
                                    <SelectItem value="approved">Approved</SelectItem>
                                    <SelectItem value="rejected">Rejected</SelectItem>
                                    <SelectItem value="enrolled">Enrolled</SelectItem>
                                    <SelectItem value="completed">Completed</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError message={updateStatusErrors.status} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="status_remarks">Remarks</Label>
                            <Textarea
                                id="status_remarks"
                                placeholder="Remarks"
                                value={updateStatusRemarks}
                                onChange={(e) => setUpdateStatusRemarks(e.target.value)}
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setShowUpdateEnrollmentStatusModal(false)}>
                            Cancel
                        </Button>
                        <Button onClick={handleUpdateEnrollmentStatusSubmit}>Update</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

export const columns: ColumnDef<Enrollment>[] = [
    {
        accessorKey: 'enrollment_id',
        header: ({ column }) => (
            <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                Enrollment ID
                <ArrowUpDown className="ml-2 h-4 w-4" />
            </Button>
        ),
        cell: ({ row }) => (
            <Link href={`/registrar/enrollments/${row.original.id}`} className="font-medium hover:underline">
                {row.getValue('enrollment_id')}
            </Link>
        ),
    },
    {
        id: 'student',
        accessorFn: (row) => `${row.student.first_name} ${row.student.last_name}`,
        header: ({ column }) => (
            <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                Student
                <ArrowUpDown className="ml-2 h-4 w-4" />
            </Button>
        ),
        cell: ({ row }) => (
            <div>
                <div>{`${row.original.student.first_name} ${row.original.student.last_name}`}</div>
                <div className="text-sm text-muted-foreground">ID: {row.original.student.student_id}</div>
            </div>
        ),
    },
    {
        id: 'guardian',
        accessorFn: (row) => `${row.guardian.first_name} ${row.guardian.last_name}`,
        header: ({ column }) => (
            <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                Guardian
                <ArrowUpDown className="ml-2 h-4 w-4" />
            </Button>
        ),
        cell: ({ row }) => (
            <div>
                <div>{`${row.original.guardian.first_name} ${row.original.guardian.last_name}`}</div>
                <div className="text-sm text-muted-foreground">{row.original.guardian.user.email}</div>
            </div>
        ),
    },
    {
        accessorKey: 'grade_level',
        header: 'Grade Level',
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: ({ row }) => <EnrollmentStatusBadge status={row.getValue('status')} />,
    },
    {
        accessorKey: 'payment_status',
        header: 'Payment',
        cell: ({ row }) => <PaymentStatusBadge status={row.getValue('payment_status')} />,
    },
    {
        id: 'actions',
        cell: ({ row }) => <ActionsCell enrollment={row.original} />,
    },
];

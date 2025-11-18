import { EnrollmentStatusBadge, PaymentStatusBadge } from '@/components/status-badges';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Link, router } from '@inertiajs/react';
import { type ColumnDef } from '@tanstack/react-table';
import { ArrowUpDown, MoreHorizontal } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

export type Student = {
    id: number;
    studentId: string;
    name: string;
    gradeLevel: string;
    guardian: string;
    enrollmentStatus: string;
    paymentStatus: string;
    balance: number;
    netAmount: number;
    activeEnrollmentId: number | null;
};

function formatCurrency(amount: number) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount);
}

function ActionsCell({ student }: { student: Student }) {
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);

    const handleDelete = () => {
        router.delete(`/admin/students/${student.id}`, {
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
                    <DropdownMenuItem onClick={() => navigator.clipboard.writeText(student.studentId)}>Copy Student ID</DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem asChild>
                        <Link href={`/admin/students/${student.id}`}>View Student</Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                        <Link href={`/admin/students/${student.id}/edit`}>Edit Student</Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                        <Link href={`/admin/students/${student.id}/enrollments`}>View Enrollments</Link>
                    </DropdownMenuItem>
                    {student.activeEnrollmentId && (
                        <>
                            <DropdownMenuItem asChild>
                                <Link href={`/admin/enrollments/${student.activeEnrollmentId}/edit`}>Update Enrollment Status</Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem asChild>
                                <Link href={`/admin/enrollments/${student.activeEnrollmentId}`}>Update Payment Status</Link>
                            </DropdownMenuItem>
                        </>
                    )}
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
        accessorKey: 'studentId',
        header: 'Student ID',
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
        accessorKey: 'gradeLevel',
        header: 'Grade',
    },
    {
        accessorKey: 'guardian',
        header: 'Guardian',
    },
    {
        accessorKey: 'enrollmentStatus',
        header: 'Enrollment',
        cell: ({ row }) => <EnrollmentStatusBadge status={row.getValue('enrollmentStatus')} />,
    },
    {
        accessorKey: 'paymentStatus',
        header: 'Payment',
        cell: ({ row }) => <PaymentStatusBadge status={row.getValue('paymentStatus')} />,
    },
    {
        accessorKey: 'balance',
        header: () => <div className="text-right">Balance</div>,
        cell: ({ row }) => {
            const amount = parseFloat(row.getValue('balance'));

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

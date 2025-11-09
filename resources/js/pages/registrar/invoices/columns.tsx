import { InvoiceStatusBadge } from '@/components/status-badges';
import { Button } from '@/components/ui/button';
import { Link, router } from '@inertiajs/react';
import { type ColumnDef } from '@tanstack/react-table';
import { ArrowUpDown, Edit, Eye, Trash } from 'lucide-react';

export interface Student {
    id: number;
    student_id: string;
    first_name: string;
    last_name: string;
}

export interface Enrollment {
    id: number;
    student: Student;
}

export interface Invoice {
    id: number;
    invoice_number: string;
    enrollment: Enrollment;
    total_amount: number;
    paid_amount: number;
    status: string;
    due_date: string;
    created_at: string;
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const handleDelete = (id: number, invoiceNumber: string) => {
    if (confirm(`Are you sure you want to delete invoice ${invoiceNumber}? This action cannot be undone.`)) {
        router.delete(`/registrar/invoices/${id}`);
    }
};

export const columns: ColumnDef<Invoice>[] = [
    {
        accessorKey: 'invoice_number',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Invoice #
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => <div className="font-medium">{row.getValue('invoice_number')}</div>,
    },
    {
        id: 'student',
        accessorFn: (row) => `${row.enrollment.student.first_name} ${row.enrollment.student.last_name}`,
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Student
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => {
            const invoice = row.original;
            return (
                <div>
                    <div>
                        {invoice.enrollment.student.first_name} {invoice.enrollment.student.last_name}
                    </div>
                    <div className="text-sm text-muted-foreground">ID: {invoice.enrollment.student.student_id}</div>
                </div>
            );
        },
    },
    {
        accessorKey: 'total_amount',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Total Amount
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => <div>{formatCurrency(row.getValue('total_amount'))}</div>,
    },
    {
        accessorKey: 'paid_amount',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Paid Amount
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => <div className="text-green-600">{formatCurrency(row.getValue('paid_amount'))}</div>,
    },
    {
        id: 'balance',
        accessorFn: (row) => row.total_amount - row.paid_amount,
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Balance
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => {
            const invoice = row.original;
            const balance = invoice.total_amount - invoice.paid_amount;
            return <div className="text-red-600">{formatCurrency(balance)}</div>;
        },
    },
    {
        accessorKey: 'status',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Status
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => <InvoiceStatusBadge status={row.getValue('status')} />,
    },
    {
        accessorKey: 'due_date',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Due Date
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => <div>{formatDate(row.getValue('due_date'))}</div>,
    },
    {
        id: 'actions',
        header: () => <div className="text-right">Actions</div>,
        cell: ({ row }) => {
            const invoice = row.original;

            return (
                <div className="flex justify-end gap-2">
                    <Link href={`/registrar/invoices/${invoice.id}`}>
                        <Button size="sm" variant="outline">
                            <Eye className="h-4 w-4" />
                        </Button>
                    </Link>
                    <Link href={`/registrar/invoices/${invoice.id}/edit`}>
                        <Button size="sm" variant="outline">
                            <Edit className="h-4 w-4" />
                        </Button>
                    </Link>
                    <Button size="sm" variant="destructive" onClick={() => handleDelete(invoice.id, invoice.invoice_number)}>
                        <Trash className="h-4 w-4" />
                    </Button>
                </div>
            );
        },
    },
];

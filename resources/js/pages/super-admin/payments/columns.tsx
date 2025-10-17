import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Link } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { format } from 'date-fns';
import { ArrowUpDown, Eye, MoreHorizontal, SquarePen, Trash } from 'lucide-react';

interface Invoice {
    id: number;
    invoice_number: string;
    enrollment?: {
        student: {
            id: number;
            student_id: string;
            first_name: string;
            last_name: string;
        };
    };
}

interface Payment {
    id: number;
    invoice: Invoice;
    amount: number;
    payment_method: string;
    payment_date: string;
    reference_number: string | null;
    notes: string | null;
    created_at: string;
}

const getPaymentMethodBadge = (method: string) => {
    const variants: Record<string, { label: string; variant: 'default' | 'secondary' | 'outline' }> = {
        cash: { label: 'Cash', variant: 'default' },
        bank_transfer: { label: 'Bank Transfer', variant: 'secondary' },
        check: { label: 'Check', variant: 'outline' },
        credit_card: { label: 'Credit Card', variant: 'default' },
        gcash: { label: 'GCash', variant: 'secondary' },
        paymaya: { label: 'PayMaya', variant: 'secondary' },
    };

    const config = variants[method] || { label: method, variant: 'outline' as const };

    return (
        <Badge variant={config.variant} className="whitespace-nowrap">
            {config.label}
        </Badge>
    );
};

export const columns: ColumnDef<Payment>[] = [
    {
        accessorKey: 'reference_number',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Reference #
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => <div className="font-medium">{row.getValue('reference_number') || 'N/A'}</div>,
    },
    {
        accessorKey: 'invoice.invoice_number',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Invoice #
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => {
            const invoice = row.original.invoice;
            return (
                <Link href={`/super-admin/invoices/${invoice.id}`} className="font-medium text-primary hover:underline">
                    {invoice.invoice_number}
                </Link>
            );
        },
    },
    {
        accessorKey: 'invoice.enrollment.student',
        header: 'Student',
        cell: ({ row }) => {
            const student = row.original.invoice.enrollment?.student;
            if (!student) return <span className="text-muted-foreground">N/A</span>;
            return (
                <div>
                    <div className="font-medium">
                        {student.first_name} {student.last_name}
                    </div>
                    <div className="text-sm text-muted-foreground">{student.student_id}</div>
                </div>
            );
        },
    },
    {
        accessorKey: 'amount',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Amount
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => {
            const amount = parseFloat(row.getValue('amount'));
            const formatted = new Intl.NumberFormat('en-PH', {
                style: 'currency',
                currency: 'PHP',
            }).format(amount);
            return <div className="font-medium">{formatted}</div>;
        },
    },
    {
        accessorKey: 'payment_method',
        header: 'Payment Method',
        cell: ({ row }) => getPaymentMethodBadge(row.getValue('payment_method')),
    },
    {
        accessorKey: 'payment_date',
        header: ({ column }) => {
            return (
                <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                    Payment Date
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            );
        },
        cell: ({ row }) => {
            const date = new Date(row.getValue('payment_date'));
            return format(date, 'MMM dd, yyyy');
        },
    },
    {
        id: 'actions',
        cell: ({ row }) => {
            const payment = row.original;

            return (
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="ghost" className="h-8 w-8 p-0">
                            <span className="sr-only">Open menu</span>
                            <MoreHorizontal className="h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem asChild>
                            <Link href={`/super-admin/payments/${payment.id}`}>
                                <Eye className="mr-2 h-4 w-4" />
                                View Details
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                            <Link href={`/super-admin/payments/${payment.id}/edit`}>
                                <SquarePen className="mr-2 h-4 w-4" />
                                Edit
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            className="text-destructive"
                            onClick={() => {
                                if (confirm('Are you sure you want to delete this payment?')) {
                                    // @ts-expect-error - router is global
                                    router.delete(`/super-admin/payments/${payment.id}`);
                                }
                            }}
                        >
                            <Trash className="mr-2 h-4 w-4" />
                            Delete
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            );
        },
    },
];

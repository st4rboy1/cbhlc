import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import AppLayout from '@/layouts/app-layout';
import { formatCurrency } from '@/lib/format-currency';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import { Eye } from 'lucide-react';

interface Student {
    id: number;
    first_name: string;
    last_name: string;
}

interface Enrollment {
    id: number;
    student: Student;
}

interface Invoice {
    id: number;
    invoice_number: string;
    enrollment: Enrollment;
}

interface Payment {
    id: number;
    invoice: Invoice;
    amount: number;
    payment_date: string;
    payment_method: string;
    reference_number: string;
    status: string;
}

interface PaginatedPayments {
    data: Payment[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    payments: PaginatedPayments;
}

export default function PaymentsIndex({ payments }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Guardian', href: '/guardian/dashboard' },
        { title: 'Payments', href: '/guardian/payments' },
    ];

    const columns: ColumnDef<Payment>[] = [
        {
            accessorKey: 'invoice.invoice_number',
            header: 'Invoice #',
            cell: ({ row }) => <span className="font-medium">{row.original.invoice.invoice_number}</span>,
        },
        {
            accessorKey: 'student',
            header: 'Student',
            cell: ({ row }) => {
                const student = row.original.invoice?.enrollment?.student;
                return (
                    <div>
                        {student ? (
                            <span>
                                {student.first_name} {student.last_name}
                            </span>
                        ) : (
                            <span className="text-muted-foreground">N/A</span>
                        )}
                    </div>
                );
            },
        },
        {
            accessorKey: 'payment_date',
            header: 'Payment Date',
            cell: ({ row }) => new Date(row.original.payment_date).toLocaleDateString(),
        },
        {
            accessorKey: 'amount',
            header: 'Amount',
            cell: ({ row }) => <span className="font-medium">{formatCurrency(row.original.amount)}</span>,
        },
        {
            accessorKey: 'payment_method',
            header: 'Payment Method',
            cell: ({ row }) => <Badge variant="outline">{row.original.payment_method}</Badge>,
        },
        {
            accessorKey: 'status',
            header: 'Status',
            cell: ({ row }) => <Badge variant="default">{row.original.status}</Badge>,
        },
        {
            id: 'actions',
            header: 'Actions',
            cell: ({ row }) => (
                <div className="flex gap-2">
                    <Button size="sm" variant="outline" onClick={() => router.visit(`/guardian/invoices/${row.original.invoice.id}`)}>
                        <Eye className="mr-1 h-3 w-3" />
                        View Invoice
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Payments" />
            <div className="px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Payments</h1>
                        <p className="mt-1 text-sm text-muted-foreground">View your payment history</p>
                    </div>
                </div>
                <DataTable columns={columns} data={payments.data} />
            </div>
        </AppLayout>
    );
}

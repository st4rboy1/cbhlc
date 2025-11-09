import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import AppLayout from '@/layouts/app-layout';
import { formatCurrency } from '@/lib/format-currency';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { ColumnDef, SortingState } from '@tanstack/react-table';
import { Eye, Plus } from 'lucide-react';
import { useEffect, useState } from 'react';

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
}

interface ReceivedBy {
    id: number;
    name: string;
}

interface Receipt {
    id: number;
    receipt_number: string;
    payment_id: number | null;
    invoice_id: number | null;
    receipt_date: string;
    amount: number;
    payment_method: string;
    notes: string | null;
    payment: Payment | null;
    invoice: Invoice | null;
    received_by: ReceivedBy;
}

interface PaginatedReceipts {
    data: Receipt[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    receipts: PaginatedReceipts & {
        filters: {
            sort_by?: string;
            sort_direction?: string;
        };
    };
}

export default function ReceiptsIndex({ receipts }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Receipts', href: '/admin/receipts' },
    ];

    const [sorting, setSorting] = useState<SortingState>(
        receipts.filters?.sort_by && receipts.filters?.sort_direction
            ? [{ id: receipts.filters.sort_by, desc: receipts.filters.sort_direction === 'desc' }]
            : [],
    );

    useEffect(() => {
        const handler = setTimeout(() => {
            router.get(
                route('admin.receipts.index'),
                {
                    ...receipts.filters,
                    sort_by: sorting.length > 0 ? sorting[0].id : undefined,
                    sort_direction: sorting.length > 0 ? (sorting[0].desc ? 'desc' : 'asc') : undefined,
                },
                { preserveState: true, replace: true },
            );
        }, 300);

        return () => clearTimeout(handler);
    }, [sorting]);

    const columns: ColumnDef<Receipt>[] = [
        {
            accessorKey: 'receipt_number',
            header: 'Receipt Number',
            cell: ({ row }) => <span className="font-medium">{row.original.receipt_number}</span>,
        },
        {
            accessorKey: 'payment',
            header: 'Student',
            cell: ({ row }) => {
                const student = row.original.payment?.invoice?.enrollment?.student;
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
            accessorKey: 'receipt_date',
            header: 'Receipt Date',
            cell: ({ row }) => new Date(row.original.receipt_date).toLocaleDateString(),
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
            id: 'actions',
            header: 'Actions',
            cell: ({ row }) => (
                <div className="flex gap-2">
                    <Button size="sm" variant="outline" onClick={() => router.visit(`/admin/receipts/${row.original.id}`)}>
                        <Eye className="mr-1 h-3 w-3" />
                        View
                    </Button>
                    <Button size="sm" variant="outline" onClick={() => router.visit(`/admin/receipts/${row.original.id}/edit`)}>
                        Edit
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Receipts" />
            <div className="px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Receipts</h1>
                        <p className="mt-1 text-sm text-muted-foreground">Manage payment receipts and records</p>
                    </div>
                    <Link href="/admin/receipts/create">
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Create Receipt
                        </Button>
                    </Link>
                </div>
                <DataTable columns={columns} data={receipts.data} sorting={sorting} onSortingChange={setSorting} />
            </div>
        </AppLayout>
    );
}

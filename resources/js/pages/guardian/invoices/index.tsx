import Heading from '@/components/heading';
import { PaymentStatusBadge } from '@/components/status-badges';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { type ColumnDef } from '@tanstack/react-table';
import { Download, Eye } from 'lucide-react';

interface Student {
    id: number;
    student_id: string;
    first_name: string;
    middle_name?: string;
    last_name: string;
    grade_level: string;
}

interface SchoolYear {
    id: number;
    name: string;
}

interface Enrollment {
    id: number;
    enrollment_id: string;
    student: Student;
    school_year?: SchoolYear;
}

interface Invoice {
    id: number;
    invoice_number: string;
    enrollment: Enrollment;
    total_amount: number;
    paid_amount: number;
    status: string;
    due_date: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationMeta {
    current_page: number;
    from: number;
    last_page: number;
    path: string;
    per_page: number;
    to: number;
    total: number;
}

interface Props {
    invoices: {
        data: Invoice[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
}

export default function GuardianInvoicesIndex({ invoices }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Guardian', href: '/guardian/dashboard' },
        { title: 'Invoices', href: '/guardian/invoices' },
    ];

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
        }).format(amount);
    };

    const columns: ColumnDef<Invoice>[] = [
        {
            accessorKey: 'invoice_number',
            header: 'Invoice Number',
            cell: ({ row }) => {
                return <span className="font-mono text-sm">{row.original.invoice_number}</span>;
            },
        },
        {
            accessorKey: 'enrollment.student',
            header: 'Student',
            cell: ({ row }) => {
                const student = row.original.enrollment.student;
                const fullName = `${student.first_name}${student.middle_name ? ` ${student.middle_name}` : ''} ${student.last_name}`;
                return (
                    <div>
                        <div className="font-medium">{fullName}</div>
                        <div className="text-sm text-muted-foreground">{student.student_id}</div>
                    </div>
                );
            },
        },
        {
            accessorKey: 'enrollment.school_year',
            header: 'School Year',
            cell: ({ row }) => {
                return <span>{row.original.enrollment.school_year?.name || '-'}</span>;
            },
        },
        {
            accessorKey: 'total_amount',
            header: 'Total Amount',
            cell: ({ row }) => {
                return <span className="font-medium">{formatCurrency(row.original.total_amount ?? 0)}</span>;
            },
        },
        {
            accessorKey: 'paid_amount',
            header: 'Amount Paid',
            cell: ({ row }) => {
                return <span className="text-green-600">{formatCurrency(row.original.paid_amount ?? 0)}</span>;
            },
        },
        {
            accessorKey: 'balance',
            header: 'Balance',
            cell: ({ row }) => {
                const balance = (row.original.total_amount ?? 0) - (row.original.paid_amount ?? 0);
                return <span className={balance > 0 ? 'font-medium text-red-600' : 'text-muted-foreground'}>{formatCurrency(balance)}</span>;
            },
        },
        {
            accessorKey: 'status',
            header: 'Status',
            cell: ({ row }) => {
                return <PaymentStatusBadge status={row.original.status} />;
            },
        },
        {
            id: 'actions',
            header: 'Actions',
            cell: ({ row }) => {
                const invoice = row.original;
                return (
                    <div className="flex gap-2">
                        <Button variant="outline" size="sm" asChild>
                            <Link href={`/guardian/invoices/${invoice.id}`}>
                                <Eye className="mr-2 h-4 w-4" />
                                View
                            </Link>
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => {
                                window.location.href = `/guardian/invoices/${invoice.id}/download`;
                            }}
                        >
                            <Download className="mr-2 h-4 w-4" />
                            PDF
                        </Button>
                    </div>
                );
            },
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Invoices" />

            <div className="space-y-6">
                <Heading title="Invoices" description="View and download invoices for your children's enrollments" />

                <DataTable columns={columns} data={invoices.data} />
            </div>
        </AppLayout>
    );
}

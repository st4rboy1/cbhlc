import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
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
    total_amount: number;
    amount_paid: number;
    balance: number;
    payment_status: string;
    created_at: string;
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
    enrollments: {
        data: Enrollment[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
}

export default function GuardianInvoicesIndex({ enrollments }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Guardian', href: '/guardian/dashboard' },
        { title: 'Invoices', href: '/guardian/invoices' },
    ];

    const getPaymentStatusBadge = (status: string) => {
        const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
            paid: 'default',
            partially_paid: 'secondary',
            unpaid: 'destructive',
            pending: 'outline',
        };

        return <Badge variant={variants[status] || 'outline'}>{status.replace('_', ' ').toUpperCase()}</Badge>;
    };

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
        }).format(amount);
    };

    const columns: ColumnDef<Enrollment>[] = [
        {
            accessorKey: 'enrollment_id',
            header: 'Invoice Number',
            cell: ({ row }) => {
                return <span className="font-mono text-sm">{row.original.enrollment_id}</span>;
            },
        },
        {
            accessorKey: 'student',
            header: 'Student',
            cell: ({ row }) => {
                const student = row.original.student;
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
            accessorKey: 'school_year',
            header: 'School Year',
            cell: ({ row }) => {
                return <span>{row.original.school_year?.name || '-'}</span>;
            },
        },
        {
            accessorKey: 'total_amount',
            header: 'Total Amount',
            cell: ({ row }) => {
                return <span className="font-medium">{formatCurrency(row.original.total_amount)}</span>;
            },
        },
        {
            accessorKey: 'amount_paid',
            header: 'Amount Paid',
            cell: ({ row }) => {
                return <span className="text-green-600">{formatCurrency(row.original.amount_paid)}</span>;
            },
        },
        {
            accessorKey: 'balance',
            header: 'Balance',
            cell: ({ row }) => {
                const balance = row.original.balance;
                return <span className={balance > 0 ? 'font-medium text-red-600' : 'text-muted-foreground'}>{formatCurrency(balance)}</span>;
            },
        },
        {
            accessorKey: 'payment_status',
            header: 'Status',
            cell: ({ row }) => {
                return getPaymentStatusBadge(row.original.payment_status);
            },
        },
        {
            id: 'actions',
            header: 'Actions',
            cell: ({ row }) => {
                const enrollment = row.original;
                return (
                    <div className="flex gap-2">
                        <Button variant="outline" size="sm" asChild>
                            <Link href={`/guardian/invoices/${enrollment.id}`}>
                                <Eye className="mr-2 h-4 w-4" />
                                View
                            </Link>
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => {
                                window.location.href = `/guardian/invoices/${enrollment.id}/download`;
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

                <DataTable columns={columns} data={enrollments.data} />
            </div>
        </AppLayout>
    );
}

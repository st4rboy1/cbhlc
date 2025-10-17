import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Edit, Eye, PlusCircle, Search, Trash } from 'lucide-react';
import { useState } from 'react';

interface Student {
    id: number;
    student_id: string;
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
    total_amount: number;
    paid_amount: number;
    status: string;
    due_date: string;
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    invoices: {
        data: Invoice[];
        links: PaginationLink[];
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        search?: string;
        status?: string;
        from_date?: string;
        to_date?: string;
    };
}

export default function SuperAdminInvoicesIndex({ invoices, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const [fromDate, setFromDate] = useState(filters.from_date || '');
    const [toDate, setToDate] = useState(filters.to_date || '');

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Invoices', href: '/super-admin/invoices' },
    ];

    const handleSearch = () => {
        router.get(
            '/super-admin/invoices',
            {
                search: search || undefined,
                status: status || undefined,
                from_date: fromDate || undefined,
                to_date: toDate || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const handleClearFilters = () => {
        setSearch('');
        setStatus('');
        setFromDate('');
        setToDate('');
        router.get('/super-admin/invoices', {}, { preserveState: true, preserveScroll: true });
    };

    const handleDelete = (id: number, invoiceNumber: string) => {
        if (confirm(`Are you sure you want to delete invoice ${invoiceNumber}? This action cannot be undone.`)) {
            router.delete(`/super-admin/invoices/${id}`);
        }
    };

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

    const getStatusBadge = (status: string) => {
        const variants: Record<string, { variant: 'default' | 'secondary' | 'destructive' | 'outline'; label: string }> = {
            draft: { variant: 'outline', label: 'Draft' },
            sent: { variant: 'default', label: 'Sent' },
            partially_paid: { variant: 'secondary', label: 'Partially Paid' },
            paid: { variant: 'default', label: 'Paid' },
            cancelled: { variant: 'destructive', label: 'Cancelled' },
            overdue: { variant: 'destructive', label: 'Overdue' },
        };

        const config = variants[status] || { variant: 'outline' as const, label: status };

        return (
            <Badge variant={config.variant} className={status === 'paid' ? 'bg-green-100 text-green-800' : ''}>
                {config.label}
            </Badge>
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Invoices" />
            <div className="container mx-auto px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Invoices</h1>
                    <Link href="/super-admin/invoices/create">
                        <Button>
                            <PlusCircle className="mr-2 h-4 w-4" />
                            Create Invoice
                        </Button>
                    </Link>
                </div>

                {/* Filters */}
                <Card className="mb-6 p-4">
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <Input
                                placeholder="Search by invoice # or student..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                            />
                        </div>
                        <div>
                            <Select value={status} onValueChange={setStatus}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Filter by status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All Statuses</SelectItem>
                                    <SelectItem value="draft">Draft</SelectItem>
                                    <SelectItem value="sent">Sent</SelectItem>
                                    <SelectItem value="partially_paid">Partially Paid</SelectItem>
                                    <SelectItem value="paid">Paid</SelectItem>
                                    <SelectItem value="overdue">Overdue</SelectItem>
                                    <SelectItem value="cancelled">Cancelled</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <Input type="date" placeholder="From date" value={fromDate} onChange={(e) => setFromDate(e.target.value)} />
                        </div>
                        <div>
                            <Input type="date" placeholder="To date" value={toDate} onChange={(e) => setToDate(e.target.value)} />
                        </div>
                    </div>
                    <div className="mt-4 flex gap-2">
                        <Button onClick={handleSearch} variant="secondary">
                            <Search className="mr-2 h-4 w-4" />
                            Search
                        </Button>
                        {(search || status || fromDate || toDate) && (
                            <Button onClick={handleClearFilters} variant="outline">
                                Clear Filters
                            </Button>
                        )}
                    </div>
                </Card>

                {/* Table */}
                <Card>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Invoice #</TableHead>
                                <TableHead>Student</TableHead>
                                <TableHead>Total Amount</TableHead>
                                <TableHead>Paid Amount</TableHead>
                                <TableHead>Balance</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Due Date</TableHead>
                                <TableHead className="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {invoices.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={8} className="text-center text-muted-foreground">
                                        No invoices found.
                                    </TableCell>
                                </TableRow>
                            ) : (
                                invoices.data.map((invoice) => (
                                    <TableRow key={invoice.id}>
                                        <TableCell className="font-medium">{invoice.invoice_number}</TableCell>
                                        <TableCell>
                                            {invoice.enrollment.student.first_name} {invoice.enrollment.student.last_name}
                                            <br />
                                            <span className="text-sm text-muted-foreground">ID: {invoice.enrollment.student.student_id}</span>
                                        </TableCell>
                                        <TableCell>{formatCurrency(invoice.total_amount)}</TableCell>
                                        <TableCell className="text-green-600">{formatCurrency(invoice.paid_amount)}</TableCell>
                                        <TableCell className="text-red-600">{formatCurrency(invoice.total_amount - invoice.paid_amount)}</TableCell>
                                        <TableCell>{getStatusBadge(invoice.status)}</TableCell>
                                        <TableCell>{formatDate(invoice.due_date)}</TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end gap-2">
                                                <Link href={`/super-admin/invoices/${invoice.id}`}>
                                                    <Button size="sm" variant="outline">
                                                        <Eye className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Link href={`/super-admin/invoices/${invoice.id}/edit`}>
                                                    <Button size="sm" variant="outline">
                                                        <Edit className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Button
                                                    size="sm"
                                                    variant="destructive"
                                                    onClick={() => handleDelete(invoice.id, invoice.invoice_number)}
                                                >
                                                    <Trash className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </Card>

                {/* Pagination */}
                {invoices.last_page > 1 && (
                    <div className="mt-4 flex justify-center gap-2">
                        {invoices.links.map((link, index) => (
                            <Link
                                key={index}
                                href={link.url || '#'}
                                preserveState
                                preserveScroll
                                className={`rounded px-3 py-1 ${
                                    link.active
                                        ? 'bg-primary text-primary-foreground'
                                        : link.url
                                          ? 'bg-secondary text-secondary-foreground hover:bg-secondary/80'
                                          : 'cursor-not-allowed bg-muted text-muted-foreground'
                                }`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Calendar, Edit, FileText, User } from 'lucide-react';

interface Student {
    id: number;
    student_id: string;
    first_name: string;
    middle_name?: string;
    last_name: string;
    grade_level: string;
}

interface User {
    id: number;
    name: string;
    email: string;
}

interface Guardian {
    id: number;
    first_name: string;
    last_name: string;
    user: User;
}

interface Enrollment {
    id: number;
    enrollment_id: string;
    student: Student;
    guardian: Guardian;
    school_year: string;
    grade_level: string;
}

interface InvoiceItem {
    id: number;
    description: string;
    quantity: number;
    unit_price: number;
    amount: number;
}

interface Payment {
    id: number;
    payment_number: string;
    amount: number;
    payment_method: string;
    payment_date: string;
    reference_number?: string;
}

interface Invoice {
    id: number;
    invoice_number: string;
    enrollment: Enrollment;
    total_amount: number;
    paid_amount: number;
    status: string;
    due_date: string;
    paid_at?: string;
    notes?: string;
    items: InvoiceItem[];
    payments: Payment[];
    created_at: string;
    updated_at: string;
}

interface Props {
    invoice: Invoice;
}

export default function SuperAdminInvoicesShow({ invoice }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Invoices', href: '/super-admin/invoices' },
        { title: 'View', href: '#' },
    ];

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
        }).format(amount);
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    const formatDateTime = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const getStudentFullName = () => {
        const middle = invoice.enrollment.student.middle_name ? ` ${invoice.enrollment.student.middle_name}` : '';
        return `${invoice.enrollment.student.first_name}${middle} ${invoice.enrollment.student.last_name}`;
    };

    const getStatusBadge = (status: string) => {
        const variants: Record<string, { className: string; label: string }> = {
            draft: { className: 'bg-gray-100 text-gray-800', label: 'Draft' },
            sent: { className: 'bg-blue-100 text-blue-800', label: 'Sent' },
            partially_paid: { className: 'bg-yellow-100 text-yellow-800', label: 'Partially Paid' },
            paid: { className: 'bg-green-100 text-green-800', label: 'Paid' },
            cancelled: { className: 'bg-red-100 text-red-800', label: 'Cancelled' },
            overdue: { className: 'bg-orange-100 text-orange-800', label: 'Overdue' },
        };

        const config = variants[status] || { className: 'bg-gray-100 text-gray-800', label: status };

        return <Badge className={config.className}>{config.label}</Badge>;
    };

    const remainingBalance = invoice.total_amount - invoice.paid_amount;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Invoice ${invoice.invoice_number}`} />
            <div className="container mx-auto max-w-5xl px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Invoice Details</h1>
                        <p className="text-muted-foreground">{invoice.invoice_number}</p>
                    </div>
                    <div className="flex gap-2">
                        <Link href="/super-admin/invoices">
                            <Button variant="outline">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Back to List
                            </Button>
                        </Link>
                        <Link href={`/super-admin/invoices/${invoice.id}/edit`}>
                            <Button>
                                <Edit className="mr-2 h-4 w-4" />
                                Edit
                            </Button>
                        </Link>
                    </div>
                </div>

                <div className="space-y-6">
                    {/* Invoice Header */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle className="text-xl">{invoice.invoice_number}</CardTitle>
                                    <p className="text-sm text-muted-foreground">Created on {formatDateTime(invoice.created_at)}</p>
                                </div>
                                {getStatusBadge(invoice.status)}
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-6 md:grid-cols-2">
                                <div>
                                    <h3 className="mb-3 font-semibold">Student Information</h3>
                                    <dl className="space-y-2 text-sm">
                                        <div className="flex justify-between">
                                            <dt className="text-gray-600">Name:</dt>
                                            <dd className="font-medium">{getStudentFullName()}</dd>
                                        </div>
                                        <div className="flex justify-between">
                                            <dt className="text-gray-600">Student ID:</dt>
                                            <dd className="font-medium">{invoice.enrollment.student.student_id}</dd>
                                        </div>
                                        <div className="flex justify-between">
                                            <dt className="text-gray-600">Grade Level:</dt>
                                            <dd className="font-medium">{invoice.enrollment.student.grade_level}</dd>
                                        </div>
                                        <div className="flex justify-between">
                                            <dt className="text-gray-600">School Year:</dt>
                                            <dd className="font-medium">{invoice.enrollment.school_year}</dd>
                                        </div>
                                    </dl>
                                </div>

                                <div>
                                    <h3 className="mb-3 font-semibold">Guardian Information</h3>
                                    <dl className="space-y-2 text-sm">
                                        <div className="flex justify-between">
                                            <dt className="text-gray-600">Name:</dt>
                                            <dd className="font-medium">
                                                {invoice.enrollment.guardian.first_name} {invoice.enrollment.guardian.last_name}
                                            </dd>
                                        </div>
                                        <div className="flex justify-between">
                                            <dt className="text-gray-600">Email:</dt>
                                            <dd className="font-medium">{invoice.enrollment.guardian.user.email}</dd>
                                        </div>
                                        <div className="flex justify-between">
                                            <dt className="text-gray-600">Enrollment ID:</dt>
                                            <dd className="font-medium">{invoice.enrollment.enrollment_id}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            <Separator className="my-6" />

                            <div className="grid gap-4 md:grid-cols-3">
                                <div>
                                    <h3 className="mb-2 text-sm font-semibold text-muted-foreground">Due Date</h3>
                                    <div className="flex items-center gap-2">
                                        <Calendar className="h-4 w-4 text-muted-foreground" />
                                        <span className="font-medium">{formatDate(invoice.due_date)}</span>
                                    </div>
                                </div>
                                {invoice.paid_at && (
                                    <div>
                                        <h3 className="mb-2 text-sm font-semibold text-muted-foreground">Paid Date</h3>
                                        <div className="flex items-center gap-2">
                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                            <span className="font-medium">{formatDateTime(invoice.paid_at)}</span>
                                        </div>
                                    </div>
                                )}
                            </div>

                            {invoice.notes && (
                                <div className="mt-6">
                                    <h3 className="mb-2 text-sm font-semibold text-muted-foreground">Notes</h3>
                                    <p className="text-sm">{invoice.notes}</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Invoice Items */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FileText className="h-5 w-5" />
                                Invoice Items
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Description</TableHead>
                                        <TableHead className="text-right">Quantity</TableHead>
                                        <TableHead className="text-right">Unit Price</TableHead>
                                        <TableHead className="text-right">Amount</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {invoice.items.map((item) => (
                                        <TableRow key={item.id}>
                                            <TableCell className="font-medium">{item.description}</TableCell>
                                            <TableCell className="text-right">{item.quantity}</TableCell>
                                            <TableCell className="text-right">{formatCurrency(item.unit_price)}</TableCell>
                                            <TableCell className="text-right font-semibold">{formatCurrency(item.amount)}</TableCell>
                                        </TableRow>
                                    ))}
                                    <TableRow>
                                        <TableCell colSpan={4} className="h-4" />
                                    </TableRow>
                                    <TableRow>
                                        <TableCell colSpan={3} className="text-right font-semibold">
                                            Total Amount:
                                        </TableCell>
                                        <TableCell className="text-right text-lg font-bold">{formatCurrency(invoice.total_amount)}</TableCell>
                                    </TableRow>
                                    {invoice.paid_amount > 0 && (
                                        <>
                                            <TableRow>
                                                <TableCell colSpan={3} className="text-right font-semibold text-green-600">
                                                    Paid Amount:
                                                </TableCell>
                                                <TableCell className="text-right font-bold text-green-600">
                                                    {formatCurrency(invoice.paid_amount)}
                                                </TableCell>
                                            </TableRow>
                                            <TableRow className="border-t-2">
                                                <TableCell colSpan={3} className="text-right text-lg font-bold">
                                                    Balance:
                                                </TableCell>
                                                <TableCell className="text-right text-lg font-bold text-red-600">
                                                    {formatCurrency(remainingBalance)}
                                                </TableCell>
                                            </TableRow>
                                        </>
                                    )}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>

                    {/* Payment History */}
                    {invoice.payments.length > 0 && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Payment History ({invoice.payments.length})</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Payment #</TableHead>
                                            <TableHead>Date</TableHead>
                                            <TableHead>Method</TableHead>
                                            <TableHead>Reference</TableHead>
                                            <TableHead className="text-right">Amount</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {invoice.payments.map((payment) => (
                                            <TableRow key={payment.id}>
                                                <TableCell className="font-medium">{payment.payment_number}</TableCell>
                                                <TableCell>{formatDate(payment.payment_date)}</TableCell>
                                                <TableCell className="capitalize">{payment.payment_method.replace('_', ' ')}</TableCell>
                                                <TableCell>{payment.reference_number || 'N/A'}</TableCell>
                                                <TableCell className="text-right font-semibold text-green-600">
                                                    {formatCurrency(payment.amount)}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>
                    )}

                    {/* Quick Actions */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-wrap gap-2">
                            <Link href={`/super-admin/students/${invoice.enrollment.student.id}`}>
                                <Button variant="outline" size="sm">
                                    <User className="mr-2 h-4 w-4" />
                                    View Student
                                </Button>
                            </Link>
                            <Link href={`/super-admin/enrollments/${invoice.enrollment.id}`}>
                                <Button variant="outline" size="sm">
                                    <FileText className="mr-2 h-4 w-4" />
                                    View Enrollment
                                </Button>
                            </Link>
                            {remainingBalance > 0 && (
                                <Link href={`/super-admin/payments/create?invoice_id=${invoice.id}`}>
                                    <Button variant="default" size="sm">
                                        Record Payment
                                    </Button>
                                </Link>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}

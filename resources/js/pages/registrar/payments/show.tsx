import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { format } from 'date-fns';
import { ArrowLeft, Calendar, CreditCard, DollarSign, FileText, Receipt, User } from 'lucide-react';

interface Student {
    id: number;
    student_id: string;
    first_name: string;
    last_name: string;
}

interface Guardian {
    id: number;
    user: {
        name: string;
        email: string;
    };
}

interface Enrollment {
    id: number;
    student: Student;
    guardian: Guardian;
}

interface InvoiceItem {
    id: number;
    description: string;
    amount: number;
}

interface Invoice {
    id: number;
    invoice_number: string;
    enrollment: Enrollment;
    total_amount: number;
    paid_amount: number;
    status: string;
    items: InvoiceItem[];
}

interface ProcessedBy {
    id: number;
    name: string;
    email: string;
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
    updated_at: string;
    processedBy?: ProcessedBy;
}

interface Props {
    payment: Payment;
}

const getPaymentMethodLabel = (method: string): string => {
    const labels: Record<string, string> = {
        cash: 'Cash',
        bank_transfer: 'Bank Transfer',
        check: 'Check',
        credit_card: 'Credit Card',
        gcash: 'GCash',
        paymaya: 'PayMaya',
    };
    return labels[method] || method;
};

export default function SuperAdminPaymentsShow({ payment }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Registrar', href: '/registrar/dashboard' },
        { title: 'Payments', href: '/registrar/payments' },
        { title: 'Payment Details', href: `/registrar/payments/${payment.id}` },
    ];

    const student = payment.invoice.enrollment.student;
    const guardian = payment.invoice.enrollment.guardian;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Payment Details" />
            <div className="container mx-auto px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href="/registrar/payments">
                            <Button variant="outline" size="icon">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold">Payment Details</h1>
                            <p className="text-sm text-muted-foreground">Reference: {payment.reference_number || 'N/A'}</p>
                        </div>
                    </div>
                    <Link href={`/registrar/payments/${payment.id}/edit`}>
                        <Button>Edit Payment</Button>
                    </Link>
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    {/* Main Payment Information */}
                    <div className="lg:col-span-2">
                        <Card className="p-6">
                            <h2 className="mb-4 text-lg font-semibold">Payment Information</h2>
                            <div className="grid gap-6 md:grid-cols-2">
                                <div className="space-y-1">
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <DollarSign className="h-4 w-4" />
                                        Amount
                                    </div>
                                    <p className="text-2xl font-bold">
                                        {new Intl.NumberFormat('en-PH', {
                                            style: 'currency',
                                            currency: 'PHP',
                                        }).format(payment.amount)}
                                    </p>
                                </div>
                                <div className="space-y-1">
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <CreditCard className="h-4 w-4" />
                                        Payment Method
                                    </div>
                                    <p className="text-lg font-medium">{getPaymentMethodLabel(payment.payment_method)}</p>
                                </div>
                                <div className="space-y-1">
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <Calendar className="h-4 w-4" />
                                        Payment Date
                                    </div>
                                    <p className="text-lg font-medium">{format(new Date(payment.payment_date), 'MMMM dd, yyyy')}</p>
                                </div>
                                <div className="space-y-1">
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <Receipt className="h-4 w-4" />
                                        Reference Number
                                    </div>
                                    <p className="text-lg font-medium">{payment.reference_number || 'N/A'}</p>
                                </div>
                            </div>

                            {payment.notes && (
                                <>
                                    <Separator className="my-6" />
                                    <div className="space-y-2">
                                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                            <FileText className="h-4 w-4" />
                                            Notes
                                        </div>
                                        <p className="text-sm">{payment.notes}</p>
                                    </div>
                                </>
                            )}
                        </Card>

                        {/* Invoice Details */}
                        <Card className="mt-6 p-6">
                            <div className="mb-4 flex items-center justify-between">
                                <h2 className="text-lg font-semibold">Invoice Details</h2>
                                <Link href={`/registrar/invoices/${payment.invoice.id}`} className="text-sm text-primary hover:underline">
                                    View Full Invoice
                                </Link>
                            </div>
                            <div className="space-y-4">
                                <div className="flex items-center justify-between">
                                    <span className="text-sm text-muted-foreground">Invoice Number</span>
                                    <span className="font-medium">{payment.invoice.invoice_number}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm text-muted-foreground">Total Amount</span>
                                    <span className="font-medium">
                                        {new Intl.NumberFormat('en-PH', {
                                            style: 'currency',
                                            currency: 'PHP',
                                        }).format(payment.invoice.total_amount)}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm text-muted-foreground">Amount Paid</span>
                                    <span className="font-medium">
                                        {new Intl.NumberFormat('en-PH', {
                                            style: 'currency',
                                            currency: 'PHP',
                                        }).format(payment.invoice.paid_amount)}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm text-muted-foreground">Balance</span>
                                    <span className="font-bold text-primary">
                                        {new Intl.NumberFormat('en-PH', {
                                            style: 'currency',
                                            currency: 'PHP',
                                        }).format(payment.invoice.total_amount - payment.invoice.paid_amount)}
                                    </span>
                                </div>
                            </div>
                        </Card>
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Student Information */}
                        <Card className="p-6">
                            <div className="mb-4 flex items-center gap-2">
                                <User className="h-4 w-4 text-muted-foreground" />
                                <h2 className="text-lg font-semibold">Student Information</h2>
                            </div>
                            <div className="space-y-3">
                                <div>
                                    <p className="text-sm text-muted-foreground">Name</p>
                                    <p className="font-medium">
                                        {student.first_name} {student.last_name}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Student ID</p>
                                    <p className="font-medium">{student.student_id}</p>
                                </div>
                                <Separator />
                                <div>
                                    <p className="text-sm text-muted-foreground">Guardian</p>
                                    <p className="font-medium">{guardian.user.name}</p>
                                    <p className="text-sm text-muted-foreground">{guardian.user.email}</p>
                                </div>
                            </div>
                        </Card>

                        {/* Metadata */}
                        <Card className="p-6">
                            <h2 className="mb-4 text-lg font-semibold">Metadata</h2>
                            <div className="space-y-3 text-sm">
                                <div>
                                    <p className="text-muted-foreground">Created</p>
                                    <p className="font-medium">{format(new Date(payment.created_at), 'MMM dd, yyyy HH:mm')}</p>
                                </div>
                                <div>
                                    <p className="text-muted-foreground">Last Updated</p>
                                    <p className="font-medium">{format(new Date(payment.updated_at), 'MMM dd, yyyy HH:mm')}</p>
                                </div>
                                {payment.processedBy && (
                                    <div>
                                        <p className="text-muted-foreground">Processed By</p>
                                        <p className="font-medium">{payment.processedBy.name}</p>
                                        <p className="text-xs text-muted-foreground">{payment.processedBy.email}</p>
                                    </div>
                                )}
                            </div>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

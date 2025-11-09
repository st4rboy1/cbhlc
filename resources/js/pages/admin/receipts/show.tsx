import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { formatCurrency } from '@/lib/format-currency';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Calendar, DollarSign, FileText, User } from 'lucide-react';

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

interface Props {
    receipt: Receipt;
}

export default function ReceiptShow({ receipt }: Props) {
    const handleDelete = () => {
        if (confirm('Are you sure you want to delete this receipt? This action cannot be undone.')) {
            router.delete(`/admin/receipts/${receipt.id}`);
        }
    };

    return (
        <AppLayout
            breadcrumbs={[
                { title: 'Admin', href: '/admin/dashboard' },
                { title: 'Receipts', href: '/admin/receipts' },
                { title: receipt.receipt_number, href: '#' },
            ]}
        >
            <Head title={receipt.receipt_number} />
            <div className="px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href="/admin/receipts">
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Back to Receipts
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold">{receipt.receipt_number}</h1>
                            <p className="text-sm text-muted-foreground">
                                <Badge variant="outline">{receipt.payment_method}</Badge>
                            </p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <Link href={`/admin/receipts/${receipt.id}/edit`}>
                            <Button variant="outline">Edit Receipt</Button>
                        </Link>
                        <Button variant="destructive" onClick={handleDelete}>
                            Delete Receipt
                        </Button>
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Amount</CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(receipt.amount)}</div>
                            <p className="text-xs text-muted-foreground">Payment amount</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Receipt Date</CardTitle>
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{new Date(receipt.receipt_date).toLocaleDateString()}</div>
                            <p className="text-xs text-muted-foreground">Date issued</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Received By</CardTitle>
                            <User className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{receipt.received_by.name}</div>
                            <p className="text-xs text-muted-foreground">Receiving staff</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Payment Method</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{receipt.payment_method}</div>
                            <p className="text-xs text-muted-foreground">Payment type</p>
                        </CardContent>
                    </Card>
                </div>

                <div className="mt-4 grid gap-4 md:grid-cols-2">
                    {receipt.payment && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Related Payment</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                {receipt.payment.invoice?.enrollment?.student && (
                                    <div>
                                        <span className="font-medium">Student:</span> {receipt.payment.invoice.enrollment.student.first_name}{' '}
                                        {receipt.payment.invoice.enrollment.student.last_name}
                                    </div>
                                )}
                                <div>
                                    <span className="font-medium">Payment Amount:</span> {formatCurrency(receipt.payment.amount)}
                                </div>
                                <Link href={`/admin/payments/${receipt.payment.id}`}>
                                    <Button variant="outline" size="sm" className="mt-2">
                                        View Payment
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>
                    )}

                    {receipt.invoice && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Related Invoice</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                <div>
                                    <span className="font-medium">Invoice Number:</span> {receipt.invoice.invoice_number}
                                </div>
                                {receipt.invoice.enrollment?.student && (
                                    <div>
                                        <span className="font-medium">Student:</span> {receipt.invoice.enrollment.student.first_name}{' '}
                                        {receipt.invoice.enrollment.student.last_name}
                                    </div>
                                )}
                                <Link href={`/admin/invoices/${receipt.invoice.id}`}>
                                    <Button variant="outline" size="sm" className="mt-2">
                                        View Invoice
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>
                    )}
                </div>

                {receipt.notes && (
                    <Card className="mt-4">
                        <CardHeader>
                            <CardTitle>Notes</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm">{receipt.notes}</p>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}

import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { format } from 'date-fns';
import { ArrowLeft, CalendarIcon, Save } from 'lucide-react';
import { useState } from 'react';

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

interface Invoice {
    id: number;
    invoice_number: string;
    enrollment: Enrollment;
    total_amount: number;
    paid_amount: number;
    status: string;
}

interface Payment {
    id: number;
    invoice: Invoice;
    amount: number;
    payment_method: string;
    payment_date: string;
    reference_number: string | null;
    notes: string | null;
}

interface Props {
    payment: Payment;
    invoices: Invoice[];
}

export default function AdminPaymentsEdit({ payment, invoices }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Administrator', href: '/admin/dashboard' },
        { title: 'Payments', href: '/admin/payments' },
        { title: 'Edit Payment', href: `/admin/payments/${payment.id}/edit` },
    ];

    const [paymentDate, setPaymentDate] = useState<Date>(new Date(payment.payment_date));

    const { data, setData, put, processing, errors } = useForm({
        invoice_id: payment.invoice.id.toString(),
        payment_date: payment.payment_date,
        amount: payment.amount.toString(),
        payment_method: payment.payment_method,
        reference_number: payment.reference_number || '',
        notes: payment.notes || '',
    });

    const selectedInvoice = invoices.find((inv) => inv.id.toString() === data.invoice_id);
    const remainingBalance = selectedInvoice
        ? Number(selectedInvoice.total_amount) - Number(selectedInvoice.paid_amount) + Number(payment.amount)
        : 0;

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/admin/payments/${payment.id}`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Payment" />
            <div className="container mx-auto px-4 py-6">
                <div className="mb-6 flex items-center gap-4">
                    <Link href="/admin/payments">
                        <Button variant="outline" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold">Edit Payment</h1>
                        <p className="text-sm text-muted-foreground">Update payment details for {payment.reference_number || 'this payment'}</p>
                    </div>
                </div>

                <form onSubmit={handleSubmit}>
                    <div className="grid gap-6 lg:grid-cols-3">
                        {/* Main Form */}
                        <div className="lg:col-span-2">
                            <Card className="p-6">
                                <h2 className="mb-6 text-lg font-semibold">Payment Details</h2>

                                <div className="space-y-6">
                                    {/* Invoice Selection */}
                                    <div className="space-y-2">
                                        <Label htmlFor="invoice_id">
                                            Invoice <span className="text-destructive">*</span>
                                        </Label>
                                        <Select value={data.invoice_id} onValueChange={(value) => setData('invoice_id', value)}>
                                            <SelectTrigger id="invoice_id">
                                                <SelectValue placeholder="Select an invoice" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {invoices.map((invoice) => (
                                                    <SelectItem key={invoice.id} value={invoice.id.toString()}>
                                                        {invoice.invoice_number} - {invoice.enrollment.student.first_name}{' '}
                                                        {invoice.enrollment.student.last_name} (Balance: ₱
                                                        {(Number(invoice.total_amount) - Number(invoice.paid_amount)).toFixed(2)})
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.invoice_id && <p className="text-sm text-destructive">{errors.invoice_id}</p>}
                                    </div>

                                    {/* Payment Date */}
                                    <div className="space-y-2">
                                        <Label htmlFor="payment_date">
                                            Payment Date <span className="text-destructive">*</span>
                                        </Label>
                                        <Popover>
                                            <PopoverTrigger asChild>
                                                <Button
                                                    variant="outline"
                                                    className={cn(
                                                        'w-full justify-start text-left font-normal',
                                                        !paymentDate && 'text-muted-foreground',
                                                    )}
                                                >
                                                    <CalendarIcon className="mr-2 h-4 w-4" />
                                                    {paymentDate ? format(paymentDate, 'PPP') : <span>Pick a date</span>}
                                                </Button>
                                            </PopoverTrigger>
                                            <PopoverContent className="w-auto p-0" align="start">
                                                <Calendar
                                                    mode="single"
                                                    selected={paymentDate}
                                                    onSelect={(date) => {
                                                        if (date) {
                                                            setPaymentDate(date);
                                                            setData('payment_date', format(date, 'yyyy-MM-dd'));
                                                        }
                                                    }}
                                                    initialFocus
                                                    className="rounded-md border shadow"
                                                />
                                            </PopoverContent>
                                        </Popover>
                                        {errors.payment_date && <p className="text-sm text-destructive">{errors.payment_date}</p>}
                                    </div>

                                    {/* Amount */}
                                    <div className="space-y-2">
                                        <Label htmlFor="amount">
                                            Amount <span className="text-destructive">*</span>
                                        </Label>
                                        <Input
                                            id="amount"
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            value={data.amount}
                                            onChange={(e) => setData('amount', e.target.value)}
                                            placeholder="0.00"
                                        />
                                        {errors.amount && <p className="text-sm text-destructive">{errors.amount}</p>}
                                        {selectedInvoice && (
                                            <p className="text-sm text-muted-foreground">
                                                Remaining balance (after this payment): ₱{remainingBalance.toFixed(2)}
                                            </p>
                                        )}
                                    </div>

                                    {/* Payment Method */}
                                    <div className="space-y-2">
                                        <Label htmlFor="payment_method">
                                            Payment Method <span className="text-destructive">*</span>
                                        </Label>
                                        <Select value={data.payment_method} onValueChange={(value) => setData('payment_method', value)}>
                                            <SelectTrigger id="payment_method">
                                                <SelectValue placeholder="Select payment method" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="cash">Cash</SelectItem>
                                                <SelectItem value="bank_transfer">Bank Transfer</SelectItem>
                                                <SelectItem value="gcash">GCash</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        {errors.payment_method && <p className="text-sm text-destructive">{errors.payment_method}</p>}
                                    </div>

                                    {/* Reference Number */}
                                    <div className="space-y-2">
                                        <Label htmlFor="reference_number">Reference Number</Label>
                                        <Input
                                            id="reference_number"
                                            value={data.reference_number}
                                            onChange={(e) => setData('reference_number', e.target.value)}
                                            placeholder="Enter reference number (optional)"
                                        />
                                        {errors.reference_number && <p className="text-sm text-destructive">{errors.reference_number}</p>}
                                    </div>

                                    {/* Notes */}
                                    <div className="space-y-2">
                                        <Label htmlFor="notes">Notes</Label>
                                        <Textarea
                                            id="notes"
                                            value={data.notes}
                                            onChange={(e) => setData('notes', e.target.value)}
                                            placeholder="Enter any additional notes (optional)"
                                            rows={4}
                                        />
                                        {errors.notes && <p className="text-sm text-destructive">{errors.notes}</p>}
                                    </div>
                                </div>
                            </Card>
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            {selectedInvoice && (
                                <Card className="p-6">
                                    <h3 className="mb-4 font-semibold">Invoice Summary</h3>
                                    <div className="space-y-3 text-sm">
                                        <div>
                                            <p className="text-muted-foreground">Student</p>
                                            <p className="font-medium">
                                                {selectedInvoice.enrollment.student.first_name} {selectedInvoice.enrollment.student.last_name}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-muted-foreground">Total Amount</p>
                                            <p className="font-medium">₱{Number(selectedInvoice.total_amount).toFixed(2)}</p>
                                        </div>
                                        <div>
                                            <p className="text-muted-foreground">Amount Paid</p>
                                            <p className="font-medium">₱{Number(selectedInvoice.paid_amount).toFixed(2)}</p>
                                        </div>
                                        <div>
                                            <p className="text-muted-foreground">Balance (after this payment)</p>
                                            <p className="text-lg font-bold text-primary">₱{remainingBalance.toFixed(2)}</p>
                                        </div>
                                    </div>
                                </Card>
                            )}

                            <Card className="p-6">
                                <div className="space-y-4">
                                    <Button type="submit" className="w-full" disabled={processing}>
                                        <Save className="mr-2 h-4 w-4" />
                                        {processing ? 'Updating...' : 'Update Payment'}
                                    </Button>
                                    <Link href="/admin/payments" className="block">
                                        <Button type="button" variant="outline" className="w-full">
                                            Cancel
                                        </Button>
                                    </Link>
                                </div>
                            </Card>
                        </div>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}

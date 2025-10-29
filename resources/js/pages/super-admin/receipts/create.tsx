import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { Head, useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import { route } from 'ziggy-js';

interface Student {
    id: number;
    first_name: string;
    last_name: string;
}

interface Enrollment {
    id: number;
    student?: Student | null;
}

interface Invoice {
    id: number;
    invoice_number: string;
    enrollment?: Enrollment | null;
}

interface Payment {
    id: number;
    invoice?: Invoice | null;
    amount: number | string;
}

interface Props {
    payments: Payment[];
    invoices: Invoice[];
    nextReceiptNumber: string;
}

export default function ReceiptCreate({ payments, invoices, nextReceiptNumber }: Props) {
    const { data, setData, post, processing, errors, wasSuccessful } = useForm({
        payment_id: '',
        invoice_id: '',
        receipt_date: new Date().toISOString().split('T')[0],
        amount: '',
        payment_method: '',
        notes: '',
    });

    useEffect(() => {
        if (wasSuccessful) toast.success('Receipt created successfully.');
    }, [wasSuccessful]);

    return (
        <AppLayout
            breadcrumbs={[
                { title: 'Super Admin', href: '/super-admin/dashboard' },
                { title: 'Receipts', href: '/super-admin/receipts' },
                { title: 'Create', href: '#' },
            ]}
        >
            <Head title="Create Receipt" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Create Receipt</h1>
                <Card className="mx-auto max-w-2xl">
                    <CardHeader>
                        <CardTitle>Receipt Information</CardTitle>
                        <p className="text-sm text-muted-foreground">
                            Receipt Number: <span className="font-medium">{nextReceiptNumber}</span>
                        </p>
                    </CardHeader>
                    <CardContent>
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                post(route('super-admin.receipts.store'));
                            }}
                            className="space-y-4"
                        >
                            <div>
                                <Label>Payment (Optional)</Label>
                                <Select value={data.payment_id} onValueChange={(value) => setData('payment_id', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a payment" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {payments.map((payment) => {
                                            const student = payment.invoice?.enrollment?.student;
                                            const amount = typeof payment.amount === 'number' ? payment.amount : parseFloat(payment.amount || '0');
                                            return (
                                                <SelectItem key={payment.id} value={payment.id.toString()}>
                                                    {student ? `${student.first_name} ${student.last_name}` : 'Unknown Student'} - ₱
                                                    {amount.toLocaleString()}
                                                </SelectItem>
                                            );
                                        })}
                                    </SelectContent>
                                </Select>
                                {errors.payment_id && <p className="text-sm text-red-500">{errors.payment_id}</p>}
                            </div>

                            <div>
                                <Label>Invoice (Optional)</Label>
                                <Select value={data.invoice_id} onValueChange={(value) => setData('invoice_id', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select an invoice" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {invoices.map((invoice) => {
                                            const student = invoice.enrollment?.student;
                                            return (
                                                <SelectItem key={invoice.id} value={invoice.id.toString()}>
                                                    {invoice.invoice_number} -{' '}
                                                    {student ? `${student.first_name} ${student.last_name}` : 'Unknown Student'}
                                                </SelectItem>
                                            );
                                        })}
                                    </SelectContent>
                                </Select>
                                {errors.invoice_id && <p className="text-sm text-red-500">{errors.invoice_id}</p>}
                            </div>

                            <div>
                                <Label>Receipt Date</Label>
                                <Input
                                    type="date"
                                    value={data.receipt_date}
                                    onChange={(e) => setData('receipt_date', e.target.value)}
                                    className={errors.receipt_date ? 'border-red-500' : ''}
                                />
                                {errors.receipt_date && <p className="text-sm text-red-500">{errors.receipt_date}</p>}
                            </div>

                            <div>
                                <Label>Amount</Label>
                                <Input
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    value={data.amount}
                                    onChange={(e) => setData('amount', e.target.value)}
                                    className={errors.amount ? 'border-red-500' : ''}
                                    placeholder="0.00"
                                />
                                {errors.amount && <p className="text-sm text-red-500">{errors.amount}</p>}
                            </div>

                            <div>
                                <Label>Payment Method</Label>
                                <Select value={data.payment_method} onValueChange={(value) => setData('payment_method', value)}>
                                    <SelectTrigger className={errors.payment_method ? 'border-red-500' : ''}>
                                        <SelectValue placeholder="Select payment method" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Cash">Cash</SelectItem>
                                        <SelectItem value="Check">Check</SelectItem>
                                        <SelectItem value="Bank Transfer">Bank Transfer</SelectItem>
                                        <SelectItem value="Credit Card">Credit Card</SelectItem>
                                        <SelectItem value="Debit Card">Debit Card</SelectItem>
                                        <SelectItem value="GCash">GCash</SelectItem>
                                        <SelectItem value="PayMaya">PayMaya</SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.payment_method && <p className="text-sm text-red-500">{errors.payment_method}</p>}
                            </div>

                            <div>
                                <Label>Notes (Optional)</Label>
                                <Input value={data.notes} onChange={(e) => setData('notes', e.target.value)} placeholder="Additional notes" />
                                {errors.notes && <p className="text-sm text-red-500">{errors.notes}</p>}
                            </div>

                            <div className="flex gap-4">
                                <Button type="submit" disabled={processing}>
                                    Create Receipt
                                </Button>
                                <Button type="button" variant="outline" onClick={() => window.history.back()}>
                                    Cancel
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

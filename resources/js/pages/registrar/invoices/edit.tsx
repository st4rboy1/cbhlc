import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Plus, Save, Trash } from 'lucide-react';

interface Student {
    id: number;
    student_id: string;
    first_name: string;
    last_name: string;
}

interface User {
    id: number;
    name: string;
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
}

interface InvoiceItem {
    id?: number;
    description: string;
    quantity: number;
    unit_price: number;
    amount: number;
}

interface Invoice {
    id: number;
    invoice_number: string;
    enrollment_id: number;
    enrollment: Enrollment;
    invoice_date: string;
    due_date: string;
    status: string;
    items: InvoiceItem[];
}

interface FormData {
    enrollment_id: string;
    invoice_date: string;
    due_date: string;
    status: string;
    items: InvoiceItem[];
}

interface Props {
    invoice: Invoice;
    enrollments: Enrollment[];
}

export default function RegistrarInvoicesEdit({ invoice, enrollments }: Props) {
    const { data, setData, put, processing, errors } = useForm<FormData>({
        enrollment_id: invoice.enrollment_id.toString(),
        invoice_date: invoice.invoice_date.substring(0, 10),
        due_date: invoice.due_date.substring(0, 10),
        status: invoice.status,
        items: invoice.items.map((item) => ({
            id: item.id,
            description: item.description,
            quantity: Number(item.quantity),
            unit_price: Number(item.unit_price),
            amount: Number(item.amount),
        })),
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Registrar', href: '/registrar/dashboard' },
        { title: 'Invoices', href: '/registrar/invoices' },
        { title: 'Edit', href: '#' },
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/registrar/invoices/${invoice.id}`);
    };

    const addItem = () => {
        setData('items', [
            ...data.items,
            {
                description: '',
                quantity: 1,
                unit_price: 0,
                amount: 0,
            },
        ]);
    };

    const removeItem = (index: number) => {
        if (data.items.length === 1) {
            alert('At least one item is required.');
            return;
        }
        const newItems = data.items.filter((_, i) => i !== index);
        setData('items', newItems);
    };

    const updateItem = (index: number, field: keyof InvoiceItem, value: string | number) => {
        const newItems = [...data.items];
        newItems[index] = {
            ...newItems[index],
            [field]: Number(value),
        };

        // Recalculate amount if quantity or unit_price changed
        if (field === 'quantity' || field === 'unit_price') {
            const quantity = newItems[index].quantity;
            const unitPrice = newItems[index].unit_price;
            newItems[index].amount = quantity * unitPrice;
        }

        setData('items', newItems);
    };

    const calculateTotal = () => {
        return data.items.reduce((sum, item) => sum + item.amount, 0);
    };

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
        }).format(amount);
    };

    const getEnrollmentDisplay = (enrollment: Enrollment) => {
        return `${enrollment.student.first_name} ${enrollment.student.last_name} (${enrollment.student.student_id}) - ${enrollment.enrollment_id}`;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Invoice" />
            <div className="container mx-auto max-w-4xl px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Edit Invoice</h1>
                        <p className="text-muted-foreground">{invoice.invoice_number}</p>
                    </div>
                    <Link href="/registrar/invoices">
                        <Button variant="outline">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back to List
                        </Button>
                    </Link>
                </div>

                <form onSubmit={handleSubmit}>
                    <div className="space-y-6">
                        {/* Invoice Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Invoice Information</CardTitle>
                                <CardDescription>Update invoice details and status.</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="enrollment_id">
                                        Enrollment <span className="text-red-600">*</span>
                                    </Label>
                                    <Select value={data.enrollment_id} onValueChange={(value) => setData('enrollment_id', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select enrollment" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {enrollments.map((enrollment) => (
                                                <SelectItem key={enrollment.id} value={enrollment.id.toString()}>
                                                    {getEnrollmentDisplay(enrollment)}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.enrollment_id && <p className="text-sm text-red-600">{errors.enrollment_id}</p>}
                                </div>

                                <div className="grid gap-4 md:grid-cols-3">
                                    <div className="space-y-2">
                                        <Label htmlFor="invoice_date">
                                            Invoice Date <span className="text-red-600">*</span>
                                        </Label>
                                        <Input
                                            id="invoice_date"
                                            type="date"
                                            value={data.invoice_date}
                                            onChange={(e) => setData('invoice_date', e.target.value)}
                                        />
                                        {errors.invoice_date && <p className="text-sm text-red-600">{errors.invoice_date}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="due_date">
                                            Due Date <span className="text-red-600">*</span>
                                        </Label>
                                        <Input
                                            id="due_date"
                                            type="date"
                                            value={data.due_date}
                                            onChange={(e) => setData('due_date', e.target.value)}
                                        />
                                        {errors.due_date && <p className="text-sm text-red-600">{errors.due_date}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="status">
                                            Status <span className="text-red-600">*</span>
                                        </Label>
                                        <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="draft">Draft</SelectItem>
                                                <SelectItem value="sent">Sent</SelectItem>
                                                <SelectItem value="paid">Paid</SelectItem>
                                                <SelectItem value="overdue">Overdue</SelectItem>
                                                <SelectItem value="cancelled">Cancelled</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        {errors.status && <p className="text-sm text-red-600">{errors.status}</p>}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Invoice Items */}
                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle>Invoice Items</CardTitle>
                                        <CardDescription>Update or add items to this invoice. At least one item is required.</CardDescription>
                                    </div>
                                    <Button type="button" variant="outline" size="sm" onClick={addItem}>
                                        <Plus className="mr-2 h-4 w-4" />
                                        Add Item
                                    </Button>
                                </div>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {data.items.map((item, index) => (
                                    <Card key={index} className="border-2">
                                        <CardContent className="pt-6">
                                            <div className="space-y-4">
                                                <div className="flex items-start justify-between">
                                                    <div>
                                                        <h4 className="font-semibold">Item {index + 1}</h4>
                                                        {item.id && <p className="text-xs text-muted-foreground">Existing Item #{item.id}</p>}
                                                    </div>
                                                    {data.items.length > 1 && (
                                                        <Button type="button" variant="destructive" size="sm" onClick={() => removeItem(index)}>
                                                            <Trash className="h-4 w-4" />
                                                        </Button>
                                                    )}
                                                </div>

                                                <div className="space-y-2">
                                                    <Label htmlFor={`item-description-${index}`}>
                                                        Description <span className="text-red-600">*</span>
                                                    </Label>
                                                    <Input
                                                        id={`item-description-${index}`}
                                                        type="text"
                                                        placeholder="e.g., Tuition Fee - Grade 1"
                                                        value={item.description}
                                                        onChange={(e) => updateItem(index, 'description', e.target.value)}
                                                    />
                                                    {errors[`items.${index}.description`] && (
                                                        <p className="text-sm text-red-600">{errors[`items.${index}.description`]}</p>
                                                    )}
                                                </div>

                                                <div className="grid gap-4 md:grid-cols-3">
                                                    <div className="space-y-2">
                                                        <Label htmlFor={`item-quantity-${index}`}>
                                                            Quantity <span className="text-red-600">*</span>
                                                        </Label>
                                                        <Input
                                                            id={`item-quantity-${index}`}
                                                            type="number"
                                                            min="1"
                                                            step="1"
                                                            value={item.quantity}
                                                            onChange={(e) => updateItem(index, 'quantity', Number(e.target.value))}
                                                        />
                                                        {errors[`items.${index}.quantity`] && (
                                                            <p className="text-sm text-red-600">{errors[`items.${index}.quantity`]}</p>
                                                        )}
                                                    </div>

                                                    <div className="space-y-2">
                                                        <Label htmlFor={`item-unit-price-${index}`}>
                                                            Unit Price <span className="text-red-600">*</span>
                                                        </Label>
                                                        <Input
                                                            id={`item-unit-price-${index}`}
                                                            type="number"
                                                            min="0"
                                                            step="0.01"
                                                            value={item.unit_price}
                                                            onChange={(e) => updateItem(index, 'unit_price', Number(e.target.value))}
                                                        />
                                                        {errors[`items.${index}.unit_price`] && (
                                                            <p className="text-sm text-red-600">{errors[`items.${index}.unit_price`]}</p>
                                                        )}
                                                    </div>

                                                    <div className="space-y-2">
                                                        <Label>Amount</Label>
                                                        <div className="flex h-10 items-center rounded-md border bg-muted px-3 py-2 font-semibold">
                                                            {formatCurrency(item.amount)}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                ))}

                                {errors.items && typeof errors.items === 'string' && <p className="text-sm text-red-600">{errors.items}</p>}

                                {/* Total */}
                                <div className="flex justify-end border-t pt-4">
                                    <div className="w-full max-w-xs space-y-2">
                                        <div className="flex justify-between text-lg font-bold">
                                            <span>Total Amount:</span>
                                            <span className="text-primary">{formatCurrency(calculateTotal())}</span>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="mt-6 flex justify-end gap-4">
                        <Link href="/registrar/invoices">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                        <Button type="submit" disabled={processing}>
                            <Save className="mr-2 h-4 w-4" />
                            Update Invoice
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}

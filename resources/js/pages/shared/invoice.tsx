import Heading from '@/components/heading';
import { PaymentStatusBadge } from '@/components/status-badges';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { AlertCircle, Building2, Calendar, Download, FileText, Mail, Phone, Printer } from 'lucide-react';
import { useRef } from 'react';

interface Student {
    student_id: string;
    first_name: string;
    middle_name?: string;
    last_name: string;
    grade_level: string;
    section?: string;
}

interface SchoolYear {
    id: number;
    name: string;
    start_year: number;
    end_year: number;
}

interface Enrollment {
    id: number;
    enrollment_id: string;
    student: Student;
    school_year?: SchoolYear;
    semester?: string;
    payment_due_date?: string;
    created_at: string;
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
    total_amount: number;
    paid_amount: number;
    items: InvoiceItem[];
}

interface Props {
    invoice: Invoice;
    settings: {
        school_name: string;
        school_address: string;
        school_phone: string;
        school_email: string;
        payment_location: string;
        payment_hours: string;
        payment_methods: string;
        payment_note: string;
    };
}

export default function Invoice({ invoice, settings }: Props) {
    const { auth } = usePage<{
        auth: {
            user: {
                roles?: Array<{ name: string }>;
            };
        };
    }>().props;

    // Determine the correct route prefix based on user role
    const getRolePrefix = () => {
        if (!auth?.user?.roles) return '';
        const role = auth.user.roles[0]?.name;
        switch (role) {
            case 'guardian':
                return '/guardian';
            case 'registrar':
            case 'administrator':
                return '/registrar';
            case 'super_admin':
                return '/super-admin';
            default:
                return '';
        }
    };

    const rolePrefix = getRolePrefix();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Invoice',
            href: '/invoice',
        },
    ];

    const invoiceRef = useRef<HTMLDivElement>(null);

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
        }).format(amount);
    };

    const formatDate = (dateString?: string) => {
        if (!dateString) return 'Not set';
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    const handlePrint = () => {
        const printContent = invoiceRef.current;
        if (!printContent) return;

        const printWindow = window.open('', '', 'height=800,width=1000');
        if (!printWindow) return;

        printWindow.document.write('<html><head><title>Invoice</title>');

        // Copy all stylesheets from the main document to the print window
        Array.from(document.styleSheets).forEach((styleSheet) => {
            try {
                const cssRules = Array.from(styleSheet.cssRules)
                    .map((rule) => rule.cssText)
                    .join('');
                const style = printWindow.document.createElement('style');
                style.appendChild(printWindow.document.createTextNode(cssRules));
                printWindow.document.head.appendChild(style);
            } catch (e) {
                console.error('Could not read stylesheet', e);
            }
        });

        printWindow.document.write('</head><body class="bg-white dark:bg-black">');

        // Clone the content and remove action buttons
        const clonedContent = printContent.cloneNode(true) as HTMLElement;
        const buttons = clonedContent.querySelector('.no-print');
        if (buttons) buttons.remove();

        printWindow.document.write(clonedContent.innerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();

        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500); // Increased timeout for better rendering
    };

    const handleDownloadPDF = () => {
        if (!invoice) return;
        // Navigate to server-side PDF download endpoint using role-based route
        window.location.href = `${rolePrefix}/invoices/${invoice.id}/download`;
    };

    if (!invoice) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Invoice" />

                <div className="px-4 py-6">
                    <Heading title="Invoice" description="No invoice record found" />
                    <Card className="mx-auto max-w-4xl">
                        <CardContent className="p-8">
                            <div className="flex flex-col items-center justify-center space-y-4 py-12">
                                <AlertCircle className="h-12 w-12 text-yellow-500" />
                                <h2 className="text-2xl font-semibold">No Invoice Available</h2>
                                <p className="text-center text-muted-foreground">No invoice record found. Please ensure the invoice exists.</p>
                                <Link href="/super-admin/invoices" className="mt-4 inline-block text-primary hover:underline">
                                    Go to Invoices List →
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    const enrollment = invoice.enrollment;

    const calculateTotalAmount = () => {
        return invoice.items.reduce((sum, item) => sum + item.amount, 0);
    };

    const calculateNetAmount = () => {
        // Assuming discount is applied to the total of items
        const totalItemsAmount = calculateTotalAmount();
        // For now, let's assume discount is not part of invoice items but a separate field on invoice
        // If discount is part of invoice items, this logic needs adjustment
        return totalItemsAmount - (invoice.discount || 0);
    };

    const calculateBalanceDue = () => {
        return calculateNetAmount() - invoice.paid_amount;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Invoice" />
            <style>{`
                @media print {
                    .no-print { display: none !important; }
                    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                }
            `}</style>

            <div className="px-4 py-6">
                <Heading
                    title={`Invoice ${invoice.invoice_number}`}
                    description={
                        invoice.enrollment
                            ? `Invoice for ${invoice.enrollment.student.first_name} ${invoice.enrollment.student.last_name}`
                            : 'Tuition fee invoice'
                    }
                />
                <Card className="mx-auto max-w-4xl">
                    <CardContent className="p-8" ref={invoiceRef}>
                        {/* Invoice Header */}
                        <div className="mb-8 flex items-start justify-between">
                            <div className="flex items-center gap-4">
                                <div className="flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
                                    <Building2 className="h-8 w-8 text-primary" />
                                </div>
                                <div>
                                    <h1 className="text-2xl font-bold text-primary">{settings.school_name}</h1>
                                    <p className="text-sm text-muted-foreground">{settings.school_address}</p>
                                    <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                        <span className="flex items-center gap-1">
                                            <Phone className="h-3 w-3" />
                                            {settings.school_phone}
                                        </span>
                                        <span className="flex items-center gap-1">
                                            <Mail className="h-3 w-3" />
                                            {settings.school_email}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div className="text-right">
                                <div className="mb-4">
                                    <h2 className="text-3xl font-bold">INVOICE</h2>
                                    <Badge variant="outline" className="mt-1">
                                        {invoice.invoice_number}
                                    </Badge>
                                </div>
                                <div className="mt-2">
                                    <PaymentStatusBadge status={invoice.status} />
                                </div>
                            </div>
                        </div>

                        <Separator className="mb-8" />

                        {/* Invoice Details */}
                        <div className="mb-8 grid gap-8 md:grid-cols-2">
                            <div>
                                <h3 className="mb-4 text-lg font-semibold">Billed To:</h3>
                                <div className="space-y-2">
                                    <p className="font-medium">
                                        {`${enrollment.student.first_name} ${enrollment.student.middle_name ? enrollment.student.middle_name + ' ' : ''}${enrollment.student.last_name}`}
                                    </p>
                                    <p className="text-sm text-muted-foreground">Student ID: {enrollment.student.student_id}</p>
                                    <p className="text-sm text-muted-foreground">Grade Level: {enrollment.student.grade_level}</p>
                                    {enrollment.student.section && (
                                        <p className="text-sm text-muted-foreground">Section: {enrollment.student.section}</p>
                                    )}
                                    {enrollment.school_year && (
                                        <p className="text-sm text-muted-foreground">School Year: {enrollment.school_year.name}</p>
                                    )}
                                    {enrollment.semester && <p className="text-sm text-muted-foreground">Semester: {enrollment.semester}</p>}
                                </div>
                            </div>

                            <div className="text-right">
                                <div className="space-y-2">
                                    <div className="flex justify-between">
                                        <span className="font-medium">Invoice Date:</span>
                                        <span className="flex items-center gap-1">
                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                            {formatDate(invoice.invoice_date)}
                                        </span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="font-medium">Due Date:</span>
                                        <span className="flex items-center gap-1">
                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                            {formatDate(invoice.due_date)}
                                        </span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="font-medium">Enrollment ID:</span>
                                        <span>{enrollment.enrollment_id}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Invoice Items Table */}
                        <div className="mb-8">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="text-left">Description</TableHead>
                                        <TableHead className="text-right">Amount</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {invoice.items.map((item, index) => (
                                        <TableRow key={index}>
                                            <TableCell className="font-medium">{item.description}</TableCell>
                                            <TableCell className="text-right font-semibold">{formatCurrency(item.amount)}</TableCell>
                                        </TableRow>
                                    ))}
                                    <TableRow>
                                        <TableCell colSpan={2} className="h-4" />
                                    </TableRow>
                                    <TableRow>
                                        <TableCell className="text-right font-semibold">Subtotal:</TableCell>
                                        <TableCell className="text-right font-semibold">{formatCurrency(calculateTotalAmount())}</TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell className="text-right font-semibold">Less: Discounts & Rebates:</TableCell>
                                        <TableCell className="text-right font-semibold">
                                            {invoice.total_amount - calculateTotalAmount() > 0
                                                ? `-${formatCurrency(invoice.total_amount - calculateTotalAmount())}`
                                                : formatCurrency(0)}
                                        </TableCell>
                                    </TableRow>
                                    <TableRow className="border-t-2">
                                        <TableCell className="text-right text-xl font-bold">NET AMOUNT:</TableCell>
                                        <TableCell className="text-right text-xl font-bold text-primary">
                                            {formatCurrency(calculateNetAmount())}
                                        </TableCell>
                                    </TableRow>
                                    {invoice.paid_amount > 0 && (
                                        <>
                                            <TableRow>
                                                <TableCell className="text-right font-semibold text-green-600">Amount Paid:</TableCell>
                                                <TableCell className="text-right font-semibold text-green-600">
                                                    {formatCurrency(invoice.paid_amount)}
                                                </TableCell>
                                            </TableRow>
                                            <TableRow className="border-t">
                                                <TableCell className="text-right text-xl font-bold">BALANCE DUE:</TableCell>
                                                <TableCell className="text-right text-xl font-bold text-red-600">
                                                    {formatCurrency(calculateBalanceDue())}
                                                </TableCell>
                                            </TableRow>
                                        </>
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        {/* Payment Status Message */}
                        {invoice.status === 'paid' && (
                            <Card className="mb-8 border-green-200 bg-green-50">
                                <CardContent className="p-4">
                                    <p className="text-center font-semibold text-green-800">✓ This invoice has been fully paid. Thank you!</p>
                                </CardContent>
                            </Card>
                        )}

                        {invoice.status === 'partial' && (
                            <Card className="mb-8 border-yellow-200 bg-yellow-50">
                                <CardContent className="p-4">
                                    <p className="text-center font-semibold text-yellow-800">
                                        Partial payment received. Balance of {formatCurrency(calculateBalanceDue())} is still due.
                                    </p>
                                </CardContent>
                            </Card>
                        )}

                        {invoice.status === 'pending' && (
                            <Card className="mb-8 border-red-200 bg-red-50">
                                <CardContent className="p-4">
                                    <p className="font.semibold text-center text-red-800">
                                        Payment pending. Please pay {formatCurrency(calculateBalanceDue())} by {formatDate(invoice.due_date)}.
                                    </p>
                                </CardContent>
                            </Card>
                        )}

                        {/* Payment Instructions */}
                        <Card className="mb-8 bg-muted/30">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <FileText className="h-5 w-5" />
                                    Payment Instructions
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 text-sm">
                                <p>
                                    <strong className="font-semibold">Payment Methods:</strong> {settings.payment_methods}
                                </p>
                                <p>
                                    <strong className="font-semibold">Business Hours:</strong> {settings.payment_hours}
                                </p>
                                <p>
                                    <strong className="font-semibold">Location:</strong> {settings.payment_location}
                                </p>
                                <p className="italic">{settings.payment_note}</p>
                            </CardContent>
                        </Card>

                        {/* Action Buttons */}
                        <div className="no-print flex justify-end gap-3">
                            <Button variant="outline" className="flex items-center gap-2" onClick={handlePrint}>
                                <Printer className="h-4 w-4" />
                                Print
                            </Button>
                            <Button className="flex items-center gap-2" onClick={handleDownloadPDF}>
                                <Download className="h-4 w-4" />
                                Download as PDF
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

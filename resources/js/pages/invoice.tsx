import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Head, Link } from '@inertiajs/react';
import { AlertCircle, Building2, Calendar, Download, FileText, Mail, Phone, Printer } from 'lucide-react';
import { useRef } from 'react';
import PageLayout from '../components/PageLayout';

interface Student {
    student_id: string;
    first_name: string;
    middle_name?: string;
    last_name: string;
    grade_level: string;
    section?: string;
}

interface Enrollment {
    enrollment_id: string;
    student: Student;
    school_year: string;
    semester?: string;
    tuition_fee: number;
    miscellaneous_fee: number;
    laboratory_fee: number;
    library_fee: number;
    sports_fee: number;
    total_amount: number;
    discount: number;
    net_amount: number;
    amount_paid: number;
    balance: number;
    payment_status: string;
    payment_due_date?: string;
    created_at: string;
}

interface Props {
    enrollment?: Enrollment;
    invoiceNumber: string;
    currentDate: string;
}

export default function Invoice({ enrollment, invoiceNumber, currentDate }: Props) {
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

    const schoolInfo = {
        name: 'Christian Bible Heritage Learning Center',
        address: '123 School St, City, Country',
        phone: '(02) 123-4567',
        email: 'info@cbhlc.edu',
    };

    const handlePrint = () => {
        const printContent = invoiceRef.current;
        if (!printContent) return;

        const printWindow = window.open('', '', 'height=600,width=800');
        if (!printWindow) return;

        printWindow.document.write('<html><head><title>Invoice</title>');
        printWindow.document.write('<style>');
        printWindow.document.write(`
            body { font-family: system-ui, -apple-system, sans-serif; padding: 20px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 8px; text-align: left; }
            th { border-bottom: 2px solid #333; }
            td { border-bottom: 1px solid #ddd; }
            .invoice-header { margin-bottom: 30px; }
            .invoice-title { font-size: 24px; font-weight: bold; }
            .school-name { font-size: 20px; font-weight: bold; color: #333; }
            .payment-instructions { background-color: #f5f5f5; padding: 15px; margin-top: 30px; }
            .no-print { display: none !important; }
            @media print {
                body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
                .no-print { display: none !important; }
            }
        `);
        printWindow.document.write('</style></head><body>');

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
        }, 250);
    };

    const handleDownloadPDF = () => {
        // For now, we'll use the browser's print to PDF functionality
        // In production, this should call a backend endpoint that generates a proper PDF
        window.print();
    };

    if (!enrollment) {
        return (
            <>
                <Head title="Invoice" />
                <PageLayout title="INVOICE" currentPage="invoice">
                    <Card className="mx-auto max-w-4xl">
                        <CardContent className="p-8">
                            <div className="flex flex-col items-center justify-center space-y-4 py-12">
                                <AlertCircle className="h-12 w-12 text-yellow-500" />
                                <h2 className="text-2xl font-semibold">No Invoice Available</h2>
                                <p className="text-center text-muted-foreground">
                                    No enrollment record found. Please complete the enrollment process first.
                                </p>
                                <Link href="/enrollment" className="mt-4 inline-block text-primary hover:underline">
                                    Go to Enrollment →
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </PageLayout>
            </>
        );
    }

    const invoiceItems = [
        { description: 'Tuition Fee', amount: enrollment.tuition_fee },
        { description: 'Miscellaneous Fee', amount: enrollment.miscellaneous_fee },
    ];

    // Add optional fees if they exist
    if (enrollment.laboratory_fee > 0) {
        invoiceItems.push({ description: 'Laboratory Fee', amount: enrollment.laboratory_fee });
    }
    if (enrollment.library_fee > 0) {
        invoiceItems.push({ description: 'Library Fee', amount: enrollment.library_fee });
    }
    if (enrollment.sports_fee > 0) {
        invoiceItems.push({ description: 'Sports Fee', amount: enrollment.sports_fee });
    }

    const getStudentFullName = () => {
        const middle = enrollment.student.middle_name ? ` ${enrollment.student.middle_name}` : '';
        return `${enrollment.student.first_name}${middle} ${enrollment.student.last_name}`;
    };

    const getPaymentStatusBadge = (status: string) => {
        switch (status) {
            case 'paid':
                return <Badge className="bg-green-100 text-green-800">PAID</Badge>;
            case 'partial':
                return <Badge className="bg-yellow-100 text-yellow-800">PARTIAL</Badge>;
            default:
                return <Badge className="bg-red-100 text-red-800">PENDING</Badge>;
        }
    };

    return (
        <>
            <Head title="Invoice" />
            <style>{`
                @media print {
                    .no-print { display: none !important; }
                    body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
                }
            `}</style>
            <PageLayout title="INVOICE" currentPage="invoice">
                <Card className="mx-auto max-w-4xl">
                    <CardContent className="p-8" ref={invoiceRef}>
                        {/* Invoice Header */}
                        <div className="mb-8 flex items-start justify-between">
                            <div className="flex items-center gap-4">
                                <div className="flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
                                    <Building2 className="h-8 w-8 text-primary" />
                                </div>
                                <div>
                                    <h1 className="text-2xl font-bold text-primary">{schoolInfo.name}</h1>
                                    <p className="text-sm text-muted-foreground">{schoolInfo.address}</p>
                                    <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                        <span className="flex items-center gap-1">
                                            <Phone className="h-3 w-3" />
                                            {schoolInfo.phone}
                                        </span>
                                        <span className="flex items-center gap-1">
                                            <Mail className="h-3 w-3" />
                                            {schoolInfo.email}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div className="text-right">
                                <div className="mb-4">
                                    <h2 className="text-3xl font-bold">INVOICE</h2>
                                    <Badge variant="outline" className="mt-1">
                                        {invoiceNumber}
                                    </Badge>
                                </div>
                                <div className="mt-2">{getPaymentStatusBadge(enrollment.payment_status)}</div>
                            </div>
                        </div>

                        <Separator className="mb-8" />

                        {/* Invoice Details */}
                        <div className="mb-8 grid gap-8 md:grid-cols-2">
                            <div>
                                <h3 className="mb-4 text-lg font-semibold">Billed To:</h3>
                                <div className="space-y-2">
                                    <p className="font-medium">{getStudentFullName()}</p>
                                    <p className="text-sm text-muted-foreground">Student ID: {enrollment.student.student_id}</p>
                                    <p className="text-sm text-muted-foreground">Grade Level: {enrollment.student.grade_level}</p>
                                    {enrollment.student.section && (
                                        <p className="text-sm text-muted-foreground">Section: {enrollment.student.section}</p>
                                    )}
                                    <p className="text-sm text-muted-foreground">School Year: {enrollment.school_year}</p>
                                    {enrollment.semester && <p className="text-sm text-muted-foreground">Semester: {enrollment.semester}</p>}
                                </div>
                            </div>

                            <div className="text-right">
                                <div className="space-y-2">
                                    <div className="flex justify-between">
                                        <span className="font-medium">Invoice Date:</span>
                                        <span className="flex items-center gap-1">
                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                            {currentDate}
                                        </span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="font-medium">Due Date:</span>
                                        <span className="flex items-center gap-1">
                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                            {formatDate(enrollment.payment_due_date)}
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
                                    {invoiceItems.map((item, index) => (
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
                                        <TableCell className="text-right font-semibold">{formatCurrency(enrollment.total_amount)}</TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell className="text-right font-semibold">Less: Discounts & Rebates:</TableCell>
                                        <TableCell className="text-right font-semibold">
                                            {enrollment.discount > 0 ? `-${formatCurrency(enrollment.discount)}` : formatCurrency(0)}
                                        </TableCell>
                                    </TableRow>
                                    <TableRow className="border-t-2">
                                        <TableCell className="text-right text-xl font-bold">NET AMOUNT:</TableCell>
                                        <TableCell className="text-right text-xl font-bold text-primary">
                                            {formatCurrency(enrollment.net_amount)}
                                        </TableCell>
                                    </TableRow>
                                    {enrollment.amount_paid > 0 && (
                                        <>
                                            <TableRow>
                                                <TableCell className="text-right font-semibold text-green-600">Amount Paid:</TableCell>
                                                <TableCell className="text-right font-semibold text-green-600">
                                                    {formatCurrency(enrollment.amount_paid)}
                                                </TableCell>
                                            </TableRow>
                                            <TableRow className="border-t">
                                                <TableCell className="text-right text-xl font-bold">BALANCE DUE:</TableCell>
                                                <TableCell className="text-right text-xl font-bold text-red-600">
                                                    {formatCurrency(enrollment.balance)}
                                                </TableCell>
                                            </TableRow>
                                        </>
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        {/* Payment Status Message */}
                        {enrollment.payment_status === 'paid' && (
                            <Card className="mb-8 border-green-200 bg-green-50">
                                <CardContent className="p-4">
                                    <p className="text-center font-semibold text-green-800">✓ This invoice has been fully paid. Thank you!</p>
                                </CardContent>
                            </Card>
                        )}

                        {enrollment.payment_status === 'partial' && (
                            <Card className="mb-8 border-yellow-200 bg-yellow-50">
                                <CardContent className="p-4">
                                    <p className="text-center font-semibold text-yellow-800">
                                        Partial payment received. Balance of {formatCurrency(enrollment.balance)} is still due.
                                    </p>
                                </CardContent>
                            </Card>
                        )}

                        {enrollment.payment_status === 'pending' && (
                            <Card className="mb-8 border-red-200 bg-red-50">
                                <CardContent className="p-4">
                                    <p className="text-center font-semibold text-red-800">
                                        Payment pending. Please pay {formatCurrency(enrollment.balance)} by {formatDate(enrollment.payment_due_date)}.
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
                                    Please pay the total amount by the due date to avoid penalties. Payments must be made in person at the school
                                    cashier's office during business hours.
                                </p>
                                <p>
                                    <strong>Payment Methods:</strong> Cash or Check only (Face-to-face payment required)
                                </p>
                                <p>
                                    <strong>Business Hours:</strong> Monday to Friday, 8:00 AM - 5:00 PM
                                </p>
                                <p>
                                    <strong>Location:</strong> School Cashier's Office, Ground Floor, Administration Building
                                </p>
                                <p className="font-medium">Thank you for your prompt payment!</p>
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
                                Download PDF
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </PageLayout>
        </>
    );
}

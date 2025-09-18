import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Head } from '@inertiajs/react';
import { Building2, Calendar, Download, FileText, Mail, Phone, Printer } from 'lucide-react';
import PageLayout from '../components/PageLayout';

interface InvoiceItem {
    description: string;
    amount: string;
}

export default function Invoice() {
    const invoiceItems: InvoiceItem[] = [
        { description: 'Tuition Fee (Monthly)', amount: '₱10,000.00' },
        { description: 'Miscellaneous Fee', amount: '₱3,000.00' },
    ];

    const subtotal = '₱13,000.00';
    const discount = '₱0.00';
    const totalDue = '₱13,000.00';

    const schoolInfo = {
        name: 'Christian Bible Heritage Learning Center',
        address: '123 School St, City, Country',
        phone: '(02) 123-4567',
        email: 'info@cbhlc.edu',
    };

    const studentInfo = {
        name: 'Bronny James',
        gradeLevel: 'Grade 2',
    };

    const invoiceDetails = {
        number: 'INV-00123',
        date: 'October 20, 2023',
        dueDate: 'October 31, 2023',
    };

    return (
        <>
            <Head title="Invoice" />
            <PageLayout title="INVOICE" currentPage="invoice">
                <Card className="mx-auto max-w-4xl">
                    <CardContent className="p-8">
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
                                        {invoiceDetails.number}
                                    </Badge>
                                </div>
                            </div>
                        </div>

                        <Separator className="mb-8" />

                        {/* Invoice Details */}
                        <div className="mb-8 grid gap-8 md:grid-cols-2">
                            <div>
                                <h3 className="mb-4 text-lg font-semibold">Billed To:</h3>
                                <div className="space-y-2">
                                    <p className="font-medium">{studentInfo.name}</p>
                                    <p className="text-sm text-muted-foreground">Grade Level: {studentInfo.gradeLevel}</p>
                                </div>
                            </div>

                            <div className="text-right">
                                <div className="space-y-2">
                                    <div className="flex justify-between">
                                        <span className="font-medium">Invoice Date:</span>
                                        <span className="flex items-center gap-1">
                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                            {invoiceDetails.date}
                                        </span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="font-medium">Due Date:</span>
                                        <span className="flex items-center gap-1">
                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                            {invoiceDetails.dueDate}
                                        </span>
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
                                            <TableCell className="text-right font-semibold">{item.amount}</TableCell>
                                        </TableRow>
                                    ))}
                                    <TableRow>
                                        <TableCell colSpan={2} className="h-4" />
                                    </TableRow>
                                    <TableRow>
                                        <TableCell className="text-right font-semibold">Subtotal:</TableCell>
                                        <TableCell className="text-right font-semibold">{subtotal}</TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell className="text-right font-semibold">Less: Discounts & Rebates:</TableCell>
                                        <TableCell className="text-right font-semibold">{discount}</TableCell>
                                    </TableRow>
                                    <TableRow className="border-t-2">
                                        <TableCell className="text-right text-xl font-bold">TOTAL DUE:</TableCell>
                                        <TableCell className="text-right text-xl font-bold text-primary">{totalDue}</TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

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
                                    Please pay the total amount by the due date to avoid penalties. Payments can be made via online transfer or bank
                                    deposit to our official school accounts.
                                </p>
                                <p className="font-medium">Thank you for your prompt payment!</p>
                            </CardContent>
                        </Card>

                        {/* Action Buttons */}
                        <div className="flex justify-end gap-3">
                            <Button variant="outline" className="flex items-center gap-2">
                                <Printer className="h-4 w-4" />
                                Print
                            </Button>
                            <Button className="flex items-center gap-2">
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

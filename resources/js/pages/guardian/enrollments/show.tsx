import { EnrollmentStatusBadge, PaymentStatusBadge } from '@/components/status-badges';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Download } from 'lucide-react';

interface Enrollment {
    id: number;
    student: {
        first_name: string;
        last_name: string;
        student_id: string;
        birth_date: string;
        gender: string;
        address: string;
        phone: string;
    };
    school_year: string;
    grade_level: string;
    section: string | null;
    adviser: string | null;
    quarter: string;
    status: 'pending' | 'approved' | 'enrolled' | 'rejected' | 'completed';
    payment_status: 'pending' | 'partial' | 'paid' | 'overdue';
    tuition_fee_cents: number;
    miscellaneous_fee_cents: number;
    laboratory_fee_cents: number;
    library_fee_cents: number;
    other_fees_cents: number;
    total_amount_cents: number;
    discount_cents: number;
    net_amount_cents: number;
    amount_paid_cents: number;
    balance_cents: number;
    created_at: string;
}

interface Payment {
    id: number;
    payment_date: string;
    amount: number;
    payment_method: string;
    reference_number: string | null;
    balance_after_cents: number;
}

interface Props {
    enrollment: Enrollment;
    payments: Payment[];
}

const formatCurrency = (cents: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(cents / 100);
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    }).format(date);
};

export default function GuardianEnrollmentsShow({ enrollment, payments }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Guardian', href: '/guardian/dashboard' },
        { title: 'Enrollments', href: '/guardian/enrollments' },
        { title: `Enrollment #${enrollment.id}`, href: `/guardian/enrollments/${enrollment.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Enrollment #${enrollment.id}`} />
            <div className="px-4 py-6">
                <div className="mb-4 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Enrollment Details</h1>
                    {enrollment.status === 'enrolled' && (
                        <Button variant="default" asChild>
                            <a href={`/guardian/enrollments/${enrollment.id}/certificate`} download>
                                <Download className="mr-2 h-4 w-4" />
                                Download Certificate
                            </a>
                        </Button>
                    )}
                </div>

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card>
                        <CardHeader>
                            <CardTitle>Enrollment Information</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Enrollment ID</p>
                                <p className="text-lg font-semibold">{enrollment.id}</p>
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Status</p>
                                <EnrollmentStatusBadge status={enrollment.status} />
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">School Year</p>
                                <p className="text-lg font-semibold">{enrollment.school_year}</p>
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Grade Level</p>
                                <p className="text-lg font-semibold">{enrollment.grade_level}</p>
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Quarter</p>
                                <p className="text-lg font-semibold">{enrollment.quarter}</p>
                            </div>
                            {enrollment.section && (
                                <div className="flex items-center justify-between">
                                    <p className="text-sm font-medium text-muted-foreground">Section</p>
                                    <p className="text-lg font-semibold">{enrollment.section}</p>
                                </div>
                            )}
                            {enrollment.adviser && (
                                <div className="flex items-center justify-between">
                                    <p className="text-sm font-medium text-muted-foreground">Adviser</p>
                                    <p className="text-lg font-semibold">{enrollment.adviser}</p>
                                </div>
                            )}
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Submission Date</p>
                                <p className="text-sm font-semibold">{formatDate(enrollment.created_at)}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Student Information</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Student Name</p>
                                <p className="text-lg font-semibold">{`${enrollment.student.first_name} ${enrollment.student.last_name}`}</p>
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Student ID</p>
                                <p className="text-sm">{enrollment.student.student_id}</p>
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Birthdate</p>
                                <p className="text-sm">
                                    {new Date(enrollment.student.birth_date).toLocaleDateString('en-US', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                    })}
                                </p>
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Gender</p>
                                <p className="text-sm">{enrollment.student.gender}</p>
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Contact Number</p>
                                <p className="text-sm">{enrollment.student.phone}</p>
                            </div>
                            <div className="flex flex-col gap-1">
                                <p className="text-sm font-medium text-muted-foreground">Address</p>
                                <p className="text-sm">{enrollment.student.address}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle>Payment Information</CardTitle>
                            <Button variant="outline" size="sm" asChild>
                                <a href={`/guardian/enrollments/${enrollment.id}/payment-history-pdf`} download>
                                    <Download className="mr-2 h-4 w-4" />
                                    Download Report
                                </a>
                            </Button>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Payment Status</p>
                                <PaymentStatusBadge status={enrollment.payment_status} />
                            </div>
                            <Separator />

                            {/* Fee Breakdown */}
                            <div className="space-y-2">
                                <p className="text-sm font-semibold">Fee Breakdown</p>
                                <div className="flex items-center justify-between text-sm">
                                    <p className="text-muted-foreground">Tuition Fee</p>
                                    <p>{formatCurrency(enrollment.tuition_fee_cents)}</p>
                                </div>
                                <div className="flex items-center justify-between text-sm">
                                    <p className="text-muted-foreground">Miscellaneous Fee</p>
                                    <p>{formatCurrency(enrollment.miscellaneous_fee_cents)}</p>
                                </div>
                                {enrollment.laboratory_fee_cents > 0 && (
                                    <div className="flex items-center justify-between text-sm">
                                        <p className="text-muted-foreground">Laboratory Fee</p>
                                        <p>{formatCurrency(enrollment.laboratory_fee_cents)}</p>
                                    </div>
                                )}
                                {enrollment.library_fee_cents > 0 && (
                                    <div className="flex items-center justify-between text-sm">
                                        <p className="text-muted-foreground">Library Fee</p>
                                        <p>{formatCurrency(enrollment.library_fee_cents)}</p>
                                    </div>
                                )}
                                {enrollment.other_fees_cents > 0 && (
                                    <div className="flex items-center justify-between text-sm">
                                        <p className="text-muted-foreground">Other Fees</p>
                                        <p>{formatCurrency(enrollment.other_fees_cents)}</p>
                                    </div>
                                )}
                            </div>
                            <Separator />

                            {/* Summary */}
                            <div className="space-y-2">
                                <p className="text-sm font-semibold">Summary</p>
                                <div className="flex items-center justify-between">
                                    <p className="text-sm font-medium text-muted-foreground">Total Amount Due</p>
                                    <p className="font-semibold">{formatCurrency(enrollment.total_amount_cents)}</p>
                                </div>
                                {enrollment.discount_cents > 0 && (
                                    <div className="flex items-center justify-between">
                                        <p className="text-sm font-medium text-muted-foreground">Discount</p>
                                        <p className="font-semibold text-green-600">-{formatCurrency(enrollment.discount_cents)}</p>
                                    </div>
                                )}
                                <div className="flex items-center justify-between">
                                    <p className="text-sm font-medium text-muted-foreground">Net Amount Due</p>
                                    <p className="text-lg font-bold">{formatCurrency(enrollment.net_amount_cents)}</p>
                                </div>
                                <div className="flex items-center justify-between">
                                    <p className="text-sm font-medium text-muted-foreground">Total Amount Paid</p>
                                    <p className="font-semibold">{formatCurrency(enrollment.amount_paid_cents)}</p>
                                </div>
                                <div className="flex items-center justify-between">
                                    <p className="text-sm font-medium text-muted-foreground">Outstanding Balance</p>
                                    <p className="text-lg font-bold">{formatCurrency(enrollment.balance_cents)}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {payments.length > 0 && (
                    <Card className="mt-4">
                        <CardHeader>
                            <CardTitle>Payment History</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Payment Date</TableHead>
                                        <TableHead>Amount</TableHead>
                                        <TableHead>Payment Method</TableHead>
                                        <TableHead>Reference Number</TableHead>
                                        <TableHead className="text-right">Balance After</TableHead>
                                        <TableHead className="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {payments.map((payment) => (
                                        <TableRow key={payment.id}>
                                            <TableCell>{formatDate(payment.payment_date)}</TableCell>
                                            <TableCell>{formatCurrency(payment.amount)}</TableCell>
                                            <TableCell className="capitalize">{payment.payment_method.replace('_', ' ')}</TableCell>
                                            <TableCell>{payment.reference_number || 'N/A'}</TableCell>
                                            <TableCell className="text-right">{formatCurrency(payment.balance_after_cents)}</TableCell>
                                            <TableCell className="text-right">
                                                <Button size="sm" variant="ghost" asChild>
                                                    <a href={`/payments/${payment.id}/receipt`} download>
                                                        <Download className="mr-1 h-3 w-3" />
                                                        Receipt
                                                    </a>
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}

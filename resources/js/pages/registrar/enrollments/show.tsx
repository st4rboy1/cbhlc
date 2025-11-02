import { DocumentStatusBadge, EnrollmentStatusBadge, PaymentStatusBadge } from '@/components/status-badges';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { formatCurrency } from '@/pages/registrar/enrollments/enrollments-table';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ExternalLink, FileText } from 'lucide-react';

interface Document {
    id: number;
    document_type: string;
    original_filename: string;
    file_path: string;
    verification_status: string;
    upload_date: string;
}

interface Enrollment {
    id: number;
    student: {
        first_name: string;
        last_name: string;
        student_id: string;
        documents: Document[];
    };
    guardian: { name: string };
    school_year: string;
    quarter: string;
    grade_level: string;
    status: string;
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
    payment_status: string;
}

interface Props {
    enrollment: Enrollment;
}

export default function RegistrarEnrollmentsShow({ enrollment }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Registrar', href: '/registrar/dashboard' },
        { title: 'Enrollments', href: '/registrar/enrollments' },
        { title: `Enrollment #${enrollment.id}`, href: `/registrar/enrollments/${enrollment.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Enrollment #${enrollment.id}`} />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Enrollment Details</h1>

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
                                <p className="text-sm font-medium text-muted-foreground">Quarter</p>
                                <p className="text-lg font-semibold">{enrollment.quarter}</p>
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Grade Level</p>
                                <p className="text-lg font-semibold">{enrollment.grade_level}</p>
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
                                <p className="text-lg font-semibold">{enrollment.student.student_id}</p>
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Guardian Name</p>
                                <p className="text-lg font-semibold">{enrollment.guardian?.name || 'N/A'}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Financial Information</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Payment Status</p>
                                <PaymentStatusBadge status={enrollment.payment_status} />
                            </div>
                            <Separator />
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
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Total Amount</p>
                                <p className="text-lg font-semibold">{formatCurrency(enrollment.total_amount_cents)}</p>
                            </div>
                            {enrollment.discount_cents > 0 && (
                                <div className="flex items-center justify-between">
                                    <p className="text-sm font-medium text-muted-foreground">Discount</p>
                                    <p className="text-lg font-semibold text-green-600">-{formatCurrency(enrollment.discount_cents)}</p>
                                </div>
                            )}
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Net Amount</p>
                                <p className="text-lg font-semibold">{formatCurrency(enrollment.net_amount_cents)}</p>
                            </div>
                            <Separator />
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Amount Paid</p>
                                <p className="text-lg font-semibold">{formatCurrency(enrollment.amount_paid_cents)}</p>
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Balance</p>
                                <p className="text-lg font-semibold">{formatCurrency(enrollment.balance_cents)}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {enrollment.student.documents && enrollment.student.documents.length > 0 && (
                    <Card className="mt-4">
                        <CardHeader>
                            <CardTitle>Uploaded Documents</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                {enrollment.student.documents.map((doc) => (
                                    <div key={doc.id} className="flex items-center justify-between rounded-lg border p-3">
                                        <div className="flex items-center gap-3">
                                            <FileText className="h-5 w-5 text-muted-foreground" />
                                            <div>
                                                <p className="font-medium">
                                                    {doc.document_type.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase())}
                                                </p>
                                                <p className="text-sm text-muted-foreground">{doc.original_filename}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <DocumentStatusBadge status={doc.verification_status} />
                                            <Link href={route('documents.view', { document: doc.id })} target="_blank">
                                                <Button variant="outline" size="sm">
                                                    <ExternalLink className="mr-2 h-4 w-4" />
                                                    View
                                                </Button>
                                            </Link>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}

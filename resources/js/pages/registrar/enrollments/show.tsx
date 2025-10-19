import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { formatCurrency, formatStatusName, getPaymentStatusVariant, getStatusVariant } from '@/pages/registrar/enrollments/enrollments-table';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Enrollment {
    id: number;
    student: { first_name: string; last_name: string; student_id: string };
    guardian: { name: string };
    school_year: string;
    quarter: string;
    grade_level: string;
    status: string;
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
                                <Badge variant={getStatusVariant(enrollment.status)}>{formatStatusName(enrollment.status)}</Badge>
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
                                <p className="text-lg font-semibold">{enrollment.guardian.name}</p>
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
                                <Badge variant={getPaymentStatusVariant(enrollment.payment_status)}>
                                    {formatStatusName(enrollment.payment_status)}
                                </Badge>
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Total Amount</p>
                                <p className="text-lg font-semibold">{formatCurrency(enrollment.net_amount_cents)}</p>
                            </div>
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
            </div>
        </AppLayout>
    );
}

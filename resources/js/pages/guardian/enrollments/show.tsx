import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { paymentStatusColors, statusColors } from '@/pages/guardian/enrollments/index';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Download } from 'lucide-react';

interface Enrollment {
    id: number;
    student: {
        first_name: string;
        last_name: string;
    };
    school_year: string;
    grade_level: string;
    status: 'pending' | 'approved' | 'enrolled' | 'rejected' | 'completed';
    payment_status: 'pending' | 'partial' | 'paid' | 'overdue';
    created_at: string;
}

interface Props {
    enrollment: Enrollment;
}

export default function GuardianEnrollmentsShow({ enrollment }: Props) {
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
                                <Badge variant={statusColors[enrollment.status] || 'default'}>{enrollment.status}</Badge>
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
                                <p className="text-sm font-medium text-muted-foreground">Submission Date</p>
                                <p className="text-lg font-semibold">{enrollment.created_at}</p>
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
                                <Badge variant={paymentStatusColors[enrollment.payment_status] || 'default'}>{enrollment.payment_status}</Badge>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}

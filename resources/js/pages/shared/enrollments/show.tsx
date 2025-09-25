import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { formatCurrency } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Edit } from 'lucide-react';
import { type FC } from 'react';

interface Props {
    enrollment: {
        id: number;
        enrollment_id: string;
        student: {
            id: number;
            first_name: string;
            middle_name?: string;
            last_name: string;
            student_id: string;
            birthdate?: string;
            gender?: string;
            address?: string;
            contact_number?: string;
        };
        guardian?: {
            id: number;
            name: string;
            email?: string;
        };
        school_year: string;
        grade_level: string;
        quarter: string;
        status: string;
        tuition_fee_cents: number;
        miscellaneous_fee_cents: number;
        laboratory_fee_cents: number;
        total_amount_cents: number;
        net_amount_cents: number;
        amount_paid_cents: number;
        balance_cents: number;
        payment_status: string;
        submitted_at?: string;
        reviewed_at?: string;
        approved_at?: string;
        notes?: string;
        created_at: string;
        updated_at: string;
    };
}

const EnrollmentShow: FC<Props> = ({ enrollment }) => {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Enrollments',
            href: '/enrollments',
        },
        {
            title: `Enrollment ${enrollment.enrollment_id}`,
            href: `/enrollments/${enrollment.id}`,
        },
    ];

    const getStatusBadge = (status: string) => {
        const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
            approved: 'default',
            pending: 'secondary',
            rejected: 'destructive',
            cancelled: 'outline',
        };
        return <Badge variant={variants[status.toLowerCase()] || 'secondary'}>{status}</Badge>;
    };

    const getPaymentBadge = (status: string) => {
        const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
            paid: 'default',
            partial: 'secondary',
            pending: 'destructive',
        };
        return <Badge variant={variants[status.toLowerCase()] || 'secondary'}>{status}</Badge>;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Enrollment ${enrollment.enrollment_id}`} />

            <div className="px-4 py-6">
                <Heading
                    title={`Enrollment ${enrollment.enrollment_id}`}
                    description={`Enrollment details for ${enrollment.student.first_name} ${enrollment.student.last_name}`}
                />

                <div className="space-y-6">
                    {/* Actions Bar */}
                    <div className="flex items-center justify-between">
                        <Button variant="ghost" asChild>
                            <Link href="/enrollments">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Back to Enrollments
                            </Link>
                        </Button>
                        {enrollment.status === 'pending' && (
                            <Button asChild>
                                <Link href={`/enrollments/${enrollment.id}/edit`}>
                                    <Edit className="mr-2 h-4 w-4" />
                                    Edit Enrollment
                                </Link>
                            </Button>
                        )}
                    </div>

                    {/* Enrollment Information */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle>Enrollment {enrollment.enrollment_id}</CardTitle>
                                    <CardDescription>School Year {enrollment.school_year}</CardDescription>
                                </div>
                                <div className="flex gap-2">
                                    {getStatusBadge(enrollment.status)}
                                    {getPaymentBadge(enrollment.payment_status)}
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Grade Level</p>
                                    <p className="text-sm">{enrollment.grade_level}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Quarter</p>
                                    <p className="text-sm">{enrollment.quarter}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Student Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Student Information</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Student ID</p>
                                    <p className="text-sm">{enrollment.student.student_id}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Name</p>
                                    <p className="text-sm">
                                        {enrollment.student.first_name} {enrollment.student.middle_name} {enrollment.student.last_name}
                                    </p>
                                </div>
                                {enrollment.student.birthdate && (
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Birthdate</p>
                                        <p className="text-sm">{enrollment.student.birthdate}</p>
                                    </div>
                                )}
                                {enrollment.student.gender && (
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Gender</p>
                                        <p className="text-sm">{enrollment.student.gender}</p>
                                    </div>
                                )}
                                {enrollment.student.contact_number && (
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Contact Number</p>
                                        <p className="text-sm">{enrollment.student.contact_number}</p>
                                    </div>
                                )}
                                {enrollment.student.address && (
                                    <div className="md:col-span-2">
                                        <p className="text-sm font-medium text-muted-foreground">Address</p>
                                        <p className="text-sm">{enrollment.student.address}</p>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Financial Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Financial Information</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div className="space-y-2">
                                    <div className="flex justify-between">
                                        <span className="text-sm">Tuition Fee</span>
                                        <span className="text-sm font-medium">{formatCurrency(enrollment.tuition_fee_cents / 100)}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm">Miscellaneous Fee</span>
                                        <span className="text-sm font-medium">{formatCurrency(enrollment.miscellaneous_fee_cents / 100)}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm">Laboratory Fee</span>
                                        <span className="text-sm font-medium">{formatCurrency(enrollment.laboratory_fee_cents / 100)}</span>
                                    </div>
                                </div>
                                <Separator />
                                <div className="space-y-2">
                                    <div className="flex justify-between">
                                        <span className="text-sm font-medium">Total Amount</span>
                                        <span className="text-sm font-medium">{formatCurrency(enrollment.total_amount_cents / 100)}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm">Amount Paid</span>
                                        <span className="text-sm font-medium">{formatCurrency(enrollment.amount_paid_cents / 100)}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm font-medium">Balance</span>
                                        <span className="text-sm font-medium text-destructive">{formatCurrency(enrollment.balance_cents / 100)}</span>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Timeline */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Timeline</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2">
                                {enrollment.submitted_at && (
                                    <div className="flex justify-between">
                                        <span className="text-sm text-muted-foreground">Submitted</span>
                                        <span className="text-sm">{new Date(enrollment.submitted_at).toLocaleString()}</span>
                                    </div>
                                )}
                                {enrollment.reviewed_at && (
                                    <div className="flex justify-between">
                                        <span className="text-sm text-muted-foreground">Reviewed</span>
                                        <span className="text-sm">{new Date(enrollment.reviewed_at).toLocaleString()}</span>
                                    </div>
                                )}
                                {enrollment.approved_at && (
                                    <div className="flex justify-between">
                                        <span className="text-sm text-muted-foreground">Approved</span>
                                        <span className="text-sm">{new Date(enrollment.approved_at).toLocaleString()}</span>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Notes */}
                    {enrollment.notes && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Notes</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-sm">{enrollment.notes}</p>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </AppLayout>
    );
};

export default EnrollmentShow;

import { EnrollmentStatusBadge, PaymentStatusBadge } from '@/components/status-badges';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { format } from 'date-fns';
import { ArrowLeft, BookOpen, Calendar, CreditCard, FileText, GraduationCap, Hash, User, Users } from 'lucide-react';

interface Student {
    id: number;
    student_id: string;
    first_name: string;
    last_name: string;
}

interface Guardian {
    id: number;
    first_name: string;
    last_name: string;
    user?: {
        name: string;
        email: string;
    };
}

interface Enrollment {
    id: number;
    enrollment_id: string;
    student_id: number;
    guardian_id: number;
    grade_level: string;
    quarter: string;
    school_year: string;
    status: string;
    type: string;
    previous_school: string | null;
    payment_plan: string;
    tuition_fee_cents: number;
    miscellaneous_fee_cents: number;
    laboratory_fee_cents: number;
    library_fee_cents: number;
    sports_fee_cents: number;
    total_amount_cents: number;
    discount_cents: number;
    net_amount_cents: number;
    amount_paid_cents: number;
    balance_cents: number;
    payment_status: string;
    created_at: string;
    updated_at: string;
    approved_at: string | null;
    rejected_at: string | null;
    student: Student;
    guardian: Guardian;
}

interface Props {
    enrollment: Enrollment;
}

const formatCurrency = (cents: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(cents / 100);
};

export default function SuperAdminEnrollmentsShow({ enrollment }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Enrollments', href: '/super-admin/enrollments' },
        { title: enrollment.enrollment_id, href: `/super-admin/enrollments/${enrollment.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Enrollment ${enrollment.enrollment_id}`} />
            <div className="container mx-auto px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href="/super-admin/enrollments">
                            <Button variant="outline" size="icon">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold">Enrollment Details</h1>
                            <p className="text-sm text-muted-foreground">ID: {enrollment.enrollment_id}</p>
                        </div>
                    </div>
                    <Link href={`/super-admin/enrollments/${enrollment.id}/edit`}>
                        <Button>Edit Enrollment</Button>
                    </Link>
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    {/* Main Content */}
                    <div className="space-y-6 lg:col-span-2">
                        {/* Enrollment Information */}
                        <Card className="p-6">
                            <h2 className="mb-4 text-lg font-semibold">Enrollment Information</h2>
                            <div className="grid gap-6 md:grid-cols-2">
                                <div className="space-y-1">
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <Hash className="h-4 w-4" />
                                        Enrollment ID
                                    </div>
                                    <p className="text-lg font-bold">{enrollment.enrollment_id}</p>
                                </div>
                                <div className="space-y-1">
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <FileText className="h-4 w-4" />
                                        Status
                                    </div>
                                    <div>
                                        <EnrollmentStatusBadge status={enrollment.status} />
                                    </div>
                                </div>
                                <div className="space-y-1">
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <GraduationCap className="h-4 w-4" />
                                        Grade Level
                                    </div>
                                    <p className="text-lg font-medium">{enrollment.grade_level}</p>
                                </div>
                                <div className="space-y-1">
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <Calendar className="h-4 w-4" />
                                        Quarter
                                    </div>
                                    <p className="text-lg font-medium">{enrollment.quarter}</p>
                                </div>
                                <div className="space-y-1">
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <BookOpen className="h-4 w-4" />
                                        School Year
                                    </div>
                                    <p className="text-lg font-medium">{enrollment.school_year}</p>
                                </div>
                                <div className="space-y-1">
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <CreditCard className="h-4 w-4" />
                                        Payment Status
                                    </div>
                                    <div>
                                        <PaymentStatusBadge status={enrollment.payment_status} />
                                    </div>
                                </div>
                                <div className="space-y-1">
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <FileText className="h-4 w-4" />
                                        Enrollment Type
                                    </div>
                                    <p className="text-lg font-medium capitalize">{enrollment.type.replace('_', ' ')}</p>
                                </div>
                                {enrollment.previous_school && (
                                    <div className="space-y-1">
                                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                            <BookOpen className="h-4 w-4" />
                                            Previous School
                                        </div>
                                        <p className="text-lg font-medium">{enrollment.previous_school}</p>
                                    </div>
                                )}
                                <div className="space-y-1">
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <CreditCard className="h-4 w-4" />
                                        Payment Plan
                                    </div>
                                    <p className="text-lg font-medium capitalize">{enrollment.payment_plan}</p>
                                </div>
                            </div>
                        </Card>

                        {/* Financial Information */}
                        <Card className="p-6">
                            <h2 className="mb-4 text-lg font-semibold">Financial Information</h2>
                            <div className="space-y-4">
                                <div className="flex items-center justify-between">
                                    <span className="text-sm text-muted-foreground">Tuition Fee</span>
                                    <span className="font-medium">{formatCurrency(enrollment.tuition_fee_cents)}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm text-muted-foreground">Miscellaneous Fee</span>
                                    <span className="font-medium">{formatCurrency(enrollment.miscellaneous_fee_cents)}</span>
                                </div>
                                {enrollment.laboratory_fee_cents > 0 && (
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-muted-foreground">Laboratory Fee</span>
                                        <span className="font-medium">{formatCurrency(enrollment.laboratory_fee_cents)}</span>
                                    </div>
                                )}
                                {enrollment.library_fee_cents > 0 && (
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-muted-foreground">Library Fee</span>
                                        <span className="font-medium">{formatCurrency(enrollment.library_fee_cents)}</span>
                                    </div>
                                )}
                                {enrollment.sports_fee_cents > 0 && (
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-muted-foreground">Sports Fee</span>
                                        <span className="font-medium">{formatCurrency(enrollment.sports_fee_cents)}</span>
                                    </div>
                                )}
                                <Separator />
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-medium">Total Amount</span>
                                    <span className="font-bold">{formatCurrency(enrollment.total_amount_cents)}</span>
                                </div>
                                {enrollment.discount_cents > 0 && (
                                    <>
                                        <div className="flex items-center justify-between">
                                            <span className="text-sm text-muted-foreground">Discount</span>
                                            <span className="font-medium text-green-600">-{formatCurrency(enrollment.discount_cents)}</span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-sm font-medium">Net Amount</span>
                                            <span className="font-bold">{formatCurrency(enrollment.net_amount_cents)}</span>
                                        </div>
                                    </>
                                )}
                                <Separator />
                                <div className="flex items-center justify-between">
                                    <span className="text-sm text-muted-foreground">Amount Paid</span>
                                    <span className="font-medium text-green-600">{formatCurrency(enrollment.amount_paid_cents)}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-bold">Balance</span>
                                    <span className="text-lg font-bold text-primary">{formatCurrency(enrollment.balance_cents)}</span>
                                </div>
                            </div>
                        </Card>
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Student Information */}
                        <Card className="p-6">
                            <div className="mb-4 flex items-center gap-2">
                                <User className="h-4 w-4 text-muted-foreground" />
                                <h2 className="text-lg font-semibold">Student Information</h2>
                            </div>
                            <div className="space-y-3">
                                <div>
                                    <p className="text-sm text-muted-foreground">Name</p>
                                    <p className="font-medium">
                                        {enrollment.student.first_name} {enrollment.student.last_name}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Student ID</p>
                                    <p className="font-medium">{enrollment.student.student_id}</p>
                                </div>
                            </div>
                        </Card>

                        {/* Guardian Information */}

                        <Card className="p-6">
                            <div className="mb-4 flex items-center gap-2">
                                <Users className="h-4 w-4 text-muted-foreground" />
                                <h2 className="text-lg font-semibold">Guardian Information</h2>
                            </div>
                            <div className="space-y-3">
                                <div>
                                    <p className="text-sm text-muted-foreground">Name</p>
                                    <p className="font-medium">
                                        {enrollment.guardian.first_name} {enrollment.guardian.last_name}
                                    </p>
                                </div>
                                {enrollment.guardian.user?.email && (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Email</p>
                                        <p className="text-sm font-medium">{enrollment.guardian.user.email}</p>
                                    </div>
                                )}
                            </div>
                        </Card>

                        {/* Metadata */}
                        <Card className="p-6">
                            <h2 className="mb-4 text-lg font-semibold">Metadata</h2>
                            <div className="space-y-3 text-sm">
                                <div>
                                    <p className="text-muted-foreground">Created</p>
                                    <p className="font-medium">{format(new Date(enrollment.created_at), 'MMM dd, yyyy HH:mm')}</p>
                                </div>
                                <div>
                                    <p className="text-muted-foreground">Last Updated</p>
                                    <p className="font-medium">{format(new Date(enrollment.updated_at), 'MMM dd, yyyy HH:mm')}</p>
                                </div>
                                {enrollment.approved_at && (
                                    <div>
                                        <p className="text-muted-foreground">Approved At</p>
                                        <p className="font-medium">{format(new Date(enrollment.approved_at), 'MMM dd, yyyy HH:mm')}</p>
                                    </div>
                                )}
                                {enrollment.rejected_at && (
                                    <div>
                                        <p className="text-muted-foreground">Rejected At</p>
                                        <p className="font-medium">{format(new Date(enrollment.rejected_at), 'MMM dd, yyyy HH:mm')}</p>
                                    </div>
                                )}
                            </div>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

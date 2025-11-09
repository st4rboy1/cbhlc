import { EnrollmentPeriodStatusBadge } from '@/components/status-badges';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { format } from 'date-fns';
import { Calendar, CheckCircle2, Edit, Trash2, XCircle } from 'lucide-react';
import { toast } from 'sonner';

export type EnrollmentPeriod = {
    id: number;
    school_year: string;
    status: string;
    start_date: string;
    end_date: string;
    early_registration_deadline: string | null;
    regular_registration_deadline: string;
    late_registration_deadline: string | null;
    description: string | null;
    allow_new_students: boolean;
    allow_returning_students: boolean;
    enrollments_count?: number;
};

interface Props {
    period: EnrollmentPeriod;
}

export default function EnrollmentPeriodShow({ period }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Registrar', href: '/registrar/dashboard' },
        { title: 'Enrollment Periods', href: '/registrar/enrollment-periods' },
        { title: period.school_year, href: `/registrar/enrollment-periods/${period.id}` },
    ];

    const handleActivate = () => {
        if (confirm('Are you sure you want to activate this enrollment period? This will close any currently active period.')) {
            router.post(
                `/registrar/enrollment-periods/${period.id}/activate`,
                {},
                {
                    onSuccess: () => {
                        toast.success('Enrollment period activated successfully');
                    },
                    onError: () => {
                        toast.error('Failed to activate enrollment period');
                    },
                },
            );
        }
    };

    const handleClose = () => {
        if (confirm('Are you sure you want to close this enrollment period? This action cannot be undone.')) {
            router.post(
                `/registrar/enrollment-periods/${period.id}/close`,
                {},
                {
                    onSuccess: () => {
                        toast.success('Enrollment period closed successfully');
                    },
                    onError: () => {
                        toast.error('Failed to close enrollment period');
                    },
                },
            );
        }
    };

    const handleDelete = () => {
        if (confirm('Are you sure you want to delete this enrollment period? This action cannot be undone.')) {
            router.delete(`/registrar/enrollment-periods/${period.id}`, {
                onSuccess: () => {
                    toast.success('Enrollment period deleted successfully');
                },
                onError: (errors) => {
                    const errorMessage = errors.period || 'Failed to delete enrollment period';
                    toast.error(errorMessage);
                },
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Enrollment Period ${period.school_year}`} />
            <div className="px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Enrollment Period Details</h1>
                        <p className="text-muted-foreground">View enrollment period information and settings</p>
                    </div>
                    <div className="flex gap-2">
                        {period.status === 'upcoming' && (
                            <Button onClick={handleActivate}>
                                <CheckCircle2 className="mr-2 h-4 w-4" />
                                Activate
                            </Button>
                        )}
                        {period.status === 'active' && (
                            <Button variant="outline" onClick={handleClose}>
                                <XCircle className="mr-2 h-4 w-4" />
                                Close
                            </Button>
                        )}
                        <Link href={`/registrar/enrollment-periods/${period.id}/edit`}>
                            <Button variant="outline">
                                <Edit className="mr-2 h-4 w-4" />
                                Edit
                            </Button>
                        </Link>
                        {period.status !== 'active' && (period.enrollments_count || 0) === 0 && (
                            <Button variant="destructive" onClick={handleDelete}>
                                <Trash2 className="mr-2 h-4 w-4" />
                                Delete
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid gap-6">
                    {/* Status Card */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle className="flex items-center gap-2">
                                    <Calendar className="h-5 w-5" />
                                    {period.school_year}
                                </CardTitle>
                                <EnrollmentPeriodStatusBadge status={period.status} />
                            </div>
                            <CardDescription>School year enrollment period</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-6 md:grid-cols-2">
                                <div>
                                    <h3 className="mb-4 font-semibold">Period Information</h3>
                                    <dl className="space-y-2 text-sm">
                                        <div className="flex justify-between">
                                            <dt className="text-muted-foreground">Start Date:</dt>
                                            <dd className="font-medium">{format(new Date(period.start_date), 'MMMM d, yyyy')}</dd>
                                        </div>
                                        <div className="flex justify-between">
                                            <dt className="text-muted-foreground">End Date:</dt>
                                            <dd className="font-medium">{format(new Date(period.end_date), 'MMMM d, yyyy')}</dd>
                                        </div>
                                        <div className="flex justify-between">
                                            <dt className="text-muted-foreground">Total Enrollments:</dt>
                                            <dd className="font-medium">{period.enrollments_count || 0}</dd>
                                        </div>
                                    </dl>
                                </div>

                                <div>
                                    <h3 className="mb-4 font-semibold">Registration Deadlines</h3>
                                    <dl className="space-y-2 text-sm">
                                        {period.early_registration_deadline && (
                                            <div className="flex justify-between">
                                                <dt className="text-muted-foreground">Early Registration:</dt>
                                                <dd className="font-medium">
                                                    {format(new Date(period.early_registration_deadline), 'MMMM d, yyyy')}
                                                </dd>
                                            </div>
                                        )}
                                        <div className="flex justify-between">
                                            <dt className="text-muted-foreground">Regular Registration:</dt>
                                            <dd className="font-medium">{format(new Date(period.regular_registration_deadline), 'MMMM d, yyyy')}</dd>
                                        </div>
                                        {period.late_registration_deadline && (
                                            <div className="flex justify-between">
                                                <dt className="text-muted-foreground">Late Registration:</dt>
                                                <dd className="font-medium">{format(new Date(period.late_registration_deadline), 'MMMM d, yyyy')}</dd>
                                            </div>
                                        )}
                                    </dl>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Settings Card */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Enrollment Settings</CardTitle>
                            <CardDescription>Student type enrollment permissions</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="flex items-center justify-between rounded-lg border p-4">
                                    <div>
                                        <p className="font-medium">New Students</p>
                                        <p className="text-sm text-muted-foreground">First-time enrollees</p>
                                    </div>
                                    <Badge variant={period.allow_new_students ? 'default' : 'secondary'}>
                                        {period.allow_new_students ? 'Allowed' : 'Not Allowed'}
                                    </Badge>
                                </div>

                                <div className="flex items-center justify-between rounded-lg border p-4">
                                    <div>
                                        <p className="font-medium">Returning Students</p>
                                        <p className="text-sm text-muted-foreground">Previously enrolled</p>
                                    </div>
                                    <Badge variant={period.allow_returning_students ? 'default' : 'secondary'}>
                                        {period.allow_returning_students ? 'Allowed' : 'Not Allowed'}
                                    </Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Description Card */}
                    {period.description && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Description</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-sm whitespace-pre-wrap text-muted-foreground">{period.description}</p>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

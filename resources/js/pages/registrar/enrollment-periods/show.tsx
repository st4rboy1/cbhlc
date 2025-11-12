import { EnrollmentPeriodStatusBadge } from '@/components/status-badges';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { format } from 'date-fns';
import { Calendar } from 'lucide-react';

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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Enrollment Period ${period.school_year}`} />
            <div className="px-4 py-6">
                <div className="mb-6">
                    <h1 className="text-2xl font-bold">Enrollment Period Details</h1>
                    <p className="text-muted-foreground">View enrollment period information and settings</p>
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

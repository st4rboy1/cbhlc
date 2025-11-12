import { EnrollmentPeriodStatusBadge } from '@/components/status-badges';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { format } from 'date-fns';
import { Calendar } from 'lucide-react';
import { useEffect } from 'react';
import { toast } from 'sonner';

export type EnrollmentPeriod = {
    id: number;
    school_year_id: number;
    school_year: {
        id: number;
        name: string;
        start_year: number;
        end_year: number;
        status: string;
    };
    status: string;
    start_date: string;
    end_date: string;
    early_registration_deadline: string | null;
    regular_registration_deadline: string;
    late_registration_deadline: string | null;
    allow_new_students: boolean;
    allow_returning_students: boolean;
    enrollments_count?: number;
};

interface PaginatedEnrollmentPeriods {
    current_page: number;
    data: EnrollmentPeriod[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: { url: string | null; label: string; active: boolean }[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

interface Props {
    periods: PaginatedEnrollmentPeriods;
    activePeriod: EnrollmentPeriod | null;
}

export default function EnrollmentPeriodsIndex({ periods, activePeriod }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Registrar', href: '/registrar/dashboard' },
        { title: 'Enrollment Periods', href: '/registrar/enrollment-periods' },
    ];

    useEffect(() => {
        // Show success message if redirected with success flash
        const urlParams = new URLSearchParams(window.location.search);
        const success = urlParams.get('success');
        if (success) {
            toast.success(success);
        }
    }, []);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Enrollment Periods" />
            <div className="px-4 py-6">
                <div className="mb-6">
                    <h1 className="text-2xl font-bold">Enrollment Periods</h1>
                    <p className="text-muted-foreground">View school year enrollment periods and registration deadlines</p>
                </div>

                {activePeriod && (
                    <Card className="mb-6 border-green-200 bg-green-50/50 dark:border-green-800 dark:bg-green-950/20">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-green-700 dark:text-green-400">
                                <Calendar className="h-5 w-5" />
                                Active Enrollment Period
                            </CardTitle>
                            <CardDescription className="text-green-600 dark:text-green-500">
                                Currently accepting enrollment applications
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 md:grid-cols-3">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">School Year</p>
                                    <p className="text-lg font-semibold">{activePeriod.school_year.name}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Period</p>
                                    <p className="text-lg font-semibold">
                                        {format(new Date(activePeriod.start_date), 'MMM d, yyyy')} -{' '}
                                        {format(new Date(activePeriod.end_date), 'MMM d, yyyy')}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Enrollments</p>
                                    <p className="text-lg font-semibold">{activePeriod.enrollments_count || 0}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}

                <Card>
                    <CardHeader>
                        <CardTitle>All Enrollment Periods</CardTitle>
                        <CardDescription>
                            Showing {periods.from || 0} to {periods.to || 0} of {periods.total} periods
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>School Year</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Start Date</TableHead>
                                    <TableHead>End Date</TableHead>
                                    <TableHead>Registration Deadline</TableHead>
                                    <TableHead>Enrollments</TableHead>
                                    <TableHead className="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {periods.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={7} className="h-24 text-center">
                                            <div className="flex flex-col items-center justify-center text-muted-foreground">
                                                <Calendar className="mb-2 h-8 w-8" />
                                                <p>No enrollment periods found</p>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    periods.data.map((period) => (
                                        <TableRow key={period.id}>
                                            <TableCell className="font-medium">{period.school_year.name}</TableCell>
                                            <TableCell>
                                                <EnrollmentPeriodStatusBadge status={period.status} />
                                            </TableCell>
                                            <TableCell>{format(new Date(period.start_date), 'MMM d, yyyy')}</TableCell>
                                            <TableCell>{format(new Date(period.end_date), 'MMM d, yyyy')}</TableCell>
                                            <TableCell>{format(new Date(period.regular_registration_deadline), 'MMM d, yyyy')}</TableCell>
                                            <TableCell>{period.enrollments_count || 0}</TableCell>
                                            <TableCell className="text-right">
                                                <Link href={`/registrar/enrollment-periods/${period.id}`}>
                                                    <Button variant="outline" size="sm">
                                                        View
                                                    </Button>
                                                </Link>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

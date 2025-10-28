import Heading from '@/components/heading';
import { EnrollmentStatusBadge, PaymentStatusBadge } from '@/components/status-badges';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { formatCurrency } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Eye, Plus } from 'lucide-react';
import { type FC } from 'react';

interface Enrollment {
    id: number;
    enrollment_id: string;
    student: {
        id: number;
        first_name: string;
        last_name: string;
        student_id: string;
    };
    guardian?: {
        id: number;
        name: string;
    };
    school_year: string;
    grade_level: string;
    quarter: string;
    status: string;
    total_amount_cents: number;
    balance_cents: number;
    payment_status: string;
    created_at: string;
}

interface Props {
    enrollments: {
        data: Enrollment[];
        links: Record<string, string>;
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
        };
    };
}

const EnrollmentIndex: FC<Props> = ({ enrollments }) => {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Enrollments',
            href: '/enrollments',
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Enrollments" />

            <div className="px-4 py-6">
                <Heading title="Enrollments" description="Manage student enrollment applications" />

                <div className="space-y-6">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle>Enrollment List</CardTitle>
                            <Button asChild>
                                <Link href="/enrollments/create">
                                    <Plus className="mr-2 h-4 w-4" />
                                    New Enrollment
                                </Link>
                            </Button>
                        </CardHeader>
                        <CardContent>
                            {enrollments.data.length === 0 ? (
                                <div className="py-8 text-center text-muted-foreground">
                                    <p>No enrollments found.</p>
                                    <Button asChild className="mt-4">
                                        <Link href="/enrollments/create">Create your first enrollment</Link>
                                    </Button>
                                </div>
                            ) : (
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Enrollment ID</TableHead>
                                            <TableHead>Student</TableHead>
                                            <TableHead>School Year</TableHead>
                                            <TableHead>Grade Level</TableHead>
                                            <TableHead>Quarter</TableHead>
                                            <TableHead>Status</TableHead>
                                            <TableHead>Balance</TableHead>
                                            <TableHead>Payment</TableHead>
                                            <TableHead>Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {enrollments.data.map((enrollment) => (
                                            <TableRow key={enrollment.id}>
                                                <TableCell className="font-medium">{enrollment.enrollment_id}</TableCell>
                                                <TableCell>
                                                    {enrollment.student.first_name} {enrollment.student.last_name}
                                                    <br />
                                                    <span className="text-xs text-muted-foreground">{enrollment.student.student_id}</span>
                                                </TableCell>
                                                <TableCell>{enrollment.school_year}</TableCell>
                                                <TableCell>{enrollment.grade_level}</TableCell>
                                                <TableCell>{enrollment.quarter}</TableCell>
                                                <TableCell>
                                                    <EnrollmentStatusBadge status={enrollment.status} />
                                                </TableCell>
                                                <TableCell>{formatCurrency(enrollment.balance_cents / 100)}</TableCell>
                                                <TableCell>
                                                    <PaymentStatusBadge status={enrollment.payment_status} />
                                                </TableCell>
                                                <TableCell>
                                                    <Button variant="ghost" size="sm" asChild>
                                                        <Link href={`/enrollments/${enrollment.id}`}>
                                                            <Eye className="h-4 w-4" />
                                                        </Link>
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
};

export default EnrollmentIndex;

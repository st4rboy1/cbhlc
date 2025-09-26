import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { PlusCircle } from 'lucide-react';

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
    enrollments: {
        data: Enrollment[];
        links: unknown;
        meta: unknown;
    };
}

const statusColors = {
    pending: 'secondary',
    approved: 'default',
    enrolled: 'default',
    rejected: 'destructive',
    completed: 'secondary',
} as const;

const paymentStatusColors = {
    pending: 'secondary',
    partial: 'outline',
    paid: 'default',
    overdue: 'destructive',
} as const;

export default function GuardianEnrollmentsIndex({ enrollments }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Guardian', href: '/guardian/dashboard' },
        { title: 'Enrollments', href: '/guardian/enrollments' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="My Children's Enrollments" />

            <div className="px-4 py-6">
                <div className="mb-4 flex items-center justify-between">
                    <h1 className="mb-4 text-2xl font-bold">My Children's Enrollments</h1>
                    <Link href="/guardian/enrollments/create">
                        <Button>
                            <PlusCircle className="mr-2 h-4 w-4" />
                            New Enrollment
                        </Button>
                    </Link>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Enrollment Applications</CardTitle>
                        <CardDescription>View and manage your children's enrollment applications</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Student Name</TableHead>
                                    <TableHead>School Year</TableHead>
                                    <TableHead>Grade Level</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Payment Status</TableHead>
                                    <TableHead>Submission Date</TableHead>
                                    <TableHead>Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {enrollments.data.map((enrollment) => (
                                    <TableRow key={enrollment.id}>
                                        <TableCell>
                                            {enrollment.student.first_name} {enrollment.student.last_name}
                                        </TableCell>
                                        <TableCell>{enrollment.school_year}</TableCell>
                                        <TableCell>{enrollment.grade_level}</TableCell>
                                        <TableCell>
                                            <Badge variant={statusColors[enrollment.status as keyof typeof statusColors] || 'default'}>
                                                {enrollment.status}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Badge
                                                variant={
                                                    paymentStatusColors[enrollment.payment_status as keyof typeof paymentStatusColors] || 'default'
                                                }
                                            >
                                                {enrollment.payment_status}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>{enrollment.created_at}</TableCell>
                                        <TableCell>
                                            <div className="flex gap-2">
                                                <Link href={`/guardian/enrollments/${enrollment.id}`}>
                                                    <Button size="sm" variant="outline">
                                                        View
                                                    </Button>
                                                </Link>
                                                {enrollment.status === 'pending' && (
                                                    <Link href={`/guardian/enrollments/${enrollment.id}/edit`}>
                                                        <Button size="sm" variant="outline">
                                                            Edit
                                                        </Button>
                                                    </Link>
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

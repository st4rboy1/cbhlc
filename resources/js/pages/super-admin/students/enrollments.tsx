import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

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
}

interface Enrollment {
    id: number;
    enrollment_id: string;
    status: string;
    grade_level: string;
    quarter: string;
    school_year: string;
    guardian: Guardian;
    created_at: string;
}

interface Props {
    student: Student;
    enrollments: Enrollment[];
}

function getStatusVariant(status: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'enrolled':
        case 'completed':
        case 'paid':
        case 'approved':
            return 'default';
        case 'pending':
        case 'ready_for_payment':
            return 'secondary';
        case 'rejected':
            return 'destructive';
        default:
            return 'outline';
    }
}

export default function StudentEnrollments({ student, enrollments }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Students', href: '/super-admin/students' },
        {
            title: `${student.first_name} ${student.last_name}`,
            href: `/super-admin/students/${student.id}`,
        },
        { title: 'Enrollments', href: `/super-admin/students/${student.id}/enrollments` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Enrollments - ${student.first_name} ${student.last_name}`} />
            <div className="container mx-auto px-4 py-6">
                <div className="mb-6 flex items-center gap-4">
                    <Link href="/super-admin/students">
                        <Button variant="outline" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold">
                            Enrollments for {student.first_name} {student.last_name}
                        </h1>
                        <p className="text-sm text-muted-foreground">Student ID: {student.student_id}</p>
                    </div>
                </div>

                {enrollments.length === 0 ? (
                    <Card>
                        <CardContent className="flex min-h-[200px] flex-col items-center justify-center">
                            <p className="text-muted-foreground">No enrollments found for this student.</p>
                        </CardContent>
                    </Card>
                ) : (
                    <Card>
                        <CardHeader>
                            <CardTitle>Enrollment History</CardTitle>
                            <CardDescription>All enrollment records for this student</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Enrollment ID</TableHead>
                                        <TableHead>School Year</TableHead>
                                        <TableHead>Grade Level</TableHead>
                                        <TableHead>Quarter</TableHead>
                                        <TableHead>Guardian</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Enrolled Date</TableHead>
                                        <TableHead>Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {enrollments.map((enrollment) => (
                                        <TableRow key={enrollment.id}>
                                            <TableCell className="font-medium">{enrollment.enrollment_id}</TableCell>
                                            <TableCell>{enrollment.school_year}</TableCell>
                                            <TableCell className="capitalize">{enrollment.grade_level}</TableCell>
                                            <TableCell className="capitalize">{enrollment.quarter}</TableCell>
                                            <TableCell>
                                                {enrollment.guardian.first_name} {enrollment.guardian.last_name}
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant={getStatusVariant(enrollment.status)} className="capitalize">
                                                    {enrollment.status.replace('_', ' ')}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>{new Date(enrollment.created_at).toLocaleDateString()}</TableCell>
                                            <TableCell>
                                                <Link href={`/super-admin/enrollments/${enrollment.id}`}>
                                                    <Button variant="ghost" size="sm">
                                                        View Details
                                                    </Button>
                                                </Link>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}

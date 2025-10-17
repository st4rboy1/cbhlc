import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Edit, User } from 'lucide-react';

interface Student {
    id: number;
    student_id: string;
    first_name: string;
    last_name: string;
    grade_level: string;
    email: string;
}

interface Enrollment {
    id: number;
    student: Student;
    school_year: string;
    grade_level: string;
    status: string;
    enrollment_date: string;
}

interface Guardian {
    id: number;
    first_name: string;
    middle_name?: string;
    last_name: string;
    email: string;
    phone: string;
    address: string;
    relationship: string;
    occupation?: string;
    employer?: string;
    emergency_contact: boolean;
    created_at: string;
    updated_at: string;
    created_by?: {
        id: number;
        name: string;
    };
    updated_by?: {
        id: number;
        name: string;
    };
}

interface Props {
    guardian: Guardian;
    students: Student[];
    enrollments: Enrollment[];
}

export default function SuperAdminGuardiansShow({ guardian, students, enrollments }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Guardians', href: '/super-admin/guardians' },
        { title: 'View', href: '#' },
    ];

    const getFullName = () => {
        const parts = [guardian.first_name];
        if (guardian.middle_name) parts.push(guardian.middle_name);
        parts.push(guardian.last_name);
        return parts.join(' ');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="View Guardian" />
            <div className="container mx-auto max-w-5xl px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Guardian Details</h1>
                    <div className="flex gap-2">
                        <Link href="/super-admin/guardians">
                            <Button variant="outline">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Back to List
                            </Button>
                        </Link>
                        <Link href={`/super-admin/guardians/${guardian.id}/edit`}>
                            <Button>
                                <Edit className="mr-2 h-4 w-4" />
                                Edit
                            </Button>
                        </Link>
                    </div>
                </div>

                <div className="space-y-6">
                    {/* Guardian Details */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle>{getFullName()}</CardTitle>
                                    <CardDescription className="capitalize">{guardian.relationship.replace('_', ' ')}</CardDescription>
                                </div>
                                {guardian.emergency_contact && <Badge variant="default">Emergency Contact</Badge>}
                            </div>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            <div className="grid gap-6 md:grid-cols-2">
                                {/* Personal Information */}
                                <div>
                                    <h3 className="mb-3 font-semibold">Personal Information</h3>
                                    <dl className="space-y-2 text-sm">
                                        <div className="flex justify-between">
                                            <dt className="text-gray-600">First Name:</dt>
                                            <dd className="font-medium">{guardian.first_name}</dd>
                                        </div>
                                        {guardian.middle_name && (
                                            <div className="flex justify-between">
                                                <dt className="text-gray-600">Middle Name:</dt>
                                                <dd className="font-medium">{guardian.middle_name}</dd>
                                            </div>
                                        )}
                                        <div className="flex justify-between">
                                            <dt className="text-gray-600">Last Name:</dt>
                                            <dd className="font-medium">{guardian.last_name}</dd>
                                        </div>
                                        <div className="flex justify-between">
                                            <dt className="text-gray-600">Relationship:</dt>
                                            <dd className="font-medium capitalize">{guardian.relationship.replace('_', ' ')}</dd>
                                        </div>
                                    </dl>
                                </div>

                                {/* Contact Information */}
                                <div>
                                    <h3 className="mb-3 font-semibold">Contact Information</h3>
                                    <dl className="space-y-2 text-sm">
                                        <div className="flex justify-between">
                                            <dt className="text-gray-600">Email:</dt>
                                            <dd className="font-medium">{guardian.email}</dd>
                                        </div>
                                        <div className="flex justify-between">
                                            <dt className="text-gray-600">Phone:</dt>
                                            <dd className="font-medium">{guardian.phone}</dd>
                                        </div>
                                        <div className="flex justify-between">
                                            <dt className="text-gray-600">Address:</dt>
                                            <dd className="max-w-xs text-right font-medium">{guardian.address}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            {/* Employment Information */}
                            {(guardian.occupation || guardian.employer) && (
                                <div className="border-t pt-4">
                                    <h3 className="mb-3 font-semibold">Employment Information</h3>
                                    <dl className="grid gap-2 text-sm md:grid-cols-2">
                                        {guardian.occupation && (
                                            <div className="flex justify-between">
                                                <dt className="text-gray-600">Occupation:</dt>
                                                <dd className="font-medium">{guardian.occupation}</dd>
                                            </div>
                                        )}
                                        {guardian.employer && (
                                            <div className="flex justify-between">
                                                <dt className="text-gray-600">Employer:</dt>
                                                <dd className="font-medium">{guardian.employer}</dd>
                                            </div>
                                        )}
                                    </dl>
                                </div>
                            )}

                            {/* Audit Information */}
                            <div className="border-t pt-4">
                                <h3 className="mb-3 font-semibold">Audit Information</h3>
                                <dl className="grid gap-2 text-sm md:grid-cols-2">
                                    <div>
                                        <dt className="text-gray-600">Created:</dt>
                                        <dd className="font-medium">
                                            {new Date(guardian.created_at).toLocaleDateString('en-US', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                            })}
                                            {guardian.created_by && ` by ${guardian.created_by.name}`}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="text-gray-600">Last Updated:</dt>
                                        <dd className="font-medium">
                                            {new Date(guardian.updated_at).toLocaleDateString('en-US', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                            })}
                                            {guardian.updated_by && ` by ${guardian.updated_by.name}`}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Associated Students */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Associated Students ({students.length})</CardTitle>
                            <CardDescription>Students under this guardian's care</CardDescription>
                        </CardHeader>
                        <CardContent>
                            {students.length === 0 ? (
                                <p className="text-center text-gray-500">No students associated with this guardian.</p>
                            ) : (
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Student ID</TableHead>
                                            <TableHead>Name</TableHead>
                                            <TableHead>Grade Level</TableHead>
                                            <TableHead>Email</TableHead>
                                            <TableHead className="text-right">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {students.map((student) => (
                                            <TableRow key={student.id}>
                                                <TableCell className="font-medium">{student.student_id}</TableCell>
                                                <TableCell>
                                                    {student.first_name} {student.last_name}
                                                </TableCell>
                                                <TableCell>{student.grade_level}</TableCell>
                                                <TableCell>{student.email}</TableCell>
                                                <TableCell className="text-right">
                                                    <Link href={`/super-admin/students/${student.id}`}>
                                                        <Button size="sm" variant="outline">
                                                            <User className="h-4 w-4" />
                                                        </Button>
                                                    </Link>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            )}
                        </CardContent>
                    </Card>

                    {/* Enrollment History */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Enrollment History ({enrollments.length})</CardTitle>
                            <CardDescription>Enrollment records for associated students</CardDescription>
                        </CardHeader>
                        <CardContent>
                            {enrollments.length === 0 ? (
                                <p className="text-center text-gray-500">No enrollment records found.</p>
                            ) : (
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Student</TableHead>
                                            <TableHead>School Year</TableHead>
                                            <TableHead>Grade Level</TableHead>
                                            <TableHead>Status</TableHead>
                                            <TableHead>Enrollment Date</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {enrollments.map((enrollment) => (
                                            <TableRow key={enrollment.id}>
                                                <TableCell className="font-medium">
                                                    {enrollment.student.first_name} {enrollment.student.last_name}
                                                </TableCell>
                                                <TableCell>{enrollment.school_year}</TableCell>
                                                <TableCell>{enrollment.grade_level}</TableCell>
                                                <TableCell>
                                                    <Badge
                                                        variant={
                                                            enrollment.status === 'enrolled'
                                                                ? 'default'
                                                                : enrollment.status === 'completed'
                                                                  ? 'secondary'
                                                                  : 'outline'
                                                        }
                                                    >
                                                        {enrollment.status}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    {new Date(enrollment.enrollment_date).toLocaleDateString('en-US', {
                                                        year: 'numeric',
                                                        month: 'short',
                                                        day: 'numeric',
                                                    })}
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
}

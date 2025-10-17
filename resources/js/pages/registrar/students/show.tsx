import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { calculateAge } from '@/pages/registrar/students/students-table';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Student {
    id: number;
    student_id: string;
    first_name: string;
    last_name: string;
    middle_name: string;
    birthdate: string;
    gender: string;
    grade_level: string;
    contact_number: string;
    address: string;
    enrollments: {
        enrollment_id: string;
        school_year: string;
        grade_level: string;
        status: string;
        payment_status: string;
    }[];
}

interface Props {
    student: Student;
}

export default function RegistrarStudentsShow({ student }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Registrar', href: '/registrar/dashboard' },
        { title: 'Students', href: '/registrar/students' },
        { title: `${student.first_name} ${student.last_name}`, href: `/registrar/students/${student.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${student.first_name} ${student.last_name}`} />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Student Details</h1>

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card>
                        <CardHeader>
                            <CardTitle>Personal Information</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Student ID</p>
                                <p className="text-lg font-semibold">{student.student_id}</p>
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Name</p>
                                <p className="text-lg font-semibold">{`${student.first_name} ${student.middle_name} ${student.last_name}`}</p>
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Birthdate</p>
                                <p className="text-lg font-semibold">{student.birthdate}</p>
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Age</p>
                                <p className="text-lg font-semibold">{calculateAge(student.birthdate)}</p>
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Gender</p>
                                <p className="text-lg font-semibold">{student.gender}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Contact Information</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Contact Number</p>
                                <p className="text-lg font-semibold">{student.contact_number}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Address</p>
                                <p className="text-lg font-semibold">{student.address}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Academic Information</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Grade Level</p>
                                <p className="text-lg font-semibold">{student.grade_level || 'Not assigned'}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}

import AppLayout from '@/layouts/app-layout';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem, type Student } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    student: Student;
}

export default function StudentShow({ student }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Students', href: '/admin/students' },
        { title: student.full_name, href: `/admin/students/${student.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Student - ${student.full_name}`} />
            <div className="px-4 py-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Student Details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <p className="font-semibold">Name</p>
                                <p>{student.full_name}</p>
                            </div>
                            <div>
                                <p className="font-semibold">Email</p>
                                <p>{student.email}</p>
                            </div>
                            <div>
                                <p className="font-semibold">Grade Level</p>
                                <p>{student.grade_level?.label ?? 'N/A'}</p>
                            </div>
                            <div>
                                <p className="font-semibold">Birthdate</p>
                                <p>{student.birthdate ? new Date(student.birthdate).toLocaleDateString() : 'N/A'}</p>
                            </div>
                            <div>
                                <p className="font-semibold">Gender</p>
                                <p>{student.gender ?? 'N/A'}</p>
                            </div>
                            <div>
                                <p className="font-semibold">Address</p>
                                <p>{student.address ?? 'N/A'}</p>
                            </div>
                            <div>
                                <p className="font-semibold">Contact Number</p>
                                <p>{student.contact_number ?? 'N/A'}</p>
                            </div>
                            <div>
                                <p className="font-semibold">Student ID</p>
                                <p>{student.student_id ?? 'N/A'}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

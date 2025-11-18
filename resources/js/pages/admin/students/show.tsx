import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    student: {
        id: string | number;
        student_id: string;
        first_name: string;
        middle_name: string | null;
        last_name: string;
        grade: string;
        status: string;
        birth_date: string;
        address: string;
        guardians: Array<{ id: number; user: { name: string; email: string } }>;
    };
}

export default function StudentShow({ student }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Students', href: '/admin/students' },
        { title: `${student.first_name} ${student.last_name}`, href: `/admin/students/${student.id}` },
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
                                <p className="text-lg font-semibold">{`${student.first_name} ${student.middle_name ? student.middle_name + ' ' : ''}${student.last_name}`}</p>
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Birth Date</p>
                                <p className="text-lg font-semibold">{student.birth_date}</p>
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Grade</p>
                                <p className="text-lg font-semibold">{student.grade}</p>
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-muted-foreground">Status</p>
                                <Badge variant="default">{student.status}</Badge>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Contact Information</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Address</p>
                                <p className="text-lg font-semibold">{student.address}</p>
                            </div>
                        </CardContent>
                    </Card>

                    {student.guardians.length > 0 && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Guardians</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4">
                                {student.guardians.map((guardian) => (
                                    <div key={guardian.id} className="space-y-1">
                                        <p className="text-sm font-medium text-muted-foreground">Name</p>
                                        <p className="text-lg font-semibold">{guardian.user.name}</p>
                                        <p className="text-sm font-medium text-muted-foreground">Email</p>
                                        <p className="text-lg font-semibold">{guardian.user.email}</p>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

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
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Students', href: '/super-admin/students' },
        { title: `${student.first_name} ${student.last_name}`, href: `/super-admin/students/${student.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${student.first_name} ${student.last_name}`} />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Student Details</h1>
                <div className="space-y-4">
                    <div>
                        <h2 className="text-lg font-semibold">Personal Information</h2>
                        <p>
                            <strong>Student ID:</strong> {student.student_id}
                        </p>
                        <p>
                            <strong>Name:</strong> {student.first_name} {student.middle_name} {student.last_name}
                        </p>
                        <p>
                            <strong>Grade:</strong> {student.grade}
                        </p>
                        <p>
                            <strong>Status:</strong> {student.status}
                        </p>
                        <p>
                            <strong>Birth Date:</strong> {student.birth_date}
                        </p>
                        <p>
                            <strong>Address:</strong> {student.address}
                        </p>
                    </div>

                    {student.guardians.length > 0 && (
                        <div>
                            <h2 className="text-lg font-semibold">Guardians</h2>
                            {student.guardians.map((guardian) => (
                                <div key={guardian.id} className="ml-4">
                                    <p>
                                        <strong>Name:</strong> {guardian.user.name}
                                    </p>
                                    <p>
                                        <strong>Email:</strong> {guardian.user.email}
                                    </p>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

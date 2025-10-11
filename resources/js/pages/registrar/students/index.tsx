import AppLayout from '@/layouts/app-layout';
import { StudentsTable } from '@/pages/registrar/students/students-table';
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

interface PaginatedStudents {
    data: Student[];
}

interface Props {
    students: PaginatedStudents;
}

export default function RegistrarStudentsIndex({ students }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Registrar', href: '/registrar/dashboard' },
        { title: 'Students', href: '/registrar/students' },
    ];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Students Index" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Students Index</h1>
                <StudentsTable students={students.data} />
            </div>
        </AppLayout>
    );
}

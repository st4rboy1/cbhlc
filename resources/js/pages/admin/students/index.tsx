import StudentManager from '@/components/admin/student/admin-student-index';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Student {
    id: number;
    name: string;
    grade: string;
    status: string;
}

interface Props {
    students: Student[];
}

export default function StudentsIndex({ students }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Students', href: '/admin/students' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Students" />
            <StudentManager students={students} />
        </AppLayout>
    );
}

import AdminEnrollmentIndex from '@/components/admin/enrollment/admin-enrollment-index';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Enrollment {
    id: number;
    student_name: string;
    grade: string;
    status: string;
}

interface Props {
    enrollments: Enrollment[];
}

export default function EnrollmentsIndex({ enrollments }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Enrollments', href: '/admin/enrollments' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Enrollments" />
            <AdminEnrollmentIndex enrollments={enrollments} />
        </AppLayout>
    );
}

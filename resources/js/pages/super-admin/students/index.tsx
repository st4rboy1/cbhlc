import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { StudentTable } from '@/pages/super-admin/students/students-table';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';

export type Student = {
    id: number;
    student_id: string;
    first_name: string;
    last_name: string;
    email: string;
    grade_level: string;
    guardians: { first_name: string; last_name: string }[];
    enrollments: {
        id: number;
        status: string;
        payment_status: string;
        balance: number;
        net_amount: number;
    }[];
};

interface PaginatedStudents {
    current_page: number;
    data: Student[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: { url: string | null; label: string; active: boolean }[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

interface Props {
    students: PaginatedStudents;
    filters: {
        search: string | null;
        grade_level: string | null;
        status: string | null;
    };
}

export default function StudentsIndex({ students, filters }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Students', href: '/super-admin/students' },
    ];

    const parseCurrency = (amount: number) => amount || 0;

    const formattedStudents = students.data.map((student) => ({
        id: student.id,
        studentId: student.student_id,
        name: `${student.first_name} ${student.last_name}`,
        gradeLevel: student.grade_level,
        guardian: student.guardians.length > 0 ? `${student.guardians[0].first_name} ${student.guardians[0].last_name}` : 'N/A',
        enrollmentStatus: student.enrollments.length > 0 ? student.enrollments[0].status : 'N/A',
        paymentStatus: student.enrollments.length > 0 ? student.enrollments[0].payment_status : 'N/A',
        balance: student.enrollments.length > 0 ? parseCurrency(student.enrollments[0].balance) : 0,
        netAmount: student.enrollments.length > 0 ? parseCurrency(student.enrollments[0].net_amount) : 0,
        activeEnrollmentId: student.enrollments.length > 0 ? student.enrollments[0].id : null,
    }));

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Students" />
            <div className="px-4 py-6">
                <div className="mb-4 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Students Index</h1>
                    <Link href="/super-admin/students/create">
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Create Student
                        </Button>
                    </Link>
                </div>
                <StudentTable students={formattedStudents} filters={filters} />
            </div>
        </AppLayout>
    );
}

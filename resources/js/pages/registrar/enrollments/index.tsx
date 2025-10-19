import AppLayout from '@/layouts/app-layout';
import { EnrollmentsTable } from '@/pages/registrar/enrollments/enrollments-table';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Enrollment {
    id: number;
    student: { first_name: string; last_name: string; student_id: string };
    guardian: { first_name: string; last_name: string; user?: { email: string } };
    school_year: string;
    quarter: string;
    grade_level: string;
    status: string;
    net_amount_cents: number;
    amount_paid_cents: number;
    balance_cents: number;
    payment_status: string;
}

interface PaginatedEnrollments {
    data: Enrollment[];
}

interface Props {
    enrollments: PaginatedEnrollments;
}

export default function RegistrarEnrollmentsIndex({ enrollments }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Registrar', href: '/registrar/dashboard' },
        { title: 'Enrollments', href: '/registrar/enrollments' },
    ];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Enrollments Index" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Enrollments Index</h1>
                <EnrollmentsTable enrollments={enrollments.data} />
            </div>
        </AppLayout>
    );
}

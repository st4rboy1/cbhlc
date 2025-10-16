import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export type EnrollmentPeriod = {
    id: number;
    school_year: string;
    status: string;
    start_date: string;
    end_date: string;
    early_registration_deadline: string | null;
    regular_registration_deadline: string;
    late_registration_deadline: string | null;
    allow_new_students: boolean;
    allow_returning_students: boolean;
    enrollments_count?: number;
};

interface Props {
    period: EnrollmentPeriod;
}

export default function EnrollmentPeriodEdit({ period }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Enrollment Periods', href: '/super-admin/enrollment-periods' },
        { title: period.school_year, href: `/super-admin/enrollment-periods/${period.id}` },
        { title: 'Edit', href: `/super-admin/enrollment-periods/${period.id}/edit` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Enrollment Period ${period.school_year}`} />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Edit Enrollment Period</h1>
                <div className="rounded bg-yellow-50 p-4 text-yellow-800">TODO: UI implementation pending (TICKET-009)</div>
                <pre className="mt-4 overflow-auto rounded bg-gray-100 p-4">{JSON.stringify({ period }, null, 2)}</pre>
            </div>
        </AppLayout>
    );
}

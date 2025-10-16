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

interface PaginatedEnrollmentPeriods {
    current_page: number;
    data: EnrollmentPeriod[];
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
    periods: PaginatedEnrollmentPeriods;
    activePeriod: EnrollmentPeriod | null;
}

export default function EnrollmentPeriodsIndex({ periods, activePeriod }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Enrollment Periods', href: '/super-admin/enrollment-periods' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Enrollment Periods" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Enrollment Periods Index</h1>
                <div className="rounded bg-yellow-50 p-4 text-yellow-800">TODO: UI implementation pending (TICKET-009)</div>
                <pre className="mt-4 overflow-auto rounded bg-gray-100 p-4">{JSON.stringify({ periods, activePeriod }, null, 2)}</pre>
            </div>
        </AppLayout>
    );
}

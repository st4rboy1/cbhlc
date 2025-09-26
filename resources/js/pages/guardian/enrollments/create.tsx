import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    students: unknown[];
    gradeLevels: string[];
    quarters: string[];
    currentSchoolYear: string;
    selectedStudentId?: string;
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
export default function GuardianEnrollmentsCreate(props: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Guardian', href: '/guardian/dashboard' },
        { title: 'Enrollments', href: '/guardian/enrollments' },
        { title: 'New Enrollment', href: '/guardian/enrollments/create' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="New Enrollment" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">New Enrollment Application</h1>
                {/* TODO: Implement enrollment form */}
                <p>Enrollment form will be implemented here</p>
            </div>
        </AppLayout>
    );
}

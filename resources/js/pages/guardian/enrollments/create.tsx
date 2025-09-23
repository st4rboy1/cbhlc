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
    return (
        <>
            <Head title="New Enrollment" />
            <div className="container mx-auto py-6">
                <h1 className="mb-6 text-3xl font-bold">New Enrollment Application</h1>
                {/* TODO: Implement enrollment form */}
                <p>Enrollment form will be implemented here</p>
            </div>
        </>
    );
}

import { Head } from '@inertiajs/react'

interface Props {
    students: any[]
    gradeLevels: string[]
    quarters: string[]
    currentSchoolYear: string
    selectedStudentId?: string
}

export default function GuardianEnrollmentsCreate({
    students,
    gradeLevels,
    quarters,
    currentSchoolYear,
    selectedStudentId
}: Props) {
    return (
        <>
            <Head title="New Enrollment" />
            <div className="container mx-auto py-6">
                <h1 className="text-3xl font-bold mb-6">New Enrollment Application</h1>
                {/* TODO: Implement enrollment form */}
                <p>Enrollment form will be implemented here</p>
            </div>
        </>
    )
}
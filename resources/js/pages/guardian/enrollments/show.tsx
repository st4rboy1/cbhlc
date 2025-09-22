import { Head } from '@inertiajs/react'

export default function GuardianEnrollmentsShow({ enrollment }: any) {
    return (
        <>
            <Head title="Enrollment Details" />
            <div className="container mx-auto py-6">
                <h1 className="text-3xl font-bold mb-6">Enrollment Details</h1>
                <pre>{JSON.stringify(enrollment, null, 2)}</pre>
            </div>
        </>
    )
}

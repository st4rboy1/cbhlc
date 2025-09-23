import { Head } from '@inertiajs/react';

export default function GuardianEnrollmentsShow({ enrollment }: { enrollment: unknown }) {
    return (
        <>
            <Head title="Enrollment Details" />
            <div className="container mx-auto py-6">
                <h1 className="mb-6 text-3xl font-bold">Enrollment Details</h1>
                <pre>{JSON.stringify(enrollment, null, 2)}</pre>
            </div>
        </>
    );
}

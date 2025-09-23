import { Head } from '@inertiajs/react';

export default function RegistrarEnrollmentsIndex(props: unknown) {
    return (
        <>
            <Head title="Enrollments Index" />
            <div className="container mx-auto py-6">
                <h1 className="mb-6 text-3xl font-bold">Enrollments Index</h1>
                <pre>{JSON.stringify(props, null, 2)}</pre>
            </div>
        </>
    );
}

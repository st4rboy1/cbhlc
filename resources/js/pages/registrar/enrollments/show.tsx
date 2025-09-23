import { Head } from '@inertiajs/react';

export default function RegistrarEnrollmentsShow(props: unknown) {
    return (
        <>
            <Head title="Enrollments Show" />
            <div className="container mx-auto py-6">
                <h1 className="mb-6 text-3xl font-bold">Enrollments Show</h1>
                <pre>{JSON.stringify(props, null, 2)}</pre>
            </div>
        </>
    );
}

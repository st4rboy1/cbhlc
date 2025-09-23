import { Head } from '@inertiajs/react';

export default function GuardianStudentsEdit(props: unknown) {
    return (
        <>
            <Head title="Students Edit" />
            <div className="container mx-auto py-6">
                <h1 className="mb-6 text-3xl font-bold">Students Edit</h1>
                <pre>{JSON.stringify(props, null, 2)}</pre>
            </div>
        </>
    );
}

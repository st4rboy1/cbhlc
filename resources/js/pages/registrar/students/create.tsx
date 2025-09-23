import { Head } from '@inertiajs/react';

export default function RegistrarStudentsCreate(props: unknown) {
    return (
        <>
            <Head title="Students Create" />
            <div className="container mx-auto py-6">
                <h1 className="mb-6 text-3xl font-bold">Students Create</h1>
                <pre>{JSON.stringify(props, null, 2)}</pre>
            </div>
        </>
    );
}

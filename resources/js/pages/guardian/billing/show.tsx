import { Head } from '@inertiajs/react';

export default function GuardianBillingShow(props: unknown) {
    return (
        <>
            <Head title="Billing Show" />
            <div className="container mx-auto py-6">
                <h1 className="mb-6 text-3xl font-bold">Billing Show</h1>
                <pre>{JSON.stringify(props, null, 2)}</pre>
            </div>
        </>
    );
}

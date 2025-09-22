import { Head } from '@inertiajs/react'

export default function GuardianBillingShow(props: any) {
    return (
        <>
            <Head title="Billing Show" />
            <div className="container mx-auto py-6">
                <h1 className="text-3xl font-bold mb-6">Billing Show</h1>
                <pre>{JSON.stringify(props, null, 2)}</pre>
            </div>
        </>
    )
}

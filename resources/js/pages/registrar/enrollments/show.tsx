import { Head } from '@inertiajs/react'

export default function RegistrarEnrollmentsShow(props: any) {
    return (
        <>
            <Head title="Enrollments Show" />
            <div className="container mx-auto py-6">
                <h1 className="text-3xl font-bold mb-6">Enrollments Show</h1>
                <pre>{JSON.stringify(props, null, 2)}</pre>
            </div>
        </>
    )
}

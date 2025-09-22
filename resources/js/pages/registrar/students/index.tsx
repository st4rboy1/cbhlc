import { Head } from '@inertiajs/react'

export default function RegistrarStudentsIndex(props: any) {
    return (
        <>
            <Head title="Students Index" />
            <div className="container mx-auto py-6">
                <h1 className="text-3xl font-bold mb-6">Students Index</h1>
                <pre>{JSON.stringify(props, null, 2)}</pre>
            </div>
        </>
    )
}

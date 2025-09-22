import { Head } from '@inertiajs/react'

export default function RegistrarStudentsCreate(props: any) {
    return (
        <>
            <Head title="Students Create" />
            <div className="container mx-auto py-6">
                <h1 className="text-3xl font-bold mb-6">Students Create</h1>
                <pre>{JSON.stringify(props, null, 2)}</pre>
            </div>
        </>
    )
}

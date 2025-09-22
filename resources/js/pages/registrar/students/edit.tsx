import { Head } from '@inertiajs/react'

export default function RegistrarStudentsEdit(props: any) {
    return (
        <>
            <Head title="Students Edit" />
            <div className="container mx-auto py-6">
                <h1 className="text-3xl font-bold mb-6">Students Edit</h1>
                <pre>{JSON.stringify(props, null, 2)}</pre>
            </div>
        </>
    )
}

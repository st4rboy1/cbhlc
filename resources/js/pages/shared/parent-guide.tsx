import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    [key: string]: unknown;
}

export default function ParentGuide(props: Props) {
    const breadcrumbs: BreadcrumbItem[] = [{ title: 'Parent Guide', href: '/parent-guide' }];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Parent Guide" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Parent Guide</h1>
                <pre className="overflow-auto rounded bg-gray-100 p-4">{JSON.stringify(props, null, 2)}</pre>
            </div>
        </AppLayout>
    );
}

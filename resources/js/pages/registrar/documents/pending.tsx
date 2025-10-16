import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Document {
    id: number;
    student_id: number;
    document_type: string;
    original_filename: string;
    verification_status: string;
    upload_date: string;
    student?: {
        first_name: string;
        last_name: string;
        student_id: string;
    };
    verifiedBy?: {
        name: string;
    };
}

interface PaginatedDocuments {
    data: Document[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    documents: PaginatedDocuments;
}

export default function PendingDocuments({ documents }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Registrar', href: '/registrar/dashboard' },
        { title: 'Documents', href: '/registrar/documents/pending' },
        { title: 'Pending', href: '/registrar/documents/pending' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Pending Documents" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Pending Documents</h1>
                <div className="rounded bg-yellow-50 p-4 text-yellow-800">TODO: UI implementation pending</div>
                <pre className="mt-4 overflow-auto rounded bg-gray-100 p-4">{JSON.stringify({ documents }, null, 2)}</pre>
            </div>
        </AppLayout>
    );
}

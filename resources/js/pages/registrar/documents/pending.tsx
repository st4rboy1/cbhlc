import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-react';

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
    filters?: {
        sort?: string;
        direction?: string;
    };
}

export default function PendingDocuments({ documents, filters }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Registrar', href: '/registrar/dashboard' },
        { title: 'Documents', href: '/registrar/documents/pending' },
        { title: 'Pending', href: '/registrar/documents/pending' },
    ];

    const sortBy = (field: string) => {
        const currentSort = filters?.sort || 'upload_date';
        const currentDirection = filters?.direction || 'desc';

        let newDirection = 'desc';

        if (currentSort === field) {
            // Toggle direction if same field
            newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
        }

        router.get(
            '/registrar/documents/pending',
            {
                sort: field,
                direction: newDirection,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const getSortIcon = (field: string) => {
        const currentSort = filters?.sort || 'upload_date';
        const currentDirection = filters?.direction || 'desc';

        if (currentSort !== field) {
            return <ArrowUpDown className="ml-1 inline h-4 w-4 text-gray-400" />;
        }

        return currentDirection === 'asc' ? (
            <ArrowUp className="ml-1 inline h-4 w-4 text-gray-700" />
        ) : (
            <ArrowDown className="ml-1 inline h-4 w-4 text-gray-700" />
        );
    };

    const verifyDocument = (documentId: number) => {
        if (confirm('Are you sure you want to verify this document?')) {
            router.post(`/registrar/documents/${documentId}/verify`);
        }
    };

    const rejectDocument = (documentId: number) => {
        const reason = prompt('Please enter the reason for rejection:');
        if (reason) {
            router.post(`/registrar/documents/${documentId}/reject`, {
                notes: reason,
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Pending Documents" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Pending Documents</h1>
                <div className="rounded-lg border">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th
                                    scope="col"
                                    className="cursor-pointer px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase hover:bg-gray-100"
                                    onClick={() => sortBy('student_name')}
                                >
                                    <span className="flex items-center">
                                        Student
                                        {getSortIcon('student_name')}
                                    </span>
                                </th>
                                <th
                                    scope="col"
                                    className="cursor-pointer px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase hover:bg-gray-100"
                                    onClick={() => sortBy('document_type')}
                                >
                                    <span className="flex items-center">
                                        Document Type
                                        {getSortIcon('document_type')}
                                    </span>
                                </th>
                                <th
                                    scope="col"
                                    className="cursor-pointer px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase hover:bg-gray-100"
                                    onClick={() => sortBy('upload_date')}
                                >
                                    <span className="flex items-center">
                                        Upload Date
                                        {getSortIcon('upload_date')}
                                    </span>
                                </th>
                                <th scope="col" className="relative px-6 py-3">
                                    <span className="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-200 bg-white">
                            {documents.data.length > 0 ? (
                                documents.data.map((document) => (
                                    <tr key={document.id}>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="text-sm font-medium text-gray-900">
                                                {document.student?.first_name} {document.student?.last_name}
                                            </div>
                                            <div className="text-sm text-gray-500">{document.student?.student_id}</div>
                                        </td>
                                        <td className="px-6 py-4 text-sm whitespace-nowrap text-gray-500">{document.document_type}</td>
                                        <td className="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                                            {new Date(document.upload_date).toLocaleDateString()}
                                        </td>
                                        <td className="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                            <a
                                                href={`/registrar/documents/${document.id}/view`}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-blue-600 hover:text-blue-900"
                                            >
                                                View
                                            </a>
                                            <button
                                                onClick={() => verifyDocument(document.id)}
                                                className="ml-4 text-indigo-600 hover:text-indigo-900"
                                            >
                                                Verify
                                            </button>
                                            <button onClick={() => rejectDocument(document.id)} className="ml-4 text-red-600 hover:text-red-900">
                                                Reject
                                            </button>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan={4} className="py-12 text-center">
                                        <h3 className="text-lg font-medium text-gray-900">No pending documents</h3>
                                        <p className="mt-1 text-sm text-gray-500">There are no documents to verify at this time.</p>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}

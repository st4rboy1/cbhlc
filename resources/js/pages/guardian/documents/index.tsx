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

export default function GuardianDocumentsIndex({ documents }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Guardian', href: '/guardian/dashboard' },
        { title: 'Documents', href: '/guardian/documents' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="My Children's Documents" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">My Children's Documents</h1>
                <div className="rounded-lg border">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                    Student
                                </th>
                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                    Document Type
                                </th>
                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                    Upload Date
                                </th>
                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                    Status
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
                                        <td className="px-6 py-4 text-sm whitespace-nowrap text-gray-500">{document.verification_status}</td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan={4} className="py-12 text-center">
                                        <h3 className="text-lg font-medium text-gray-900">No documents found</h3>
                                        <p className="mt-1 text-sm text-gray-500">There are no documents for your children at this time.</p>
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

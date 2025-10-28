import { DocumentStatusBadge } from '@/components/status-badges';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import { CheckCircle, Eye, Trash2, XCircle } from 'lucide-react';

interface Student {
    id: number;
    first_name: string;
    last_name: string;
}

interface VerifiedBy {
    id: number;
    name: string;
}

interface Document {
    id: number;
    document_type: string;
    original_filename: string;
    file_size: number;
    mime_type: string;
    upload_date: string;
    verification_status: string;
    verified_at: string | null;
    rejection_reason: string | null;
    student: Student;
    verified_by: VerifiedBy | null;
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

export default function DocumentsIndex({ documents }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Documents', href: '/super-admin/documents' },
    ];

    const getDocumentTypeLabel = (type: string): string => {
        const labels: Record<string, string> = {
            birth_certificate: 'Birth Certificate',
            report_card: 'Report Card',
            form_138: 'Form 138',
            good_moral: 'Good Moral Certificate',
            other: 'Other Document',
        };
        return labels[type] || type;
    };

    const formatFileSize = (bytes: number): string => {
        const units = ['B', 'KB', 'MB', 'GB'];
        let size = bytes;
        let unitIndex = 0;

        while (size >= 1024 && unitIndex < units.length - 1) {
            size /= 1024;
            unitIndex++;
        }

        return `${size.toFixed(2)} ${units[unitIndex]}`;
    };

    const handleVerify = (id: number) => {
        if (confirm('Are you sure you want to verify this document?')) {
            router.post(`/super-admin/documents/${id}/verify`);
        }
    };

    const handleReject = (id: number) => {
        const reason = prompt('Please provide a reason for rejection (minimum 10 characters):');
        if (reason && reason.length >= 10) {
            router.post(`/super-admin/documents/${id}/reject`, { notes: reason });
        } else if (reason) {
            alert('Rejection reason must be at least 10 characters.');
        }
    };

    const handleDelete = (id: number) => {
        if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
            router.delete(`/super-admin/documents/${id}`);
        }
    };

    const columns: ColumnDef<Document>[] = [
        {
            accessorKey: 'student',
            header: 'Student',
            cell: ({ row }) => (
                <div>
                    <div className="font-medium">
                        {row.original.student.first_name} {row.original.student.last_name}
                    </div>
                    <div className="text-xs text-muted-foreground">{getDocumentTypeLabel(row.original.document_type)}</div>
                </div>
            ),
        },
        {
            accessorKey: 'original_filename',
            header: 'File Name',
            cell: ({ row }) => (
                <div>
                    <div className="font-medium">{row.original.original_filename}</div>
                    <div className="text-xs text-muted-foreground">{formatFileSize(row.original.file_size)}</div>
                </div>
            ),
        },
        {
            accessorKey: 'upload_date',
            header: 'Upload Date',
            cell: ({ row }) => new Date(row.original.upload_date).toLocaleDateString(),
        },
        {
            accessorKey: 'verification_status',
            header: 'Status',
            cell: ({ row }) => <DocumentStatusBadge status={row.original.verification_status} />,
        },
        {
            id: 'actions',
            header: 'Actions',
            cell: ({ row }) => (
                <div className="flex gap-2">
                    <Button size="sm" variant="outline" onClick={() => router.visit(`/super-admin/documents/${row.original.id}`)}>
                        <Eye className="mr-1 h-3 w-3" />
                        View
                    </Button>
                    {row.original.verification_status === 'pending' && (
                        <>
                            <Button size="sm" variant="outline" onClick={() => handleVerify(row.original.id)}>
                                <CheckCircle className="mr-1 h-3 w-3" />
                                Verify
                            </Button>
                            <Button size="sm" variant="outline" onClick={() => handleReject(row.original.id)}>
                                <XCircle className="mr-1 h-3 w-3" />
                                Reject
                            </Button>
                        </>
                    )}
                    <Button size="sm" variant="destructive" onClick={() => handleDelete(row.original.id)}>
                        <Trash2 className="h-3 w-3" />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Documents" />
            <div className="px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Documents</h1>
                        <p className="mt-1 text-sm text-muted-foreground">Manage student documents and verification status</p>
                    </div>
                </div>
                <DataTable columns={columns} data={documents.data} />
            </div>
        </AppLayout>
    );
}

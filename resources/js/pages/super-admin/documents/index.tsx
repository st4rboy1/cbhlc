import { DocumentStatusBadge } from '@/components/status-badges';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { ColumnDef, SortingState } from '@tanstack/react-table';
import { CheckCircle, Eye, Trash2, XCircle } from 'lucide-react';
import { useEffect, useState } from 'react';

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
    student_id: number;
    verified_by: { id: number; name: string } | null;
}

interface Student {
    id: number;
    first_name: string;
    last_name: string;
    documents: Document[];
}

interface PaginatedStudents {
    data: Student[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface StudentOption {
    value: number;
    label: string;
}

interface Props {
    studentsWithDocuments: PaginatedStudents;
    filters: {
        verification_status?: string;
        document_type?: string;
        student_id?: string;
        search?: string;
        sort_by?: string;
        sort_direction?: string;
    };
    students: StudentOption[];
}

export default function DocumentsIndex({ studentsWithDocuments, filters, students }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Documents', href: '/super-admin/documents' },
    ];

    const [search, setSearch] = useState(filters.search || '');
    const [studentId, setStudentId] = useState(filters.student_id || '');
    const [sorting, setSorting] = useState<SortingState>(
        filters.sort_by && filters.sort_direction ? [{ id: filters.sort_by, desc: filters.sort_direction === 'desc' }] : [],
    );

    useEffect(() => {
        const handler = setTimeout(() => {
            router.get(
                route('super-admin.documents.index'),
                {
                    search: search,
                    student_id: studentId === 'all' ? '' : studentId,
                    sort_by: sorting.length > 0 ? sorting[0].id : undefined,
                    sort_direction: sorting.length > 0 ? (sorting[0].desc ? 'desc' : 'asc') : undefined,
                },
                { preserveState: true, replace: true },
            );
        }, 300);

        return () => clearTimeout(handler);
    }, [search, studentId, sorting]);

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

    const columns: ColumnDef<Student>[] = [
        {
            accessorKey: 'first_name',
            header: 'Student Name',
            enableSorting: true,
            cell: ({ row }) => (
                <div className="font-medium">
                    {row.original.first_name} {row.original.last_name}
                </div>
            ),
        },
        {
            accessorKey: 'documents',
            header: 'Documents',
            cell: ({ row }) => (
                <div className="flex flex-col gap-2">
                    {row.original.documents.length > 0 ? (
                        row.original.documents.map((document) => (
                            <div key={document.id} className="flex items-center justify-between rounded-md bg-muted p-2">
                                <div className="flex flex-col">
                                    <span className="font-medium">{document.original_filename}</span>
                                    <span className="text-xs text-muted-foreground">
                                        {getDocumentTypeLabel(document.document_type)} - {formatFileSize(document.file_size)}
                                    </span>
                                </div>
                                <div className="flex gap-2">
                                    <DocumentStatusBadge status={document.verification_status} />
                                    <Button size="sm" variant="outline" onClick={() => router.visit(`/super-admin/documents/${document.id}`)}>
                                        <Eye className="h-3 w-3" />
                                    </Button>
                                    {document.verification_status === 'pending' && (
                                        <>
                                            <Button size="sm" variant="outline" onClick={() => handleVerify(document.id)}>
                                                <CheckCircle className="h-3 w-3" />
                                            </Button>
                                            <Button size="sm" variant="outline" onClick={() => handleReject(document.id)}>
                                                <XCircle className="h-3 w-3" />
                                            </Button>
                                        </>
                                    )}
                                    <Button size="sm" variant="destructive" onClick={() => handleDelete(document.id)}>
                                        <Trash2 className="h-3 w-3" />
                                    </Button>
                                </div>
                            </div>
                        ))
                    ) : (
                        <span className="text-muted-foreground">No documents uploaded</span>
                    )}
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
                <div className="mb-4 flex items-center gap-4">
                    <Input placeholder="Search documents..." value={search} onChange={(e) => setSearch(e.target.value)} className="max-w-sm" />
                    <Select value={studentId} onValueChange={(value) => setStudentId(value === 'all' ? '' : value)}>
                        <SelectTrigger className="w-[180px]">
                            <SelectValue placeholder="Filter by student" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Students</SelectItem>
                            {students.map((student) => (
                                <SelectItem key={student.value} value={String(student.value)}>
                                    {student.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
                <DataTable columns={columns} data={studentsWithDocuments.data} sorting={sorting} onSortingChange={setSorting} />
            </div>
        </AppLayout>
    );
}

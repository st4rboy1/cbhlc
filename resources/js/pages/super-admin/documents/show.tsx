import { DocumentStatusBadge } from '@/components/status-badges';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Calendar, CheckCircle, Download, FileText, User, XCircle } from 'lucide-react';

interface Guardian {
    id: number;
    first_name: string;
    last_name: string;
}

interface Student {
    id: number;
    first_name: string;
    last_name: string;
    guardians: Guardian[];
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

interface Props {
    document: Document;
    fileUrl: string;
}

export default function DocumentShow({ document, fileUrl }: Props) {
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

    const handleVerify = () => {
        if (confirm('Are you sure you want to verify this document?')) {
            router.post(`/super-admin/documents/${document.id}/verify`);
        }
    };

    const handleReject = () => {
        const reason = prompt('Please provide a reason for rejection (minimum 10 characters):');
        if (reason && reason.length >= 10) {
            router.post(`/super-admin/documents/${document.id}/reject`, { notes: reason });
        } else if (reason) {
            alert('Rejection reason must be at least 10 characters.');
        }
    };

    const handleDelete = () => {
        if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
            router.delete(`/super-admin/documents/${document.id}`);
        }
    };

    return (
        <AppLayout
            breadcrumbs={[
                { title: 'Super Admin', href: '/super-admin/dashboard' },
                { title: 'Documents', href: '/super-admin/documents' },
                { title: 'View Document', href: '#' },
            ]}
        >
            <Head title="View Document" />
            <div className="px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href="/super-admin/documents">
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Back to Documents
                            </Button>
                        </Link>
                        <div>
                            <h1 className="flex items-center gap-2 text-2xl font-bold">
                                {document.original_filename}
                                <DocumentStatusBadge status={document.verification_status} />
                            </h1>
                            <p className="text-sm text-muted-foreground">{getDocumentTypeLabel(document.document_type)}</p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" onClick={() => window.open(fileUrl, '_blank')}>
                            <Download className="mr-2 h-4 w-4" />
                            Download
                        </Button>
                        {document.verification_status === 'pending' && (
                            <>
                                <Button variant="outline" onClick={handleVerify}>
                                    <CheckCircle className="mr-2 h-4 w-4" />
                                    Verify
                                </Button>
                                <Button variant="outline" onClick={handleReject}>
                                    <XCircle className="mr-2 h-4 w-4" />
                                    Reject
                                </Button>
                            </>
                        )}
                        <Button variant="destructive" onClick={handleDelete}>
                            Delete Document
                        </Button>
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Student Information</CardTitle>
                            <User className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {document.student.first_name} {document.student.last_name}
                            </div>
                            <p className="text-xs text-muted-foreground">{document.student.guardians.length} guardian(s) registered</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">File Details</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatFileSize(document.file_size)}</div>
                            <p className="text-xs text-muted-foreground">{document.mime_type}</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Upload Date</CardTitle>
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{new Date(document.upload_date).toLocaleDateString()}</div>
                            <p className="text-xs text-muted-foreground">{new Date(document.upload_date).toLocaleTimeString()}</p>
                        </CardContent>
                    </Card>
                </div>

                {document.verified_by && (
                    <Card className="mt-4">
                        <CardHeader>
                            <CardTitle>Verification Information</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-2">
                            <div>
                                <span className="font-medium">Verified by:</span> {document.verified_by.name}
                            </div>
                            {document.verified_at && (
                                <div>
                                    <span className="font-medium">Verified at:</span> {new Date(document.verified_at).toLocaleString()}
                                </div>
                            )}
                            {document.rejection_reason && (
                                <div>
                                    <span className="font-medium">Rejection reason:</span>
                                    <p className="mt-1 rounded-md bg-destructive/10 p-2 text-sm">{document.rejection_reason}</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                )}

                <Card className="mt-4">
                    <CardHeader>
                        <CardTitle>Document Preview</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {document.mime_type.startsWith('image/') ? (
                            <img src={fileUrl} alt={document.original_filename} className="max-h-[600px] w-full rounded-lg object-contain" />
                        ) : (
                            <div className="flex min-h-[400px] items-center justify-center rounded-lg border-2 border-dashed">
                                <div className="text-center">
                                    <FileText className="mx-auto h-12 w-12 text-muted-foreground" />
                                    <p className="mt-2 text-sm text-muted-foreground">Preview not available for this file type</p>
                                    <Button variant="outline" className="mt-4" onClick={() => window.open(fileUrl, '_blank')}>
                                        <Download className="mr-2 h-4 w-4" />
                                        Download to View
                                    </Button>
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

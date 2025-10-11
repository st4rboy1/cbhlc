import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { useToast } from '@/hooks/use-toast';
import { router } from '@inertiajs/react';
import { Download, FileText, Trash2 } from 'lucide-react';
import React from 'react';

interface Document {
    id: number;
    document_type: string;
    original_filename: string;
    file_size: number;
    upload_date: string;
    verification_status: string;
    rejection_reason?: string | null;
    verified_by?: {
        id: number;
        name: string;
    };
    verified_at?: string | null;
}

interface DocumentListProps {
    documents: Document[];
    studentId: number;
    onDocumentDeleted?: () => void;
}

const DOCUMENT_TYPE_LABELS: Record<string, string> = {
    birth_certificate: 'Birth Certificate',
    report_card: 'Report Card',
    form_138: 'Form 138',
    good_moral: 'Good Moral Certificate',
    other: 'Other Document',
};

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
};

const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'verified':
            return (
                <Badge variant="default" className="bg-green-500">
                    Verified
                </Badge>
            );
        case 'rejected':
            return (
                <Badge variant="destructive">Rejected</Badge>
            );
        case 'pending':
        default:
            return (
                <Badge variant="secondary" className="bg-yellow-500 text-yellow-950">
                    Pending
                </Badge>
            );
    }
};

export function DocumentList({ documents, studentId, onDocumentDeleted }: DocumentListProps) {
    const { toast } = useToast();

    const handleDownload = (documentId: number) => {
        window.location.href = `/guardian/students/${studentId}/documents/${documentId}/download`;
    };

    const handleDelete = (documentId: number, filename: string) => {
        router.delete(`/guardian/students/${studentId}/documents/${documentId}`, {
            onSuccess: () => {
                toast({
                    title: 'Success',
                    description: 'Document deleted successfully',
                });
                if (onDocumentDeleted) {
                    onDocumentDeleted();
                }
            },
            onError: (errors) => {
                toast({
                    variant: 'destructive',
                    title: 'Error',
                    description: errors.document || 'Failed to delete document',
                });
            },
        });
    };

    if (documents.length === 0) {
        return (
            <Card>
                <CardContent className="flex flex-col items-center justify-center py-12">
                    <FileText className="h-12 w-12 text-muted-foreground mb-4" />
                    <h3 className="text-lg font-semibold mb-2">No Documents Uploaded</h3>
                    <p className="text-sm text-muted-foreground text-center max-w-sm">
                        Upload required documents to complete the enrollment process.
                    </p>
                </CardContent>
            </Card>
        );
    }

    return (
        <div className="space-y-4">
            {documents.map((document) => (
                <Card key={document.id}>
                    <CardContent className="p-4">
                        <div className="flex items-start gap-4">
                            {/* Icon */}
                            <div className="flex-shrink-0">
                                <div className="h-12 w-12 rounded-lg bg-primary/10 flex items-center justify-center">
                                    <FileText className="h-6 w-6 text-primary" />
                                </div>
                            </div>

                            {/* Document Info */}
                            <div className="flex-1 min-w-0">
                                <div className="flex items-start justify-between gap-2 mb-1">
                                    <div className="flex-1 min-w-0">
                                        <h4 className="font-medium truncate">{document.original_filename}</h4>
                                        <p className="text-sm text-muted-foreground">
                                            {DOCUMENT_TYPE_LABELS[document.document_type] || document.document_type}
                                        </p>
                                    </div>
                                    {getStatusBadge(document.verification_status)}
                                </div>

                                <div className="flex flex-wrap items-center gap-2 text-xs text-muted-foreground mt-2">
                                    <span>{formatFileSize(document.file_size)}</span>
                                    <span>•</span>
                                    <span>Uploaded {formatDate(document.upload_date)}</span>
                                    {document.verified_at && (
                                        <>
                                            <span>•</span>
                                            <span>Verified {formatDate(document.verified_at)}</span>
                                        </>
                                    )}
                                </div>

                                {/* Rejection Reason */}
                                {document.verification_status === 'rejected' && document.rejection_reason && (
                                    <div className="mt-2 p-2 bg-destructive/10 rounded-md">
                                        <p className="text-xs text-destructive">
                                            <strong>Reason:</strong> {document.rejection_reason}
                                        </p>
                                    </div>
                                )}

                                {/* Verified By */}
                                {document.verification_status === 'verified' && document.verified_by && (
                                    <p className="text-xs text-muted-foreground mt-2">
                                        Verified by {document.verified_by.name}
                                    </p>
                                )}
                            </div>

                            {/* Actions */}
                            <div className="flex items-center gap-2">
                                <Button
                                    variant="outline"
                                    size="icon"
                                    onClick={() => handleDownload(document.id)}
                                    title="Download document"
                                >
                                    <Download className="h-4 w-4" />
                                </Button>

                                {document.verification_status === 'pending' && (
                                    <AlertDialog>
                                        <AlertDialogTrigger asChild>
                                            <Button variant="outline" size="icon" title="Delete document">
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Delete Document?</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    Are you sure you want to delete "{document.original_filename}"? This action cannot be undone.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                <AlertDialogAction
                                                    onClick={() => handleDelete(document.id, document.original_filename)}
                                                    className="bg-destructive hover:bg-destructive/90"
                                                >
                                                    Delete
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                )}
                            </div>
                        </div>
                    </CardContent>
                </Card>
            ))}
        </div>
    );
}

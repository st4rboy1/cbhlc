import { DocumentList } from '@/components/documents/document-list';
import { DocumentUpload } from '@/components/documents/document-upload';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { ArrowLeft, Upload } from 'lucide-react';

interface Student {
    id: number;
    first_name: string;
    middle_name?: string;
    last_name: string;
    student_id: string;
}

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

interface Props {
    student: Student;
    documents: Document[];
}

export default function StudentDocumentsIndex({ student, documents }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Students', href: '/guardian/students' },
        { title: `${student.first_name} ${student.last_name}`, href: `/guardian/students/${student.id}` },
        { title: 'Documents', href: `/guardian/students/${student.id}/documents` },
    ];

    const handleDocumentUploaded = () => {
        router.reload();
    };

    const handleDocumentDeleted = () => {
        router.reload();
    };

    const getDocumentStats = () => {
        const total = documents.length;
        const verified = documents.filter((d) => d.verification_status === 'verified').length;
        const pending = documents.filter((d) => d.verification_status === 'pending').length;
        const rejected = documents.filter((d) => d.verification_status === 'rejected').length;

        return { total, verified, pending, rejected };
    };

    const stats = getDocumentStats();

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Documents - ${student.first_name} ${student.last_name}`} />

            <div className="px-4 py-6">
                <div className="mb-6">
                    <Button variant="ghost" size="sm" onClick={() => router.visit(`/guardian/students/${student.id}`)}>
                        <ArrowLeft className="mr-2 h-4 w-4" />
                        Back to Student
                    </Button>
                </div>

                <Heading
                    title="Student Documents"
                    description={`Manage documents for ${student.first_name} ${student.last_name} (${student.student_id})`}
                />

                {/* Stats Cards */}
                <div className="mb-6 grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="pb-2">
                            <CardDescription>Total Documents</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="pb-2">
                            <CardDescription>Verified</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">{stats.verified}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="pb-2">
                            <CardDescription>Pending</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-yellow-600">{stats.pending}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="pb-2">
                            <CardDescription>Rejected</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">{stats.rejected}</div>
                        </CardContent>
                    </Card>
                </div>

                <Tabs defaultValue="documents" className="space-y-6">
                    <TabsList>
                        <TabsTrigger value="documents">My Documents</TabsTrigger>
                        <TabsTrigger value="upload">
                            <Upload className="mr-2 h-4 w-4" />
                            Upload New
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent value="documents" className="space-y-4">
                        <DocumentList documents={documents} studentId={student.id} onDocumentDeleted={handleDocumentDeleted} />
                    </TabsContent>

                    <TabsContent value="upload" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Upload Document</CardTitle>
                                <CardDescription>Upload required documents for enrollment verification</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <DocumentUpload studentId={student.id} onSuccess={handleDocumentUploaded} />
                            </CardContent>
                        </Card>

                        {/* Information Cards */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Required Documents</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <ul className="list-inside list-disc space-y-2 text-sm text-muted-foreground">
                                    <li>
                                        <strong>Birth Certificate:</strong> Official copy of student's birth certificate
                                    </li>
                                    <li>
                                        <strong>Report Card:</strong> Most recent report card or academic records
                                    </li>
                                    <li>
                                        <strong>Form 138:</strong> Transfer credentials for students coming from other schools
                                    </li>
                                    <li>
                                        <strong>Good Moral Certificate:</strong> Certificate of good moral character from previous school
                                    </li>
                                </ul>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Upload Guidelines</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2 text-sm text-muted-foreground">
                                <p>
                                    <strong>Accepted formats:</strong> JPEG, PNG, PDF
                                </p>
                                <p>
                                    <strong>Maximum file size:</strong> 50MB per document
                                </p>
                                <p>
                                    <strong>Image quality:</strong> Ensure documents are clear and legible
                                </p>
                                <p>
                                    <strong>Processing time:</strong> Documents will be verified within 2-3 business days
                                </p>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </AppLayout>
    );
}

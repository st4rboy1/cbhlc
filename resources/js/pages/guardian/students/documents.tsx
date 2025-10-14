import { DocumentUpload } from '@/components/documents/document-upload';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

interface Student {
    id: number;
    first_name: string;
    middle_name?: string;
    last_name: string;
    student_id: string;
}

interface Props {
    student: Student;
}

export default function StudentDocuments({ student }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Students', href: '/guardian/students' },
        { title: `${student.first_name} ${student.last_name}`, href: `/guardian/students/${student.id}` },
        { title: 'Upload Document', href: `/guardian/students/${student.id}/documents/upload` },
    ];

    const handleSuccess = () => {
        router.visit(`/guardian/students/${student.id}`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Upload Document - ${student.first_name} ${student.last_name}`} />

            <div className="px-4 py-6">
                <div className="mb-6">
                    <Button variant="ghost" size="sm" onClick={() => router.visit(`/guardian/students/${student.id}`)}>
                        <ArrowLeft className="mr-2 h-4 w-4" />
                        Back to Student
                    </Button>
                </div>

                <Heading
                    title="Upload Document"
                    description={`Upload documents for ${student.first_name} ${student.last_name} (${student.student_id})`}
                />

                <div className="mx-auto max-w-2xl space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Document Upload</CardTitle>
                            <CardDescription>Upload required documents for enrollment verification</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <DocumentUpload studentId={student.id} onSuccess={handleSuccess} />
                        </CardContent>
                    </Card>

                    {/* Information Card */}
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
                </div>
            </div>
        </AppLayout>
    );
}

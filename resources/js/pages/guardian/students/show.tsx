import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import axios from 'axios';
import {
    Calendar,
    CheckCircle2,
    Clock,
    Download,
    Edit,
    Eye,
    FileText,
    GraduationCap,
    Mail,
    MapPin,
    Phone,
    Upload,
    User,
    XCircle,
} from 'lucide-react';
import { useEffect, useState } from 'react';

interface Enrollment {
    id: number;
    school_year: string;
    grade_level: string;
    quarter: string;
    status: string;
    payment_status: string;
    created_at: string;
}

interface Document {
    id: number;
    document_type: string;
    document_type_label: string;
    original_filename: string;
    file_size: number;
    upload_date: string;
    verification_status: string;
}

interface Student {
    id: number;
    student_id: string;
    first_name: string;
    middle_name: string;
    last_name: string;
    birthdate: string;
    gender: string;
    address: string;
    contact_number: string;
    email: string;
    grade_level: string;
    section: string | null;
    birth_place: string;
    nationality: string;
    religion: string;
    enrollments: Enrollment[];
    documents: Document[];
}

interface Props {
    student: Student;
}

const statusColors = {
    pending: 'secondary',
    enrolled: 'default',
    rejected: 'destructive',
    completed: 'outline',
} as const;

const paymentStatusColors = {
    pending: 'secondary',
    partial: 'outline',
    paid: 'default',
    overdue: 'destructive',
} as const;

export default function GuardianStudentsShow({ student }: Props) {
    const [downloadingId, setDownloadingId] = useState<number | null>(null);
    const [imageUrls, setImageUrls] = useState<Record<number, string>>({});
    const [viewingImageId, setViewingImageId] = useState<number | null>(null);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Guardian', href: '/guardian/dashboard' },
        { title: 'Students', href: '/guardian/students' },
        { title: `${student.first_name} ${student.last_name}`, href: `/guardian/students/${student.id}` },
    ];

    const fullName = `${student.first_name} ${student.middle_name ? student.middle_name + ' ' : ''}${student.last_name}`;

    const getDocumentStats = () => {
        const total = student.documents?.length || 0;
        const verified = student.documents?.filter((d) => d.verification_status === 'verified').length || 0;
        const pending = student.documents?.filter((d) => d.verification_status === 'pending').length || 0;
        const rejected = student.documents?.filter((d) => d.verification_status === 'rejected').length || 0;

        return { total, verified, pending, rejected };
    };

    const stats = getDocumentStats();

    // Load signed URLs for all documents on mount
    useEffect(() => {
        const loadImageUrls = async () => {
            if (!student.documents || student.documents.length === 0) return;

            const urls: Record<number, string> = {};
            await Promise.all(
                student.documents.map(async (document) => {
                    try {
                        const response = await axios.get(`/guardian/students/${student.id}/documents/${document.id}`);
                        urls[document.id] = response.data.url;
                    } catch (error) {
                        console.error(`Failed to load image for document ${document.id}:`, error);
                    }
                }),
            );
            setImageUrls(urls);
        };

        loadImageUrls();
    }, [student.id, student.documents]);

    const handleDownload = async (documentId: number) => {
        try {
            setDownloadingId(documentId);

            // Get signed URL
            const response = await axios.get(`/guardian/students/${student.id}/documents/${documentId}`);
            const { url } = response.data;

            // Trigger download
            window.location.href = url;
        } catch (error) {
            console.error('Download failed:', error);
            alert('Failed to download document. Please try again.');
        } finally {
            setDownloadingId(null);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={fullName} />
            <div className="px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">{fullName}</h1>
                        <p className="text-muted-foreground">Student ID: {student.student_id}</p>
                    </div>
                    <Link href={`/guardian/students/${student.id}/edit`}>
                        <Button>
                            <Edit className="mr-2 h-4 w-4" />
                            Edit Information
                        </Button>
                    </Link>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    {/* Personal Information Card */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <User className="h-5 w-5" />
                                Personal Information
                            </CardTitle>
                            <CardDescription>Basic student details</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-3">
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-medium text-muted-foreground">Full Name:</span>
                                    <span className="font-semibold">{fullName}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-medium text-muted-foreground">Student ID:</span>
                                    <span className="font-semibold">{student.student_id}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-medium text-muted-foreground">Birthdate:</span>
                                    <span className="font-semibold">
                                        {student.birthdate
                                            ? new Date(student.birthdate).toLocaleDateString('en-US', {
                                                  year: 'numeric',
                                                  month: 'long',
                                                  day: 'numeric',
                                              })
                                            : 'N/A'}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-medium text-muted-foreground">Birth Place:</span>
                                    <span className="font-semibold">{student.birth_place || 'N/A'}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-medium text-muted-foreground">Gender:</span>
                                    <span className="font-semibold capitalize">{student.gender || 'N/A'}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-medium text-muted-foreground">Nationality:</span>
                                    <span className="font-semibold">{student.nationality || 'N/A'}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-medium text-muted-foreground">Religion:</span>
                                    <span className="font-semibold">{student.religion || 'N/A'}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Academic Information Card */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <GraduationCap className="h-5 w-5" />
                                Academic Information
                            </CardTitle>
                            <CardDescription>Current academic status</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-3">
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-medium text-muted-foreground">Current Grade:</span>
                                    <span className="font-semibold">{student.grade_level || 'Not enrolled'}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-medium text-muted-foreground">Section:</span>
                                    <span className="font-semibold">{student.section || 'Not assigned'}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-medium text-muted-foreground">Total Enrollments:</span>
                                    <span className="font-semibold">{student.enrollments.length}</span>
                                </div>
                            </div>

                            <div className="mt-4 border-t pt-4">
                                <Link href={`/guardian/enrollments/create?student_id=${student.id}`}>
                                    <Button className="w-full">
                                        <Calendar className="mr-2 h-4 w-4" />
                                        Enroll for New School Year
                                    </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Contact Information Card */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Phone className="h-5 w-5" />
                                Contact Information
                            </CardTitle>
                            <CardDescription>How to reach this student</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-3">
                                {student.contact_number && (
                                    <div className="flex items-start gap-2">
                                        <Phone className="mt-0.5 h-4 w-4 text-muted-foreground" />
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-muted-foreground">Phone Number</p>
                                            <p className="font-semibold">{student.contact_number}</p>
                                        </div>
                                    </div>
                                )}
                                {student.email && (
                                    <div className="flex items-start gap-2">
                                        <Mail className="mt-0.5 h-4 w-4 text-muted-foreground" />
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-muted-foreground">Email Address</p>
                                            <p className="font-semibold">{student.email}</p>
                                        </div>
                                    </div>
                                )}
                                {student.address && (
                                    <div className="flex items-start gap-2">
                                        <MapPin className="mt-0.5 h-4 w-4 text-muted-foreground" />
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-muted-foreground">Home Address</p>
                                            <p className="font-semibold">{student.address}</p>
                                        </div>
                                    </div>
                                )}
                                {!student.contact_number && !student.email && !student.address && (
                                    <p className="py-4 text-center text-sm text-muted-foreground">No contact information available</p>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Enrollment History */}
                <Card className="mt-6">
                    <CardHeader>
                        <CardTitle>Enrollment History</CardTitle>
                        <CardDescription>Past and current enrollment records for {student.first_name}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {student.enrollments.length > 0 ? (
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>School Year</TableHead>
                                        <TableHead>Grade Level</TableHead>
                                        <TableHead>Quarter</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Payment Status</TableHead>
                                        <TableHead>Date Enrolled</TableHead>
                                        <TableHead className="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {student.enrollments.map((enrollment) => (
                                        <TableRow key={enrollment.id}>
                                            <TableCell className="font-medium">{enrollment.school_year}</TableCell>
                                            <TableCell>{enrollment.grade_level}</TableCell>
                                            <TableCell>{enrollment.quarter}</TableCell>
                                            <TableCell>
                                                <Badge variant={statusColors[enrollment.status as keyof typeof statusColors] || 'default'}>
                                                    {enrollment.status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <Badge
                                                    variant={
                                                        paymentStatusColors[enrollment.payment_status as keyof typeof paymentStatusColors] ||
                                                        'default'
                                                    }
                                                >
                                                    {enrollment.payment_status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                {new Date(enrollment.created_at).toLocaleDateString('en-US', {
                                                    year: 'numeric',
                                                    month: 'short',
                                                    day: 'numeric',
                                                })}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <Link href={`/guardian/enrollments/${enrollment.id}`}>
                                                    <Button size="sm" variant="outline">
                                                        View Details
                                                    </Button>
                                                </Link>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        ) : (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <GraduationCap className="mb-4 h-12 w-12 text-muted-foreground" />
                                <p className="mb-2 text-lg font-semibold">No Enrollment History</p>
                                <p className="mb-4 text-sm text-muted-foreground">This student has not been enrolled yet</p>
                                <Link href={`/guardian/enrollments/create?student_id=${student.id}`}>
                                    <Button>
                                        <Calendar className="mr-2 h-4 w-4" />
                                        Create First Enrollment
                                    </Button>
                                </Link>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Documents */}
                <Card className="mt-6">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <FileText className="h-5 w-5" />
                            Required Documents
                        </CardTitle>
                        <CardDescription>Uploaded documents for {student.first_name}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {/* Document Statistics */}
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

                        {student.documents && student.documents.length > 0 ? (
                            <div className="space-y-6">
                                {student.documents.map((document) => (
                                    <div key={document.id} className="space-y-3 rounded-lg border p-4">
                                        <div className="flex items-start justify-between">
                                            <div className="flex items-center gap-3">
                                                <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                                    <FileText className="h-5 w-5 text-primary" />
                                                </div>
                                                <div>
                                                    <p className="font-medium">{document.document_type_label}</p>
                                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                                        <span>{document.original_filename}</span>
                                                        <span>•</span>
                                                        <span>{(document.file_size / 1024 / 1024).toFixed(2)} MB</span>
                                                        <span>•</span>
                                                        <span>
                                                            Uploaded{' '}
                                                            {new Date(document.upload_date).toLocaleDateString('en-US', {
                                                                year: 'numeric',
                                                                month: 'short',
                                                                day: 'numeric',
                                                            })}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                {document.verification_status === 'pending' && (
                                                    <Badge variant="secondary" className="flex items-center gap-1">
                                                        <Clock className="h-3 w-3" />
                                                        Pending Review
                                                    </Badge>
                                                )}
                                                {document.verification_status === 'verified' && (
                                                    <Badge variant="default" className="flex items-center gap-1">
                                                        <CheckCircle2 className="h-3 w-3" />
                                                        Verified
                                                    </Badge>
                                                )}
                                                {document.verification_status === 'rejected' && (
                                                    <Badge variant="destructive" className="flex items-center gap-1">
                                                        <XCircle className="h-3 w-3" />
                                                        Rejected
                                                    </Badge>
                                                )}
                                            </div>
                                        </div>

                                        {/* Image Preview */}
                                        {imageUrls[document.id] ? (
                                            <div className="space-y-2">
                                                <img
                                                    src={imageUrls[document.id]}
                                                    alt={document.document_type_label}
                                                    className="h-auto w-full max-w-2xl rounded-lg border object-contain"
                                                />
                                                <div className="flex gap-2">
                                                    <Button variant="outline" size="sm" onClick={() => setViewingImageId(document.id)}>
                                                        <Eye className="mr-2 h-4 w-4" />
                                                        View Full Size
                                                    </Button>
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => handleDownload(document.id)}
                                                        disabled={downloadingId === document.id}
                                                    >
                                                        <Download className="mr-2 h-4 w-4" />
                                                        {downloadingId === document.id ? 'Downloading...' : 'Download'}
                                                    </Button>
                                                </div>
                                            </div>
                                        ) : (
                                            <div className="flex h-32 items-center justify-center rounded-lg bg-muted">
                                                <p className="text-sm text-muted-foreground">Loading image...</p>
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <Upload className="mb-4 h-12 w-12 text-muted-foreground" />
                                <p className="mb-2 text-lg font-semibold">No Documents Uploaded</p>
                                <p className="text-sm text-muted-foreground">Documents are required for enrollment processing</p>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Full Size Image Dialog */}
            <Dialog open={viewingImageId !== null} onOpenChange={(open) => !open && setViewingImageId(null)}>
                <DialogContent className="max-w-5xl">
                    <DialogHeader>
                        <DialogTitle>{viewingImageId && student.documents.find((d) => d.id === viewingImageId)?.document_type_label}</DialogTitle>
                    </DialogHeader>
                    <div className="flex justify-center">
                        {viewingImageId && imageUrls[viewingImageId] && (
                            <img
                                src={imageUrls[viewingImageId]}
                                alt="Document"
                                className="h-auto max-h-[80vh] w-auto max-w-full rounded-lg object-contain"
                            />
                        )}
                    </div>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}

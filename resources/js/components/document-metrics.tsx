import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AlertCircle, CheckCircle, FileCheck, FileText, FileX, TrendingUp, UserCheck, Users, XCircle } from 'lucide-react';

interface DocumentMetrics {
    totalDocuments: number;
    pendingDocuments: number;
    verifiedDocuments: number;
    rejectedDocuments: number;
    studentsAllDocsVerified: number;
    studentsPendingDocs: number;
    studentsRejectedDocs: number;
}

interface Props {
    metrics: DocumentMetrics;
}

export function DocumentMetrics({ metrics }: Props) {
    const verificationRate = metrics.totalDocuments > 0 ? Math.round((metrics.verifiedDocuments / metrics.totalDocuments) * 100) : 0;

    return (
        <div className="space-y-6">
            {/* Document Status Overview */}
            <div>
                <h2 className="mb-4 text-lg font-semibold">Document Verification Status</h2>
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Documents</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{metrics.totalDocuments}</div>
                            <p className="text-xs text-muted-foreground">All uploaded documents</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pending Verification</CardTitle>
                            <AlertCircle className="h-4 w-4 text-yellow-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{metrics.pendingDocuments}</div>
                            {metrics.pendingDocuments > 0 ? (
                                <Badge variant="secondary" className="mt-1">
                                    Requires review
                                </Badge>
                            ) : (
                                <p className="text-xs text-muted-foreground">All caught up!</p>
                            )}
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Verified</CardTitle>
                            <CheckCircle className="h-4 w-4 text-green-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{metrics.verifiedDocuments}</div>
                            <p className="flex items-center gap-1 text-xs text-muted-foreground">
                                <TrendingUp className="h-3 w-3 text-green-500" />
                                <span>{verificationRate}% verification rate</span>
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Rejected</CardTitle>
                            <XCircle className="h-4 w-4 text-red-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{metrics.rejectedDocuments}</div>
                            <p className="text-xs text-muted-foreground">Need resubmission</p>
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Student Document Status */}
            <div>
                <h2 className="mb-4 text-lg font-semibold">Student Document Completion</h2>
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">All Documents Verified</CardTitle>
                            <FileCheck className="h-4 w-4 text-green-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{metrics.studentsAllDocsVerified}</div>
                            <p className="flex items-center gap-1 text-xs text-muted-foreground">
                                <UserCheck className="h-3 w-3 text-green-500" />
                                <span>Complete verification</span>
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pending Documents</CardTitle>
                            <FileText className="h-4 w-4 text-yellow-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{metrics.studentsPendingDocs}</div>
                            <p className="flex items-center gap-1 text-xs text-muted-foreground">
                                <Users className="h-3 w-3 text-yellow-500" />
                                <span>Students with pending docs</span>
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Rejected Documents</CardTitle>
                            <FileX className="h-4 w-4 text-red-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{metrics.studentsRejectedDocs}</div>
                            <p className="flex items-center gap-1 text-xs text-muted-foreground">
                                <Users className="h-3 w-3 text-red-500" />
                                <span>Students need to resubmit</span>
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    );
}

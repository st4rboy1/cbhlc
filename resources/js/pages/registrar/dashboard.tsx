import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { AlertCircle, Calendar, CheckCircle, Clock, DollarSign, FileText, Settings, Users, XCircle } from 'lucide-react';
import * as React from 'react';

// Dialog components for the modal
import InputError from '@/components/input-error';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface EnrollmentStats {
    pending: number;
    approved: number;
    rejected: number;
    total: number;
}

interface StudentStats {
    total_students: number;
    new_students: number;
    enrolled_students: number;
}

interface PaymentStats {
    pending: number;
    partial: number;
    paid: number;
    overdue: number;
}

interface RecentApplication {
    id: number;
    student_name: string;
    grade_level: string;
    status: string;
    submission_date: string;
    payment_status: string;
}

interface Deadline {
    title: string;
    date: string;
    daysLeft: number;
}

interface GradeDistribution {
    grade: string;
    count: number;
}

interface Props {
    enrollmentStats: EnrollmentStats;
    recentApplications: RecentApplication[];
    studentStats: StudentStats;
    paymentStats: PaymentStats;
    upcomingDeadlines: Deadline[];
    gradeLevelDistribution: GradeDistribution[];
}

export default function RegistrarDashboard({
    enrollmentStats,
    recentApplications,
    studentStats,
    paymentStats,
    upcomingDeadlines,
    gradeLevelDistribution,
}: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Registrar Dashboard',
            href: '/registrar/dashboard',
        },
    ];

    // State for Approve Enrollment Modal
    const [showApproveModal, setShowApproveModal] = React.useState(false);
    const [enrollmentToApprove, setEnrollmentToApprove] = React.useState<RecentApplication | null>(null);
    const [approveRemarks, setApproveRemarks] = React.useState('');
    const [approveErrors, setApproveErrors] = React.useState<Record<string, string>>({});

    // State for Reject Enrollment Modal
    const [showRejectModal, setShowRejectModal] = React.useState(false);
    const [enrollmentToReject, setEnrollmentToReject] = React.useState<RecentApplication | null>(null);
    const [rejectReason, setRejectReason] = React.useState('');
    const [rejectErrors, setRejectErrors] = React.useState<Record<string, string>>({});

    const handleQuickApproveClick = React.useCallback((application: RecentApplication) => {
        setEnrollmentToApprove(application);
        setApproveRemarks('');
        setApproveErrors({});
        setShowApproveModal(true);
    }, []);

    const handleQuickRejectClick = React.useCallback((application: RecentApplication) => {
        setEnrollmentToReject(application);
        setRejectReason('');
        setRejectErrors({});
        setShowRejectModal(true);
    }, []);

    const handleApproveSubmit = () => {
        if (!enrollmentToApprove) return;

        router.post(
            `/registrar/enrollments/${enrollmentToApprove.id}/quick-approve`,
            { remarks: approveRemarks },
            {
                onSuccess: () => {
                    setShowApproveModal(false);
                    setEnrollmentToApprove(null);
                    setApproveRemarks('');
                    setApproveErrors({});
                },
                onError: (errors) => {
                    setApproveErrors(errors);
                },
            },
        );
    };

    const handleRejectSubmit = () => {
        if (!enrollmentToReject) return;

        router.post(
            `/registrar/enrollments/${enrollmentToReject.id}/quick-reject`,
            { reason: rejectReason },
            {
                onSuccess: () => {
                    setShowRejectModal(false);
                    setEnrollmentToReject(null);
                    setRejectReason('');
                    setRejectErrors({});
                },
                onError: (errors) => {
                    setRejectErrors(errors);
                },
            },
        );
    };

    const getStatusBadge = (status: string) => {
        switch (status.toLowerCase()) {
            case 'pending':
                return (
                    <Badge variant="secondary" className="bg-yellow-100 text-yellow-800">
                        Pending
                    </Badge>
                );
            case 'enrolled':
            case 'approved':
                return (
                    <Badge variant="secondary" className="bg-green-100 text-green-800">
                        Enrolled
                    </Badge>
                );
            case 'rejected':
                return <Badge variant="destructive">Rejected</Badge>;
            case 'completed':
                return (
                    <Badge variant="default" className="bg-blue-100 text-blue-800">
                        Completed
                    </Badge>
                );
            default:
                return <Badge variant="outline">{status}</Badge>;
        }
    };

    const getPaymentStatusBadge = (status: string) => {
        switch (status.toLowerCase()) {
            case 'pending':
                return (
                    <Badge variant="outline" className="border-orange-500 text-orange-700">
                        Pending
                    </Badge>
                );
            case 'partial':
                return (
                    <Badge variant="outline" className="border-blue-500 text-blue-700">
                        Partial
                    </Badge>
                );
            case 'paid':
                return (
                    <Badge variant="outline" className="border-green-500 text-green-700">
                        Paid
                    </Badge>
                );
            case 'overdue':
                return (
                    <Badge variant="outline" className="border-red-500 text-red-700">
                        Overdue
                    </Badge>
                );
            default:
                return <Badge variant="outline">{status}</Badge>;
        }
    };

    const getDeadlineColor = (daysLeft: number) => {
        if (daysLeft < 0) return 'text-gray-500';
        if (daysLeft <= 7) return 'text-red-600';
        if (daysLeft <= 14) return 'text-orange-600';
        return 'text-green-600';
    };

    const formatDeadlineDays = (daysLeft: number) => {
        if (daysLeft < 0) return `${Math.abs(daysLeft)} days ago`;
        if (daysLeft === 0) return 'Today';
        if (daysLeft === 1) return '1 day left';
        return `${daysLeft} days left`;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Registrar Dashboard" />

            <div className="px-4 py-6">
                <Heading title="Registrar Dashboard" description="Manage enrollment applications and student records" />

                <div className="space-y-6">
                    {/* Overview Cards */}
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Total Enrollments</CardTitle>
                                <FileText className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{enrollmentStats.total}</div>
                                <p className="text-xs text-muted-foreground">All time enrollment applications</p>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Pending Applications</CardTitle>
                                <Clock className="h-4 w-4 text-yellow-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{enrollmentStats.pending}</div>
                                <p className="text-xs text-muted-foreground">Awaiting review</p>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Approved</CardTitle>
                                <CheckCircle className="h-4 w-4 text-green-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{enrollmentStats.approved}</div>
                                <p className="text-xs text-muted-foreground">Successfully enrolled</p>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Rejected</CardTitle>
                                <XCircle className="h-4 w-4 text-red-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{enrollmentStats.rejected}</div>
                                <p className="text-xs text-muted-foreground">Applications rejected</p>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Secondary Stats Row */}
                    <div className="grid gap-4 md:grid-cols-3">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Student Statistics</CardTitle>
                                <Users className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent className="space-y-2">
                                <div className="flex justify-between">
                                    <span className="text-sm text-muted-foreground">Total Students</span>
                                    <span className="font-semibold">{studentStats.total_students}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm text-muted-foreground">New Students</span>
                                    <span className="font-semibold">{studentStats.new_students}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm text-muted-foreground">Enrolled</span>
                                    <span className="font-semibold">{studentStats.enrolled_students}</span>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Payment Overview</CardTitle>
                                <DollarSign className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent className="space-y-2">
                                <div className="flex justify-between">
                                    <span className="text-sm text-muted-foreground">Pending</span>
                                    <span className="font-semibold text-orange-600">{paymentStats.pending}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm text-muted-foreground">Partial</span>
                                    <span className="font-semibold text-blue-600">{paymentStats.partial}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm text-muted-foreground">Paid</span>
                                    <span className="font-semibold text-green-600">{paymentStats.paid}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm text-muted-foreground">Overdue</span>
                                    <span className="font-semibold text-red-600">{paymentStats.overdue}</span>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Upcoming Deadlines</CardTitle>
                                <Calendar className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent className="space-y-2">
                                {upcomingDeadlines.map((deadline, index) => (
                                    <div key={index} className="flex flex-col space-y-1">
                                        <div className="flex justify-between">
                                            <span className="text-sm font-medium">{deadline.title}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-xs text-muted-foreground">{deadline.date}</span>
                                            <span className={`text-xs font-semibold ${getDeadlineColor(deadline.daysLeft)}`}>
                                                {formatDeadlineDays(deadline.daysLeft)}
                                            </span>
                                        </div>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Grade Level Distribution */}
                    {gradeLevelDistribution.length > 0 && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-sm font-medium">Grade Level Distribution</CardTitle>
                                <CardDescription>Current enrollment by grade level</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2">
                                    {gradeLevelDistribution.map((item) => (
                                        <div key={item.grade} className="flex items-center gap-2">
                                            <div className="w-24 text-sm">{item.grade}</div>
                                            <div className="flex-1">
                                                <Progress
                                                    value={(item.count / Math.max(...gradeLevelDistribution.map((g) => g.count))) * 100}
                                                    className="h-2"
                                                />
                                            </div>
                                            <div className="w-12 text-right text-sm">{item.count}</div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Recent Applications */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Recent Applications</CardTitle>
                            <CardDescription>Latest enrollment applications requiring attention</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Student Name</TableHead>
                                        <TableHead>Grade Level</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Payment</TableHead>
                                        <TableHead>Submission Date</TableHead>
                                        <TableHead className="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {recentApplications.length > 0 ? (
                                        recentApplications.map((application) => (
                                            <TableRow key={application.id}>
                                                <TableCell className="font-medium">{application.student_name}</TableCell>
                                                <TableCell>{application.grade_level}</TableCell>
                                                <TableCell>{getStatusBadge(application.status)}</TableCell>
                                                <TableCell>{getPaymentStatusBadge(application.payment_status)}</TableCell>
                                                <TableCell>{application.submission_date}</TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex justify-end gap-2">
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            onClick={() => router.visit(`/registrar/enrollments/${application.id}`)}
                                                        >
                                                            View
                                                        </Button>
                                                        {application.status.toLowerCase() === 'pending' && (
                                                            <>
                                                                <Button
                                                                    size="sm"
                                                                    variant="default"
                                                                    className="bg-green-600 hover:bg-green-700"
                                                                    onClick={() => handleQuickApproveClick(application)}
                                                                >
                                                                    Approve
                                                                </Button>
                                                                <Button
                                                                    size="sm"
                                                                    variant="destructive"
                                                                    onClick={() => handleQuickRejectClick(application)}
                                                                >
                                                                    Reject
                                                                </Button>
                                                            </>
                                                        )}
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    ) : (
                                        <TableRow>
                                            <TableCell colSpan={6} className="text-center text-muted-foreground">
                                                No enrollment applications found
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>

                    {/* Quick Actions */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                            <CardDescription>Frequently used registrar functions</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-2 md:grid-cols-4">
                                <Button variant="outline" className="w-full" onClick={() => router.visit('/registrar/enrollments')}>
                                    <FileText className="mr-2 h-4 w-4" />
                                    View All Enrollments
                                </Button>
                                <Button variant="outline" className="w-full" onClick={() => router.visit('/registrar/students')}>
                                    <Users className="mr-2 h-4 w-4" />
                                    Manage Students
                                </Button>
                                <Button variant="outline" className="w-full" onClick={() => router.visit('/registrar/documents/pending')}>
                                    <AlertCircle className="mr-2 h-4 w-4" />
                                    Pending Documents
                                </Button>
                                <Button variant="outline" className="w-full" onClick={() => router.visit('/settings/profile')}>
                                    <Settings className="mr-2 h-4 w-4" />
                                    Profile Settings
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Approve Enrollment Modal */}
            <Dialog open={showApproveModal} onOpenChange={setShowApproveModal}>
                <DialogContent className="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>Approve Enrollment Application</DialogTitle>
                        <DialogDescription>
                            Provide any remarks for approving enrollment #{enrollmentToApprove?.id} - {enrollmentToApprove?.student_name}.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="remarks">Remarks (Optional)</Label>
                            <Textarea
                                id="remarks"
                                placeholder="Enter remarks..."
                                value={approveRemarks}
                                onChange={(e) => setApproveRemarks(e.target.value)}
                                className={approveErrors.remarks ? 'border-destructive' : ''}
                            />
                            <InputError message={approveErrors.remarks} className="mt-0" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setShowApproveModal(false)}>
                            Cancel
                        </Button>
                        <Button onClick={handleApproveSubmit}>Approve</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Reject Enrollment Modal */}
            <Dialog open={showRejectModal} onOpenChange={setShowRejectModal}>
                <DialogContent className="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>Reject Enrollment Application</DialogTitle>
                        <DialogDescription>
                            Provide a reason for rejecting enrollment #{enrollmentToReject?.id} - {enrollmentToReject?.student_name}.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="reason">Reason</Label>
                            <Textarea
                                id="reason"
                                placeholder="Enter rejection reason..."
                                value={rejectReason}
                                onChange={(e) => setRejectReason(e.target.value)}
                                className={rejectErrors.reason ? 'border-destructive' : ''}
                            />
                            <InputError message={rejectErrors.reason} className="mt-0" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setShowRejectModal(false)}>
                            Cancel
                        </Button>
                        <Button onClick={handleRejectSubmit} disabled={!rejectReason.trim()}>
                            Reject
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}

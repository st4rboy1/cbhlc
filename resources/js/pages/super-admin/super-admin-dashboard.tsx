import { DocumentMetrics } from '@/components/document-metrics';
import { ExpandedDashboard } from '@/components/expanded-dashboard';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ChevronRight, CreditCard, DollarSign, FileText, School, UserPlus } from 'lucide-react';

interface Props {
    stats: {
        // Core metrics
        total_students: number;
        active_enrollments: number;
        pending_enrollments: number;
        total_revenue: number;

        // User Journey Metrics
        total_users: number;
        verified_users: number;
        unverified_users: number;

        // Guardian Journey Metrics
        total_guardians: number;
        guardians_with_students: number;
        guardians_without_students: number;
        guardians_with_students_no_enrollments: number;

        // Student Journey Metrics
        students_with_enrollments: number;
        students_without_enrollments: number;

        // Enrollment metrics
        approved_enrollments: number;
        completed_enrollments: number;
        rejected_enrollments: number;
        enrollments_needing_payment: number;

        // Payment metrics
        total_invoices: number;
        paid_invoices: number;
        partial_payments: number;
        pending_payments: number;
        total_collected: number;
        total_balance: number;
        collection_rate: number;

        // Financial Projections
        total_expected_revenue: number;
        potential_incoming_revenue: number;

        // Transaction metrics
        total_payments: number;
        recent_payments_count: number;

        // Document Verification Metrics
        total_documents: number;
        pending_documents: number;
        verified_documents: number;
        rejected_documents: number;
        students_all_docs_verified: number;
        students_pending_docs: number;
        students_rejected_docs: number;
    };
    activeSchoolYear: SchoolYear | null;
    schoolYears: SchoolYear[];
}

interface SchoolYear {
    id: number;
    name: string;
    start_year: number;
    end_year: number;
    start_date: string;
    end_date: string;
    status: string;
    is_active: boolean;
}

export default function SuperAdminDashboardPage({ stats, activeSchoolYear }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [{ title: 'Super Admin Dashboard', href: '/super-admin/dashboard' }];

    // Convert snake_case to camelCase for the shared component
    const dashboardStats = {
        totalStudents: stats.total_students,
        activeEnrollments: stats.active_enrollments,
        pendingApplications: stats.pending_enrollments,
        totalUsers: stats.total_users,
        verifiedUsers: stats.verified_users,
        unverifiedUsers: stats.unverified_users,
        totalGuardians: stats.total_guardians,
        guardiansWithStudents: stats.guardians_with_students,
        guardiansWithoutStudents: stats.guardians_without_students,
        guardiansWithStudentsNoEnrollments: stats.guardians_with_students_no_enrollments,
        studentsWithEnrollments: stats.students_with_enrollments,
        studentsWithoutEnrollments: stats.students_without_enrollments,
        approvedEnrollments: stats.approved_enrollments,
        completedEnrollments: stats.completed_enrollments,
        rejectedEnrollments: stats.rejected_enrollments,
        enrollmentsNeedingPayment: stats.enrollments_needing_payment,
        totalInvoices: stats.total_invoices,
        paidInvoices: stats.paid_invoices,
        partialPayments: stats.partial_payments,
        pendingPayments: stats.pending_payments,
        totalCollected: stats.total_collected,
        totalBalance: stats.total_balance,
        collectionRate: stats.collection_rate,
        totalExpectedRevenue: stats.total_expected_revenue,
        potentialIncomingRevenue: stats.potential_incoming_revenue,
        totalPayments: stats.total_payments,
        recentPaymentsCount: stats.recent_payments_count,
        totalRevenue: stats.total_revenue,
    };

    const documentMetrics = {
        totalDocuments: stats.total_documents,
        pendingDocuments: stats.pending_documents,
        verifiedDocuments: stats.verified_documents,
        rejectedDocuments: stats.rejected_documents,
        studentsAllDocsVerified: stats.students_all_docs_verified,
        studentsPendingDocs: stats.students_pending_docs,
        studentsRejectedDocs: stats.students_rejected_docs,
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Super Admin Dashboard" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Super Admin Dashboard{activeSchoolYear ? ` - ${activeSchoolYear.name}` : ''}</h1>

                <ExpandedDashboard stats={dashboardStats} />

                <div className="mt-6">
                    <DocumentMetrics metrics={documentMetrics} />
                </div>

                <div className="mt-6 grid gap-6 md:grid-cols-2">
                    {/* Quick Actions */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                            <CardDescription>Common administrative tasks</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            <Button variant="outline" className="w-full justify-between" asChild>
                                <Link href="/super-admin/enrollments">
                                    <span className="flex items-center gap-2">
                                        <FileText className="h-4 w-4" />
                                        Manage Enrollments
                                    </span>
                                    <ChevronRight className="h-4 w-4" />
                                </Link>
                            </Button>
                            <Button variant="outline" className="w-full justify-between" asChild>
                                <Link href="/super-admin/students">
                                    <span className="flex items-center gap-2">
                                        <School className="h-4 w-4" />
                                        Manage Students
                                    </span>
                                    <ChevronRight className="h-4 w-4" />
                                </Link>
                            </Button>
                            <Button variant="outline" className="w-full justify-between" asChild>
                                <Link href="/super-admin/users">
                                    <span className="flex items-center gap-2">
                                        <UserPlus className="h-4 w-4" />
                                        Manage Users
                                    </span>
                                    <ChevronRight className="h-4 w-4" />
                                </Link>
                            </Button>
                            <Button variant="outline" className="w-full justify-between" asChild>
                                <Link href="/super-admin/payments">
                                    <span className="flex items-center gap-2">
                                        <CreditCard className="h-4 w-4" />
                                        View Payments
                                    </span>
                                    <ChevronRight className="h-4 w-4" />
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>

                    {/* System Management */}
                    <Card>
                        <CardHeader>
                            <CardTitle>System Management</CardTitle>
                            <CardDescription>Configure system settings</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            <Button variant="outline" className="w-full justify-between" asChild>
                                <Link href="/super-admin/grade-level-fees">
                                    <span className="flex items-center gap-2">
                                        <DollarSign className="h-4 w-4" />
                                        Grade Level Fees
                                    </span>
                                    <ChevronRight className="h-4 w-4" />
                                </Link>
                            </Button>
                            <Button variant="outline" className="w-full justify-between" asChild>
                                <Link href="/super-admin/enrollment-periods">
                                    <span className="flex items-center gap-2">
                                        <FileText className="h-4 w-4" />
                                        Enrollment Periods
                                    </span>
                                    <ChevronRight className="h-4 w-4" />
                                </Link>
                            </Button>
                            <Button variant="outline" className="w-full justify-between" asChild>
                                <Link href="/super-admin/invoices">
                                    <span className="flex items-center gap-2">
                                        <FileText className="h-4 w-4" />
                                        Invoices
                                    </span>
                                    <ChevronRight className="h-4 w-4" />
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}

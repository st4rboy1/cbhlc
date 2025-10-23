import { ComprehensiveDashboard } from '@/components/comprehensive-dashboard';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ChevronRight, CreditCard, DollarSign, FileText, School, Settings, UserPlus } from 'lucide-react';

interface Props {
    stats: {
        // Core metrics
        total_students: number;
        active_enrollments: number;
        pending_enrollments: number;
        total_revenue: number;

        // User metrics
        total_users: number;
        total_guardians: number;

        // Enrollment metrics
        approved_enrollments: number;
        completed_enrollments: number;
        rejected_enrollments: number;

        // Payment metrics
        total_invoices: number;
        paid_invoices: number;
        partial_payments: number;
        pending_payments: number;
        total_collected: number;
        total_balance: number;
        collection_rate: number;

        // Transaction metrics
        total_payments: number;
        recent_payments_count: number;
    };
}

export default function SuperAdminDashboardPage({ stats }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [{ title: 'Super Admin Dashboard', href: '/super-admin/dashboard' }];

    // Convert snake_case to camelCase for the shared component
    const dashboardStats = {
        totalStudents: stats.total_students,
        activeEnrollments: stats.active_enrollments,
        pendingApplications: stats.pending_enrollments,
        totalUsers: stats.total_users,
        totalGuardians: stats.total_guardians,
        approvedEnrollments: stats.approved_enrollments,
        completedEnrollments: stats.completed_enrollments,
        rejectedEnrollments: stats.rejected_enrollments,
        totalInvoices: stats.total_invoices,
        paidInvoices: stats.paid_invoices,
        partialPayments: stats.partial_payments,
        pendingPayments: stats.pending_payments,
        totalCollected: stats.total_collected,
        totalBalance: stats.total_balance,
        collectionRate: stats.collection_rate,
        totalPayments: stats.total_payments,
        recentPaymentsCount: stats.recent_payments_count,
        totalRevenue: stats.total_revenue,
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Super Admin Dashboard" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Super Admin Dashboard</h1>

                <ComprehensiveDashboard stats={dashboardStats} />

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
                            <Button variant="outline" className="w-full justify-between" asChild>
                                <Link href="/super-admin/settings">
                                    <span className="flex items-center gap-2">
                                        <Settings className="h-4 w-4" />
                                        System Settings
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

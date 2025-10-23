import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Link } from '@inertiajs/react';
import { ChevronRight, CreditCard, DollarSign, FileText, School, Settings, TrendingUp, UserCheck, UserPlus, Users } from 'lucide-react';

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

export function SuperAdminDashboard({ stats }: Props) {
    return (
        <div className="space-y-6">
            {/* Core Statistics */}
            <div>
                <h2 className="mb-4 text-lg font-semibold">Overview</h2>
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Students</CardTitle>
                            <School className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total_students}</div>
                            <p className="flex items-center gap-1 text-xs text-muted-foreground">
                                <TrendingUp className="h-3 w-3 text-green-500" />
                                <span>All students in system</span>
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Active Enrollments</CardTitle>
                            <UserCheck className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.active_enrollments}</div>
                            <p className="text-xs text-muted-foreground">Currently enrolled</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">System Users</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total_users}</div>
                            <p className="text-xs text-muted-foreground">{stats.total_guardians} guardians</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Revenue</CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">${stats.total_revenue.toLocaleString()}</div>
                            <p className="flex items-center gap-1 text-xs text-muted-foreground">
                                <TrendingUp className="h-3 w-3 text-green-500" />
                                <span>This school year</span>
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Enrollment Statistics */}
            <div>
                <h2 className="mb-4 text-lg font-semibold">Enrollment Status</h2>
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pending</CardTitle>
                            <FileText className="h-4 w-4 text-yellow-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.pending_enrollments}</div>
                            {stats.pending_enrollments > 0 ? (
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
                            <CardTitle className="text-sm font-medium">Approved</CardTitle>
                            <UserCheck className="h-4 w-4 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.approved_enrollments}</div>
                            <p className="text-xs text-muted-foreground">Approved applications</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Completed</CardTitle>
                            <School className="h-4 w-4 text-green-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.completed_enrollments}</div>
                            <p className="text-xs text-muted-foreground">Finished enrollments</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Rejected</CardTitle>
                            <FileText className="h-4 w-4 text-red-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.rejected_enrollments}</div>
                            <p className="text-xs text-muted-foreground">Declined applications</p>
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Payment Statistics */}
            <div>
                <h2 className="mb-4 text-lg font-semibold">Financial Overview</h2>
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Collected</CardTitle>
                            <DollarSign className="h-4 w-4 text-green-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">${stats.total_collected.toLocaleString()}</div>
                            <p className="flex items-center gap-1 text-xs text-muted-foreground">
                                <TrendingUp className="h-3 w-3 text-green-500" />
                                <span>{stats.collection_rate}% collection rate</span>
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Outstanding Balance</CardTitle>
                            <DollarSign className="h-4 w-4 text-orange-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">${stats.total_balance.toLocaleString()}</div>
                            <p className="text-xs text-muted-foreground">Pending collection</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Invoices</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total_invoices}</div>
                            <p className="text-xs text-muted-foreground">{stats.paid_invoices} fully paid</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Payments</CardTitle>
                            <CreditCard className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total_payments}</div>
                            <p className="text-xs text-muted-foreground">{stats.recent_payments_count} this week</p>
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Payment Status Breakdown */}
            <div>
                <h2 className="mb-4 text-lg font-semibold">Payment Status</h2>
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Fully Paid</CardTitle>
                            <DollarSign className="h-4 w-4 text-green-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.paid_invoices}</div>
                            <p className="flex items-center gap-1 text-xs text-muted-foreground">
                                <TrendingUp className="h-3 w-3 text-green-500" />
                                <span>Complete payments</span>
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Partial Payments</CardTitle>
                            <DollarSign className="h-4 w-4 text-yellow-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.partial_payments}</div>
                            <p className="text-xs text-muted-foreground">In progress</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pending Payments</CardTitle>
                            <DollarSign className="h-4 w-4 text-red-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.pending_payments}</div>
                            <p className="text-xs text-muted-foreground">Not yet paid</p>
                        </CardContent>
                    </Card>
                </div>
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
    );
}

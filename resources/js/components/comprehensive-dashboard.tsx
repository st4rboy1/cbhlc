import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatCurrency } from '@/lib/format-currency';
import { CreditCard, DollarSign, FileText, GraduationCap, School, TrendingUp, UserCheck } from 'lucide-react';

interface DashboardStats {
    // Core metrics
    totalStudents: number;
    activeEnrollments: number;
    newEnrollments?: number;
    pendingApplications: number;
    totalStaff?: number;

    // User Journey Metrics
    totalUsers: number;
    verifiedUsers: number;
    unverifiedUsers: number;

    // Guardian Journey Metrics
    totalGuardians: number;
    guardiansWithStudents: number;
    guardiansWithoutStudents: number;
    guardiansWithStudentsNoEnrollments: number;

    // Student Journey Metrics
    studentsWithEnrollments: number;
    studentsWithoutEnrollments: number;

    // Enrollment metrics
    approvedEnrollments: number;
    completedEnrollments: number;
    rejectedEnrollments: number;
    enrollmentsNeedingPayment: number;

    // Payment metrics
    totalInvoices: number;
    paidInvoices: number;
    partialPayments: number;
    pendingPayments: number;
    totalCollected: number;
    totalBalance: number;
    collectionRate: number;

    // Financial Projections
    totalExpectedRevenue: number;
    potentialIncomingRevenue: number;

    // Transaction metrics
    totalPayments: number;
    recentPaymentsCount: number;
    totalRevenue: number;
}

interface Props {
    stats: DashboardStats;
}

export function ComprehensiveDashboard({ stats }: Props) {
    return (
        <div className="space-y-6">
            {/* Core Metrics */}
            <div>
                <h2 className="mb-4 text-lg font-semibold">Core Metrics</h2>
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Students</CardTitle>
                            <School className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalStudents}</div>
                            <p className="text-xs text-muted-foreground">All students in system</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Active Enrollments</CardTitle>
                            <UserCheck className="h-4 w-4 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.activeEnrollments}</div>
                            <p className="text-xs text-muted-foreground">Currently enrolled</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pending Applications</CardTitle>
                            <FileText className="h-4 w-4 text-yellow-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.pendingApplications}</div>
                            <p className="text-xs text-muted-foreground">Awaiting review</p>
                        </CardContent>
                    </Card>
                    {stats.newEnrollments !== undefined && (
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">New Enrollments</CardTitle>
                                <GraduationCap className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{stats.newEnrollments}</div>
                                <p className="text-xs text-muted-foreground">This month</p>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>

            {/* Financial Overview */}
            <div>
                <h2 className="mb-4 text-lg font-semibold">Financial Overview</h2>
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Revenue</CardTitle>
                            <DollarSign className="h-4 w-4 text-green-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(stats.totalRevenue)}</div>
                            <p className="flex items-center gap-1 text-xs text-muted-foreground">
                                <TrendingUp className="h-3 w-3 text-green-500" />
                                <span>This school year</span>
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Collected</CardTitle>
                            <DollarSign className="h-4 w-4 text-green-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(stats.totalCollected)}</div>
                            <p className="text-xs text-muted-foreground">{stats.collectionRate}% collection rate</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Expected Revenue</CardTitle>
                            <DollarSign className="h-4 w-4 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(stats.totalExpectedRevenue)}</div>
                            <p className="text-xs text-muted-foreground">Total expected this year</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Outstanding Balance</CardTitle>
                            <DollarSign className="h-4 w-4 text-orange-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(stats.totalBalance)}</div>
                            <p className="text-xs text-muted-foreground">Pending collection</p>
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Payment Status */}
            <div>
                <h2 className="mb-4 text-lg font-semibold">Payment Status</h2>
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Invoices</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalInvoices}</div>
                            <p className="text-xs text-muted-foreground">{stats.paidInvoices} fully paid</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Payments</CardTitle>
                            <CreditCard className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalPayments}</div>
                            <p className="text-xs text-muted-foreground">{stats.recentPaymentsCount} this week</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pending Payments</CardTitle>
                            <DollarSign className="h-4 w-4 text-red-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.pendingPayments}</div>
                            <p className="text-xs text-muted-foreground">Not yet paid</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Partial Payments</CardTitle>
                            <DollarSign className="h-4 w-4 text-yellow-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.partialPayments}</div>
                            <p className="text-xs text-muted-foreground">In progress</p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    );
}

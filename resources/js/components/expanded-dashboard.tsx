import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatCurrency } from '@/lib/format-currency';
import {
    AlertCircle,
    CheckCircle,
    CreditCard,
    DollarSign,
    FileText,
    GraduationCap,
    School,
    TrendingUp,
    UserCheck,
    UserPlus,
    Users,
    UserX,
} from 'lucide-react';

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

export function ExpandedDashboard({ stats }: Props) {
    return (
        <div className="space-y-6">
            {/* User Journey */}
            <div>
                <h2 className="mb-4 text-lg font-semibold">User Registration Journey</h2>
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Users</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalUsers}</div>
                            <p className="text-xs text-muted-foreground">Registered accounts</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Verified Users</CardTitle>
                            <CheckCircle className="h-4 w-4 text-green-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.verifiedUsers}</div>
                            <p className="text-xs text-muted-foreground">Email verified</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Unverified Users</CardTitle>
                            <AlertCircle className="h-4 w-4 text-yellow-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.unverifiedUsers}</div>
                            <p className="text-xs text-muted-foreground">Pending verification</p>
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Guardian Journey */}
            <div>
                <h2 className="mb-4 text-lg font-semibold">Guardian Journey</h2>
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Guardians</CardTitle>
                            <UserPlus className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalGuardians}</div>
                            <p className="flex items-center gap-1 text-xs text-muted-foreground">
                                <TrendingUp className="h-3 w-3 text-green-500" />
                                <span>Registered guardians</span>
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">With Students</CardTitle>
                            <Users className="h-4 w-4 text-green-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.guardiansWithStudents}</div>
                            <p className="text-xs text-muted-foreground">Have added students</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Without Students</CardTitle>
                            <UserX className="h-4 w-4 text-orange-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.guardiansWithoutStudents}</div>
                            <p className="text-xs text-muted-foreground">No students yet</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Not Enrolled</CardTitle>
                            <AlertCircle className="h-4 w-4 text-yellow-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.guardiansWithStudentsNoEnrollments}</div>
                            <p className="text-xs text-muted-foreground">Students not enrolled</p>
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Student & Enrollment Journey */}
            <div>
                <h2 className="mb-4 text-lg font-semibold">Student & Enrollment Journey</h2>
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
                            <CardTitle className="text-sm font-medium">With Enrollments</CardTitle>
                            <GraduationCap className="h-4 w-4 text-green-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.studentsWithEnrollments}</div>
                            <p className="text-xs text-muted-foreground">Have enrollment records</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Without Enrollments</CardTitle>
                            <AlertCircle className="h-4 w-4 text-orange-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.studentsWithoutEnrollments}</div>
                            <p className="text-xs text-muted-foreground">No enrollments yet</p>
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
                </div>
            </div>

            {/* Enrollment Status */}
            <div>
                <h2 className="mb-4 text-lg font-semibold">Enrollment Status</h2>
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pending</CardTitle>
                            <FileText className="h-4 w-4 text-yellow-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.pendingApplications}</div>
                            {stats.pendingApplications > 0 ? (
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
                            <div className="text-2xl font-bold">{stats.approvedEnrollments}</div>
                            <p className="text-xs text-muted-foreground">Approved applications</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Completed</CardTitle>
                            <School className="h-4 w-4 text-green-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.completedEnrollments}</div>
                            <p className="text-xs text-muted-foreground">Finished enrollments</p>
                        </CardContent>
                    </Card>
                    {stats.newEnrollments !== undefined ? (
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">New This Month</CardTitle>
                                <GraduationCap className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{stats.newEnrollments}</div>
                                <p className="text-xs text-muted-foreground">This month's enrollments</p>
                            </CardContent>
                        </Card>
                    ) : (
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Rejected</CardTitle>
                                <FileText className="h-4 w-4 text-red-500" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{stats.rejectedEnrollments}</div>
                                <p className="text-xs text-muted-foreground">Declined applications</p>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>

            {/* Payment Journey */}
            <div>
                <h2 className="mb-4 text-lg font-semibold">Payment Journey</h2>
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Need Payment</CardTitle>
                            <AlertCircle className="h-4 w-4 text-orange-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.enrollmentsNeedingPayment}</div>
                            <p className="text-xs text-muted-foreground">Enrolled, not fully paid</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Fully Paid</CardTitle>
                            <CheckCircle className="h-4 w-4 text-green-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.paidInvoices}</div>
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
                            <div className="text-2xl font-bold">{stats.partialPayments}</div>
                            <p className="text-xs text-muted-foreground">In progress</p>
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
                            <CardTitle className="text-sm font-medium">Potential Incoming</CardTitle>
                            <DollarSign className="h-4 w-4 text-orange-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(stats.potentialIncomingRevenue)}</div>
                            <p className="text-xs text-muted-foreground">Outstanding balance</p>
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Transaction Metrics */}
            <div>
                <h2 className="mb-4 text-lg font-semibold">Transaction Metrics</h2>
                <div className="grid gap-4 md:grid-cols-3">
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
        </div>
    );
}

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Link } from '@inertiajs/react';
import { ChevronRight, CreditCard, DollarSign, FileText, School, Settings, TrendingUp, UserCheck, UserPlus, Users } from 'lucide-react';

interface Props {
    stats: {
        total_students: number;
        pending_enrollments: number;
        active_users: number;
        total_revenue: number;
    };
}

export function SuperAdminDashboard({ stats }: Props) {
    return (
        <div className="space-y-6">
            {/* Statistics Cards */}
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
                            <span>Enrolled students</span>
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Pending Enrollments</CardTitle>
                        <UserCheck className="h-4 w-4 text-muted-foreground" />
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
                        <CardTitle className="text-sm font-medium">Active Users</CardTitle>
                        <Users className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{stats.active_users}</div>
                        <p className="text-xs text-muted-foreground">System users</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Total Revenue</CardTitle>
                        <DollarSign className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">${stats.total_revenue.toLocaleString()}</div>
                        <p className="text-xs text-muted-foreground">All-time revenue</p>
                    </CardContent>
                </Card>
            </div>

            <div className="grid gap-6 md:grid-cols-2">
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

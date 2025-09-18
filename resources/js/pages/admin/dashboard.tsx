import PageLayout from '@/components/PageLayout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { ChevronRight, FileText, GraduationCap, TrendingUp, UserCheck, Users } from 'lucide-react';

export default function AdminDashboard() {
    const { auth } = usePage<SharedData>().props;

    // Mock statistics - replace with real data from backend
    const stats = {
        totalStudents: 245,
        newEnrollments: 12,
        pendingApplications: 8,
        totalStaff: 32,
    };

    const recentActivities = [
        { id: 1, type: 'enrollment', message: 'New enrollment application from John Doe', time: '2 hours ago' },
        { id: 2, type: 'approval', message: 'Application approved for Jane Smith', time: '4 hours ago' },
        { id: 3, type: 'staff', message: 'New staff member added: Maria Garcia', time: '1 day ago' },
        { id: 4, type: 'report', message: 'Monthly enrollment report generated', time: '2 days ago' },
    ];

    return (
        <>
            <Head title="Admin Dashboard" />
            <PageLayout title="ADMINISTRATOR DASHBOARD" currentPage="admin.dashboard">
                {/* Welcome Section */}
                <div className="mb-6">
                    <h2 className="text-2xl font-bold text-foreground">Welcome back, {auth.user?.name}!</h2>
                    <p className="text-muted-foreground">Here's an overview of your school's current status</p>
                </div>

                {/* Statistics Cards */}
                <div className="mb-6 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium">Total Students</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalStudents}</div>
                            <p className="flex items-center gap-1 text-xs text-muted-foreground">
                                <TrendingUp className="h-3 w-3 text-green-500" />
                                <span>+5% from last month</span>
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium">New Enrollments</CardTitle>
                            <GraduationCap className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.newEnrollments}</div>
                            <p className="text-xs text-muted-foreground">This month</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium">Pending Applications</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.pendingApplications}</div>
                            <Badge variant="secondary" className="mt-1">
                                Requires review
                            </Badge>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium">Total Staff</CardTitle>
                            <UserCheck className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalStaff}</div>
                            <p className="text-xs text-muted-foreground">Active employees</p>
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
                                <Link href="/enrollment">
                                    Review Enrollment Applications
                                    <ChevronRight className="h-4 w-4" />
                                </Link>
                            </Button>
                            <Button variant="outline" className="w-full justify-between" asChild>
                                <Link href="/registrar">
                                    Manage Student Records
                                    <ChevronRight className="h-4 w-4" />
                                </Link>
                            </Button>
                            <Button variant="outline" className="w-full justify-between" asChild>
                                <Link href="/users">
                                    Manage Users & Permissions
                                    <ChevronRight className="h-4 w-4" />
                                </Link>
                            </Button>
                            <Button variant="outline" className="w-full justify-between" asChild>
                                <Link href="/reports">
                                    Generate Reports
                                    <ChevronRight className="h-4 w-4" />
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>

                    {/* Recent Activity */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Recent Activity</CardTitle>
                            <CardDescription>Latest system activities</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {recentActivities.map((activity) => (
                                    <div key={activity.id} className="flex items-start space-x-3">
                                        <div className="flex-shrink-0">
                                            <div className="mt-2 h-2 w-2 rounded-full bg-primary" />
                                        </div>
                                        <div className="flex-1 space-y-1">
                                            <p className="text-sm text-foreground">{activity.message}</p>
                                            <p className="text-xs text-muted-foreground">{activity.time}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </PageLayout>
        </>
    );
}

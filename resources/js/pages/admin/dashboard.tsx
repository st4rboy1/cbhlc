import { ExpandedDashboard } from '@/components/expanded-dashboard';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';

interface Stats {
    totalStudents: number;
    activeEnrollments: number;
    newEnrollments: number;
    pendingApplications: number;
    totalStaff: number;

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
interface Activity {
    id: number;
    message: string;
    time: string;
}

interface Props {
    stats: Stats;
    recentActivities: Activity[];
}

export default function AdminDashboard({ stats, recentActivities }: Props) {
    const { auth } = usePage<SharedData>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Administrator Dashboard',
            href: '/admin/dashboard',
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Dashboard" />

            <div className="px-4 py-6">
                <Heading title={`Welcome back, ${auth.user?.name}!`} description="Here's an overview of your school's current status" />

                <ExpandedDashboard stats={stats} />

                <div className="mt-6 grid gap-6 md:grid-cols-2">
                    {/* Quick Actions */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                            <CardDescription>Common administrative tasks</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            <Button variant="outline" className="w-full justify-between" asChild>
                                <Link href="/admin/enrollments">
                                    Review Enrollment Applications
                                    <ChevronRight className="h-4 w-4" />
                                </Link>
                            </Button>
                            <Button variant="outline" className="w-full justify-between" asChild>
                                <Link href="/admin/students">
                                    Manage Student Records
                                    <ChevronRight className="h-4 w-4" />
                                </Link>
                            </Button>
                            <Button variant="outline" className="w-full justify-between" asChild>
                                <Link href="/admin/users">
                                    Manage Users & Permissions
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
            </div>
        </AppLayout>
    );
}

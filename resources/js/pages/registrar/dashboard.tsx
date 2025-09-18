import PageLayout from '@/components/PageLayout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { CheckCircle, Clock, FileText, Search, UserPlus, XCircle } from 'lucide-react';

export default function RegistrarDashboard() {
    const { auth } = usePage<SharedData>().props;

    // Mock data - replace with real data from backend
    const enrollmentStats = {
        pending: 8,
        approved: 24,
        rejected: 3,
        total: 35,
    };

    const recentApplications = [
        { id: 1, name: 'John Michael Doe', grade: 'Grade 6', status: 'pending', date: '2025-09-15' },
        { id: 2, name: 'Maria Santos', grade: 'Grade 4', status: 'approved', date: '2025-09-14' },
        { id: 3, name: 'Pedro Cruz', grade: 'Grade 5', status: 'pending', date: '2025-09-13' },
        { id: 4, name: 'Ana Reyes', grade: 'Grade 3', status: 'approved', date: '2025-09-12' },
        { id: 5, name: 'Luis Garcia', grade: 'Grade 6', status: 'rejected', date: '2025-09-11' },
    ];

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'pending':
                return <Badge variant="secondary">Pending</Badge>;
            case 'approved':
                return <Badge variant="default">Approved</Badge>;
            case 'rejected':
                return <Badge variant="destructive">Rejected</Badge>;
            default:
                return null;
        }
    };

    return (
        <>
            <Head title="Registrar Dashboard" />
            <PageLayout title="REGISTRAR DASHBOARD" currentPage="registrar.dashboard">
                {/* Welcome Section */}
                <div className="mb-6">
                    <h2 className="text-2xl font-bold text-foreground">Welcome, {auth.user?.name}!</h2>
                    <p className="text-muted-foreground">Manage enrollment applications and student records</p>
                </div>

                {/* Enrollment Statistics */}
                <div className="mb-6 grid gap-6 md:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium">Pending Review</CardTitle>
                            <Clock className="h-4 w-4 text-yellow-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{enrollmentStats.pending}</div>
                            <p className="text-xs text-muted-foreground">Applications waiting</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium">Approved</CardTitle>
                            <CheckCircle className="h-4 w-4 text-green-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{enrollmentStats.approved}</div>
                            <p className="text-xs text-muted-foreground">This month</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium">Rejected</CardTitle>
                            <XCircle className="h-4 w-4 text-red-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{enrollmentStats.rejected}</div>
                            <p className="text-xs text-muted-foreground">This month</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium">Total Applications</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{enrollmentStats.total}</div>
                            <p className="text-xs text-muted-foreground">This school year</p>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    {/* Quick Actions */}
                    <Card className="lg:col-span-1">
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                            <CardDescription>Common registrar tasks</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            <Button variant="default" className="w-full" asChild>
                                <Link href="/enrollment">
                                    <UserPlus className="mr-2 h-4 w-4" />
                                    Review Applications
                                </Link>
                            </Button>
                            <Button variant="outline" className="w-full" asChild>
                                <Link href="/registrar">
                                    <Search className="mr-2 h-4 w-4" />
                                    Search Students
                                </Link>
                            </Button>
                            <Button variant="outline" className="w-full" asChild>
                                <Link href="/documents">
                                    <FileText className="mr-2 h-4 w-4" />
                                    Verify Documents
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>

                    {/* Recent Applications */}
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle>Recent Applications</CardTitle>
                            <CardDescription>Latest enrollment submissions</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Student Name</TableHead>
                                        <TableHead>Grade</TableHead>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Action</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {recentApplications.map((application) => (
                                        <TableRow key={application.id}>
                                            <TableCell className="font-medium">{application.name}</TableCell>
                                            <TableCell>{application.grade}</TableCell>
                                            <TableCell>{application.date}</TableCell>
                                            <TableCell>{getStatusBadge(application.status)}</TableCell>
                                            <TableCell>
                                                <Button variant="ghost" size="sm" asChild>
                                                    <Link href={`/enrollment/${application.id}`}>View</Link>
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </div>
            </PageLayout>
        </>
    );
}

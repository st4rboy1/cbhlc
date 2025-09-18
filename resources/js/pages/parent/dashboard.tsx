import PageLayout from '@/components/PageLayout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Bell, Book, Calendar, CreditCard, FileText, GraduationCap, MessageSquare, User } from 'lucide-react';

export default function ParentDashboard() {
    const { auth } = usePage<SharedData>().props;

    // Mock data - replace with real data from backend
    const children = [
        {
            id: 1,
            name: 'Maria Rodriguez',
            grade: 'Grade 6-A',
            enrollmentStatus: 'Enrolled',
            photo: null,
        },
        {
            id: 2,
            name: 'Juan Rodriguez',
            grade: 'Grade 4-B',
            enrollmentStatus: 'Enrolled',
            photo: null,
        },
    ];

    const announcements = [
        {
            id: 1,
            title: 'Parent-Teacher Conference',
            message: 'Scheduled for October 15, 2025. Please confirm your attendance.',
            date: '2025-09-15',
            type: 'event',
        },
        {
            id: 2,
            title: 'School Holiday',
            message: 'No classes on September 21, 2025 - National Holiday',
            date: '2025-09-14',
            type: 'holiday',
        },
        {
            id: 3,
            title: 'Tuition Payment Reminder',
            message: 'Monthly tuition fee due on September 30, 2025',
            date: '2025-09-13',
            type: 'payment',
        },
    ];

    const upcomingEvents = [
        { date: '2025-10-01', event: 'Foundation Day Celebration' },
        { date: '2025-10-15', event: 'Parent-Teacher Conference' },
        { date: '2025-10-30', event: 'Halloween Program' },
        { date: '2025-11-01', event: 'All Saints Day - No Classes' },
    ];

    return (
        <>
            <Head title="Parent Dashboard" />
            <PageLayout title="PARENT DASHBOARD" currentPage="parent.dashboard">
                {/* Welcome Section */}
                <div className="mb-6">
                    <h2 className="text-2xl font-bold text-foreground">Welcome, {auth.user?.name}!</h2>
                    <p className="text-muted-foreground">Monitor your children's education journey</p>
                </div>

                {/* Children Cards */}
                <div className="mb-6">
                    <h3 className="mb-4 text-lg font-semibold">Your Children</h3>
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        {children.map((child) => (
                            <Card key={child.id}>
                                <CardHeader>
                                    <div className="flex items-center space-x-4">
                                        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                                            <User className="h-6 w-6 text-primary" />
                                        </div>
                                        <div>
                                            <CardTitle className="text-base">{child.name}</CardTitle>
                                            <CardDescription>{child.grade}</CardDescription>
                                        </div>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <div className="mb-3 flex items-center justify-between">
                                        <span className="text-sm text-muted-foreground">Status:</span>
                                        <Badge variant="default">{child.enrollmentStatus}</Badge>
                                    </div>
                                    <div className="space-y-2">
                                        <Button variant="outline" size="sm" className="w-full" asChild>
                                            <Link href={`/studentreport`}>
                                                <FileText className="mr-2 h-3 w-3" />
                                                View Report
                                            </Link>
                                        </Button>
                                        <Button variant="outline" size="sm" className="w-full" asChild>
                                            <Link href={`/tuition`}>
                                                <CreditCard className="mr-2 h-3 w-3" />
                                                Tuition Info
                                            </Link>
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    {/* School Announcements */}
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Bell className="h-5 w-5" />
                                School Announcements
                            </CardTitle>
                            <CardDescription>Latest updates from the school</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {announcements.map((announcement) => (
                                    <div key={announcement.id}>
                                        <div className="flex items-start space-x-3">
                                            <div className="mt-1 flex-shrink-0">
                                                {announcement.type === 'event' && <Calendar className="h-4 w-4 text-blue-500" />}
                                                {announcement.type === 'holiday' && <Book className="h-4 w-4 text-green-500" />}
                                                {announcement.type === 'payment' && <CreditCard className="h-4 w-4 text-yellow-500" />}
                                            </div>
                                            <div className="flex-1">
                                                <h4 className="text-sm font-medium">{announcement.title}</h4>
                                                <p className="mt-1 text-sm text-muted-foreground">{announcement.message}</p>
                                                <p className="mt-2 text-xs text-muted-foreground">{announcement.date}</p>
                                            </div>
                                        </div>
                                        <Separator className="mt-4" />
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Quick Links & Calendar */}
                    <div className="space-y-6">
                        {/* Quick Links */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Quick Links</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                <Button variant="outline" className="w-full justify-start" asChild>
                                    <Link href="/enrollment">
                                        <GraduationCap className="mr-2 h-4 w-4" />
                                        New Enrollment
                                    </Link>
                                </Button>
                                <Button variant="outline" className="w-full justify-start" asChild>
                                    <Link href="/invoice">
                                        <FileText className="mr-2 h-4 w-4" />
                                        View Invoices
                                    </Link>
                                </Button>
                                <Button variant="outline" className="w-full justify-start" asChild>
                                    <Link href="/contact">
                                        <MessageSquare className="mr-2 h-4 w-4" />
                                        Contact School
                                    </Link>
                                </Button>
                            </CardContent>
                        </Card>

                        {/* Upcoming Events */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Calendar className="h-5 w-5" />
                                    Upcoming Events
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-3">
                                    {upcomingEvents.map((event, index) => (
                                        <div key={index} className="flex items-start justify-between">
                                            <div>
                                                <p className="text-sm font-medium">{event.event}</p>
                                                <p className="text-xs text-muted-foreground">{event.date}</p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </PageLayout>
        </>
    );
}

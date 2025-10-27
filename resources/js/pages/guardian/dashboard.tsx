import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Bell, Book, Calendar, CreditCard, FileText, GraduationCap, MessageSquare, Plus, User } from 'lucide-react';

interface Child {
    id: number;
    name: string;
    grade: string;
    enrollmentStatus: string;
    photo: string | null;
}

interface Announcement {
    id: number;
    title: string;
    message: string;
    date: string;
    type: string;
}

interface UpcomingEvent {
    date: string;
    event: string;
}

interface GuardianDashboardProps {
    children: Child[];
    announcements: Announcement[];
    upcomingEvents: UpcomingEvent[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Guardian Dashboard',
        href: '/guardian/dashboard',
    },
];

export default function GuardianDashboard({ children, announcements, upcomingEvents }: GuardianDashboardProps) {
    const { auth } = usePage<SharedData>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Guardian Dashboard" />

            <div className="px-4 py-6">
                <Heading title={`Welcome, ${auth.user?.name}!`} description="Monitor your children's education journey" />

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
                                            <Link href={`/students/${child.id}/report`}>
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

                        {/* Add Student Card */}
                        <Card className="cursor-pointer border-dashed transition-colors hover:border-primary hover:bg-primary/5">
                            <Link href="/guardian/students/create" className="block h-full">
                                <CardContent className="flex h-full min-h-[280px] items-center justify-center p-6">
                                    <div className="text-center">
                                        <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
                                            <Plus className="h-8 w-8 text-primary" />
                                        </div>
                                        <p className="mt-4 text-sm font-medium text-muted-foreground">Add New Student</p>
                                    </div>
                                </CardContent>
                            </Link>
                        </Card>
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
                                    <Link href="/guardian/enrollments">
                                        <GraduationCap className="mr-2 h-4 w-4" />
                                        New Enrollment
                                    </Link>
                                </Button>
                                <Button variant="outline" className="w-full justify-start" asChild>
                                    <Link href="/guardian/invoices">
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
            </div>
        </AppLayout>
    );
}

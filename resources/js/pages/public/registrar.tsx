import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { BookOpen, Building, Calendar, Gift, GraduationCap, Users } from 'lucide-react';

interface SchoolEvent {
    icon: React.ReactNode;
    title: string;
    date?: string;
    status?: 'upcoming' | 'completed' | 'in-progress';
}

export default function Registrar() {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Registrar',
            href: '/registrar',
        },
    ];

    const schoolEvents: SchoolEvent[] = [
        {
            icon: <BookOpen className="h-5 w-5 text-blue-600" />,
            title: 'START OF CLASSES',
            date: 'August 15, 2024',
            status: 'completed',
        },
        {
            icon: <Users className="h-5 w-5 text-blue-600" />,
            title: 'PARENT ORIENTATION',
            date: 'August 20, 2024',
            status: 'completed',
        },
        {
            icon: <Building className="h-5 w-5 text-blue-600" />,
            title: 'FOUNDATION DAY',
            date: 'November 15, 2024',
            status: 'upcoming',
        },
        {
            icon: <Gift className="h-5 w-5 text-blue-600" />,
            title: 'CHRISTMAS PROGRAM',
            date: 'December 20, 2024',
            status: 'upcoming',
        },
        {
            icon: <GraduationCap className="h-5 w-5 text-blue-600" />,
            title: 'GRADUATION DAY',
            date: 'March 20, 2025',
            status: 'upcoming',
        },
    ];

    const getStatusColor = (status?: string) => {
        switch (status) {
            case 'completed':
                return 'bg-green-100 text-green-800 border-green-200';
            case 'upcoming':
                return 'bg-blue-100 text-blue-800 border-blue-200';
            case 'in-progress':
                return 'bg-yellow-100 text-yellow-800 border-yellow-200';
            default:
                return 'bg-gray-100 text-gray-800 border-gray-200';
        }
    };

    const getStatusText = (status?: string) => {
        switch (status) {
            case 'completed':
                return 'Completed';
            case 'upcoming':
                return 'Upcoming';
            case 'in-progress':
                return 'In Progress';
            default:
                return 'Scheduled';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Registrar" />

            <div className="px-4 py-6">
                <Heading title="Registrar" description="School events schedule and academic information" />
                {/* Hero Images */}
                <div className="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2">
                    <Card className="min-h-[200px] overflow-hidden">
                        <div className="relative flex h-full items-center justify-center bg-gradient-to-br from-blue-400 via-purple-500 to-teal-500">
                            <div
                                className="absolute inset-0 bg-cover bg-center opacity-60"
                                style={{ backgroundImage: "url('ra_2022-06-19_22-17-45.jpg')" }}
                            />
                            <div className="relative z-10 text-center text-white">
                                <h3 className="mb-2 text-xl font-bold">School Gallery</h3>
                                <p className="text-sm opacity-90">Moments from our school community</p>
                            </div>
                        </div>
                    </Card>
                    <Card className="min-h-[200px] overflow-hidden">
                        <div className="relative flex h-full items-center justify-center bg-gradient-to-br from-green-400 via-blue-500 to-purple-500">
                            <div
                                className="absolute inset-0 bg-cover bg-center opacity-60"
                                style={{ backgroundImage: "url('ra_2022-06-19_22-17-45.jpg')" }}
                            />
                            <div className="relative z-10 text-center text-white">
                                <h3 className="mb-2 text-xl font-bold">Campus Life</h3>
                                <p className="text-sm opacity-90">Discover our vibrant campus environment</p>
                            </div>
                        </div>
                    </Card>
                </div>

                {/* School Events Schedule */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Calendar className="h-6 w-6 text-primary" />
                            School Events Schedule
                        </CardTitle>
                        <p className="text-sm text-muted-foreground">Academic year 2024-2025 important dates and events</p>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-1 lg:grid-cols-2">
                            <div>
                                <h3 className="mb-4 text-sm font-semibold tracking-wide text-muted-foreground uppercase">SCHOOL EVENTS</h3>
                                <div className="space-y-4">
                                    {schoolEvents.map((event, index) => (
                                        <div
                                            key={index}
                                            className="flex items-center justify-between rounded-lg border bg-card p-4 transition-shadow hover:shadow-md"
                                        >
                                            <div className="flex items-center gap-3">
                                                <div className="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50">{event.icon}</div>
                                                <div>
                                                    <p className="font-medium">{event.title}</p>
                                                    {event.date && <p className="text-sm text-muted-foreground">{event.date}</p>}
                                                </div>
                                            </div>
                                            {event.status && <Badge className={getStatusColor(event.status)}>{getStatusText(event.status)}</Badge>}
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {/* Additional Information Panel */}
                            <div className="space-y-4">
                                <div className="rounded-lg border border-blue-200 bg-blue-50 p-4">
                                    <h4 className="mb-2 font-semibold text-blue-900">Important Notice</h4>
                                    <p className="text-sm text-blue-800">
                                        All students and parents are expected to attend the Parent Orientation. Please check the school calendar for
                                        any updates or changes to the schedule.
                                    </p>
                                </div>

                                <div className="rounded-lg border border-green-200 bg-green-50 p-4">
                                    <h4 className="mb-2 font-semibold text-green-900">Registration Status</h4>
                                    <p className="text-sm text-green-800">
                                        New student registration is now open for the upcoming academic year. Please submit all required documents on
                                        time.
                                    </p>
                                </div>

                                <div className="rounded-lg border border-amber-200 bg-amber-50 p-4">
                                    <h4 className="mb-2 font-semibold text-amber-900">Academic Calendar</h4>
                                    <p className="text-sm text-amber-800">
                                        The complete academic calendar is available at the registrar's office. Contact us for any questions about
                                        important dates.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Quick Stats */}
                <div className="mt-6 grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardContent className="flex items-center p-6">
                            <div className="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100">
                                <Users className="h-6 w-6 text-blue-600" />
                            </div>
                            <div className="ml-4">
                                <p className="text-sm font-medium text-muted-foreground">Total Students</p>
                                <p className="text-2xl font-bold">450</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="flex items-center p-6">
                            <div className="flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                                <GraduationCap className="h-6 w-6 text-green-600" />
                            </div>
                            <div className="ml-4">
                                <p className="text-sm font-medium text-muted-foreground">Grade Levels</p>
                                <p className="text-2xl font-bold">6</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="flex items-center p-6">
                            <div className="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100">
                                <Calendar className="h-6 w-6 text-purple-600" />
                            </div>
                            <div className="ml-4">
                                <p className="text-sm font-medium text-muted-foreground">School Days</p>
                                <p className="text-2xl font-bold">200</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}

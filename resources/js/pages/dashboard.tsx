import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head } from '@inertiajs/react';
import { BookOpen, Building, Calendar, Gift, GraduationCap, Users } from 'lucide-react';
import PageLayout from '../components/PageLayout';

interface EventItem {
    icon: React.ReactNode;
    title: string;
    date?: string;
    status?: 'upcoming' | 'completed' | 'in-progress';
}

export default function Dashboard() {
    const events: EventItem[] = [
        {
            icon: <BookOpen className="h-4 w-4" />,
            title: 'START OF CLASSES',
            date: 'Aug 15, 2024',
            status: 'completed',
        },
        {
            icon: <Users className="h-4 w-4" />,
            title: 'PARENT ORIENTATION',
            date: 'Aug 20, 2024',
            status: 'completed',
        },
        {
            icon: <Building className="h-4 w-4" />,
            title: 'FOUNDATION DAY',
            date: 'Nov 15, 2024',
            status: 'upcoming',
        },
        {
            icon: <Gift className="h-4 w-4" />,
            title: 'CHRISTMAS PROGRAM',
            date: 'Dec 20, 2024',
            status: 'upcoming',
        },
        {
            icon: <GraduationCap className="h-4 w-4" />,
            title: 'GRADUATION DAY',
            date: 'Mar 20, 2025',
            status: 'upcoming',
        },
    ];

    const getStatusColor = (status?: string) => {
        switch (status) {
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'upcoming':
                return 'bg-blue-100 text-blue-800';
            case 'in-progress':
                return 'bg-yellow-100 text-yellow-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <>
            <Head title="Dashboard" />
            <PageLayout title="DASHBOARD" currentPage="dashboard">
                {/* Hero Images */}
                <div className="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2">
                    <Card className="overflow-hidden">
                        <div className="aspect-video bg-gradient-to-br from-teal-400 to-blue-500">
                            <img src="/api/placeholder/400/200" alt="Student Activities" className="h-full w-full object-cover mix-blend-overlay" />
                        </div>
                    </Card>
                    <Card className="overflow-hidden">
                        <div className="aspect-video bg-gradient-to-br from-purple-400 to-pink-500">
                            <img src="/api/placeholder/400/200" alt="School Campus" className="h-full w-full object-cover mix-blend-overlay" />
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
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {events.map((event, index) => (
                                <div
                                    key={index}
                                    className="flex items-center justify-between rounded-lg border bg-card/50 p-4 transition-colors hover:bg-card"
                                >
                                    <div className="flex items-center gap-4">
                                        <div className="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary">
                                            {event.icon}
                                        </div>
                                        <div>
                                            <p className="font-medium">{event.title}</p>
                                            {event.date && <p className="text-sm text-muted-foreground">{event.date}</p>}
                                        </div>
                                    </div>
                                    {event.status && (
                                        <Badge variant="secondary" className={getStatusColor(event.status)}>
                                            {event.status === 'upcoming' ? 'Upcoming' : event.status === 'completed' ? 'Completed' : 'In Progress'}
                                        </Badge>
                                    )}
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </PageLayout>
        </>
    );
}

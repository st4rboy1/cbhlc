import Heading from '@/components/heading';
import { EnrollmentStatusBadge } from '@/components/status-badges';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { Award, Book, Calendar, Clock, FileText, School, Trophy, User } from 'lucide-react';

export default function StudentDashboard() {
    const { auth } = usePage<SharedData>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Student Dashboard',
            href: '/student/dashboard',
        },
    ];

    // Mock data - replace with real data from backend
    const studentInfo = {
        studentId: 'CBHLC-2025-001',
        grade: 'Grade 6-A',
        section: 'Saint Matthew',
        adviser: 'Ms. Sarah Johnson',
        enrollmentStatus: 'Enrolled',
        academicYear: '2025-2026',
    };

    const subjects = [
        { name: 'Mathematics', teacher: 'Mr. John Smith', schedule: 'MWF 8:00-9:00 AM' },
        { name: 'Science', teacher: 'Ms. Maria Garcia', schedule: 'TTH 9:00-10:00 AM' },
        { name: 'English', teacher: 'Ms. Sarah Johnson', schedule: 'MWF 10:00-11:00 AM' },
        { name: 'Filipino', teacher: 'Mr. Pedro Cruz', schedule: 'TTH 8:00-9:00 AM' },
        { name: 'Christian Living', teacher: 'Pastor James Lee', schedule: 'MWF 2:00-3:00 PM' },
    ];

    const achievements = [
        { id: 1, title: 'Perfect Attendance', month: 'August 2025', icon: Trophy },
        { id: 2, title: "Dean's List", quarter: 'First Quarter', icon: Award },
        { id: 3, title: 'Best in Math', quarter: 'First Quarter', icon: Book },
    ];

    const upcomingActivities = [
        { date: '2025-10-01', activity: 'Foundation Day Program', time: '8:00 AM' },
        { date: '2025-10-05', activity: 'Quarterly Examination', time: 'All Day' },
        { date: '2025-10-15', activity: 'Science Fair', time: '1:00 PM' },
        { date: '2025-10-30', activity: 'Halloween Celebration', time: '2:00 PM' },
    ];

    const academicProgress = 88; // Example progress percentage

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Student Dashboard" />

            <div className="px-4 py-6">
                <Heading title={`Welcome back, ${auth.user?.name}!`} description="Track your academic journey and school activities" />

                {/* Student Information Card */}
                <Card className="mb-6">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <User className="h-5 w-5" />
                            Student Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <p className="text-sm text-muted-foreground">Student ID</p>
                                <p className="font-medium">{studentInfo.studentId}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Grade & Section</p>
                                <p className="font-medium">
                                    {studentInfo.grade} - {studentInfo.section}
                                </p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Class Adviser</p>
                                <p className="font-medium">{studentInfo.adviser}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Academic Year</p>
                                <p className="font-medium">{studentInfo.academicYear}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Enrollment Status</p>
                                <EnrollmentStatusBadge status={studentInfo.enrollmentStatus} />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Academic Progress */}
                <div className="mb-6 grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Book className="h-5 w-5" />
                                Academic Progress
                            </CardTitle>
                            <CardDescription>Current quarter performance</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div>
                                    <div className="mb-2 flex justify-between">
                                        <span className="text-sm font-medium">Overall Average</span>
                                        <span className="text-sm font-bold">{academicProgress}%</span>
                                    </div>
                                    <Progress value={academicProgress} className="h-2" />
                                </div>
                                <Separator />
                                <div className="grid grid-cols-2 gap-4 text-center">
                                    <div>
                                        <p className="text-2xl font-bold text-primary">15</p>
                                        <p className="text-xs text-muted-foreground">Days Present</p>
                                    </div>
                                    <div>
                                        <p className="text-2xl font-bold text-primary">0</p>
                                        <p className="text-xs text-muted-foreground">Days Absent</p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Trophy className="h-5 w-5" />
                                Achievements
                            </CardTitle>
                            <CardDescription>Your accomplishments</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                {achievements.map((achievement) => {
                                    const Icon = achievement.icon;
                                    return (
                                        <div key={achievement.id} className="flex items-center gap-3">
                                            <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                                                <Icon className="h-4 w-4 text-primary" />
                                            </div>
                                            <div>
                                                <p className="text-sm font-medium">{achievement.title}</p>
                                                <p className="text-xs text-muted-foreground">{achievement.month || achievement.quarter}</p>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    {/* Class Schedule */}
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <School className="h-5 w-5" />
                                My Subjects
                            </CardTitle>
                            <CardDescription>Current class schedule</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {subjects.map((subject, index) => (
                                    <div key={index}>
                                        <div className="flex items-start justify-between">
                                            <div>
                                                <h4 className="font-medium">{subject.name}</h4>
                                                <p className="text-sm text-muted-foreground">Teacher: {subject.teacher}</p>
                                                <p className="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                                                    <Clock className="h-3 w-3" />
                                                    {subject.schedule}
                                                </p>
                                            </div>
                                            <FileText className="h-4 w-4 text-muted-foreground" />
                                        </div>
                                        {index < subjects.length - 1 && <Separator className="mt-4" />}
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Upcoming Activities */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Calendar className="h-5 w-5" />
                                Upcoming Activities
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                {upcomingActivities.map((activity, index) => (
                                    <div key={index} className="space-y-1">
                                        <p className="text-sm font-medium">{activity.activity}</p>
                                        <p className="text-xs text-muted-foreground">
                                            {activity.date} • {activity.time}
                                        </p>
                                        {index < upcomingActivities.length - 1 && <Separator className="!mt-3" />}
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

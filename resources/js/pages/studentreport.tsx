import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Head } from '@inertiajs/react';
import { Calendar, CheckCircle, FileText, GraduationCap, MapPin, User } from 'lucide-react';
import PageLayout from '../components/PageLayout';

interface StudentInfo {
    name: string;
    age: number;
    gender: string;
    section: string;
    birthdate: string;
    address: string;
    gradeLevel: string;
}

interface ReportData {
    academicYear: string;
    semester: string;
    status: 'enrolled' | 'pending' | 'completed';
    enrollmentDate: string;
}

export default function StudentReport() {
    const studentInfo: StudentInfo = {
        name: 'Manero Sj Rodriguez',
        age: 12,
        gender: 'Male',
        section: 'Grade 6-A',
        birthdate: 'March 15, 2012',
        address: '123 Sample Street, City, Province',
        gradeLevel: 'Grade 6',
    };

    const reportData: ReportData = {
        academicYear: '2024-2025',
        semester: 'First Semester',
        status: 'enrolled',
        enrollmentDate: 'August 15, 2024',
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'enrolled':
                return 'bg-green-100 text-green-800 border-green-200';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800 border-yellow-200';
            case 'completed':
                return 'bg-blue-100 text-blue-800 border-blue-200';
            default:
                return 'bg-gray-100 text-gray-800 border-gray-200';
        }
    };

    const getStatusText = (status: string) => {
        switch (status) {
            case 'enrolled':
                return 'Currently Enrolled';
            case 'pending':
                return 'Enrollment Pending';
            case 'completed':
                return 'Academic Year Completed';
            default:
                return 'Unknown Status';
        }
    };

    return (
        <>
            <Head title="Student Report" />
            <PageLayout title="STUDENT REPORT" currentPage="studentreport">
                {/* Status Banner */}
                <Card className="mb-6 border-green-200 bg-green-50">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-green-800">
                            <CheckCircle className="h-5 w-5" />
                            Enrollment Status
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-center justify-between">
                            <div>
                                <Badge className={getStatusColor(reportData.status)}>{getStatusText(reportData.status)}</Badge>
                                <p className="mt-2 text-green-700">
                                    Academic Year {reportData.academicYear} - {reportData.semester}
                                </p>
                            </div>
                            <div className="text-right text-sm text-green-600">
                                <p>Enrolled since: {reportData.enrollmentDate}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid gap-6 md:grid-cols-2">
                    {/* Student Personal Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <User className="h-5 w-5 text-primary" />
                                Student Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-3">
                                <div className="flex justify-between">
                                    <span className="font-medium text-muted-foreground">Full Name:</span>
                                    <span className="text-right font-semibold">{studentInfo.name}</span>
                                </div>
                                <Separator />
                                <div className="flex justify-between">
                                    <span className="font-medium text-muted-foreground">Age:</span>
                                    <span className="font-semibold">{studentInfo.age} years old</span>
                                </div>
                                <Separator />
                                <div className="flex justify-between">
                                    <span className="font-medium text-muted-foreground">Gender:</span>
                                    <span className="font-semibold">{studentInfo.gender}</span>
                                </div>
                                <Separator />
                                <div className="flex justify-between">
                                    <span className="font-medium text-muted-foreground">Grade Level:</span>
                                    <span className="flex items-center gap-1 font-semibold">
                                        <GraduationCap className="h-4 w-4 text-muted-foreground" />
                                        {studentInfo.gradeLevel}
                                    </span>
                                </div>
                                <Separator />
                                <div className="flex justify-between">
                                    <span className="font-medium text-muted-foreground">Section:</span>
                                    <span className="font-semibold">{studentInfo.section}</span>
                                </div>
                                <Separator />
                                <div className="flex justify-between">
                                    <span className="font-medium text-muted-foreground">Date of Birth:</span>
                                    <span className="flex items-center gap-1 font-semibold">
                                        <Calendar className="h-4 w-4 text-muted-foreground" />
                                        {studentInfo.birthdate}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Academic Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FileText className="h-5 w-5 text-primary" />
                                Academic Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-3">
                                <div className="flex justify-between">
                                    <span className="font-medium text-muted-foreground">Academic Year:</span>
                                    <span className="font-semibold">{reportData.academicYear}</span>
                                </div>
                                <Separator />
                                <div className="flex justify-between">
                                    <span className="font-medium text-muted-foreground">Current Semester:</span>
                                    <span className="font-semibold">{reportData.semester}</span>
                                </div>
                                <Separator />
                                <div className="flex justify-between">
                                    <span className="font-medium text-muted-foreground">Enrollment Date:</span>
                                    <span className="font-semibold">{reportData.enrollmentDate}</span>
                                </div>
                                <Separator />
                                <div className="flex justify-between">
                                    <span className="font-medium text-muted-foreground">Student Status:</span>
                                    <Badge className={getStatusColor(reportData.status)}>{getStatusText(reportData.status)}</Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Contact Information */}
                <Card className="mt-6">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <MapPin className="h-5 w-5 text-primary" />
                            Contact Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-3">
                            <div className="flex items-start gap-2">
                                <MapPin className="mt-1 h-4 w-4 flex-shrink-0 text-muted-foreground" />
                                <div>
                                    <span className="font-medium">Home Address:</span>
                                    <p className="text-muted-foreground">{studentInfo.address}</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Report Summary */}
                <Card className="mt-6 border-blue-200 bg-blue-50">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-blue-800">
                            <FileText className="h-5 w-5" />
                            Report Summary
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="text-blue-700">
                        <div className="space-y-2">
                            <p>
                                <strong>Student Status:</strong> {studentInfo.name} is currently enrolled for the {reportData.academicYear} academic
                                year.
                            </p>
                            <p>
                                <strong>Grade Placement:</strong> The student is assigned to {studentInfo.section} and is progressing normally through
                                the curriculum.
                            </p>
                            <p>
                                <strong>Next Steps:</strong> Continue regular attendance and maintain academic performance. Report any changes in
                                contact information to the registrar's office.
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </PageLayout>
        </>
    );
}

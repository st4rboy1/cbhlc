import { EnrollmentStatusBadge } from '@/components/status-badges';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { ChevronDown, ChevronUp, Search } from 'lucide-react';
import { useState } from 'react';

const dashboardData = {
    auth: {
        user: {
            name: 'Maria',
            email: 'maria.santos@example.com',
        },
    },
    quote: {
        message: 'Nothing in life is to be feared, it is only to be understood. Now is the time to understand more, so that we may fear less.',
        author: 'Marie Curie',
    },
    students: [
        {
            id: 1,
            studentId: '2025-2338',
            firstName: 'Juan',
            middleName: 'Garcia',
            lastName: 'Santos',
            fullName: 'Juan Garcia Santos',
            birthdate: '2012-03-14T16:00:00.000000Z',
            gender: 'Male',
            gradeLevel: 'Grade 6',
            latestEnrollment: {
                schoolYear: '2022-2023',
                status: 'completed',
                gradeLevel: 'Grade 4',
            },
        },
        {
            id: 2,
            studentId: '2025-9860',
            firstName: 'Ana',
            middleName: 'Garcia',
            lastName: 'Santos',
            fullName: 'Ana Garcia Santos',
            birthdate: '2015-08-21T16:00:00.000000Z',
            gender: 'Female',
            gradeLevel: 'Grade 3',
            latestEnrollment: null,
        },
    ],
};

function calculateAge(birthdate: string) {
    const birth = new Date(birthdate);
    const today = new Date();
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    return age;
}

export function GuardianDashboard() {
    const [searchQuery, setSearchQuery] = useState('');
    const [expandedStudent, setExpandedStudent] = useState<number | null>(null);

    const filteredStudents = dashboardData.students.filter(
        (student) =>
            student.fullName.toLowerCase().includes(searchQuery.toLowerCase()) || student.studentId.toLowerCase().includes(searchQuery.toLowerCase()),
    );

    const toggleExpand = (studentId: number) => {
        setExpandedStudent(expandedStudent === studentId ? null : studentId);
    };

    return (
        <div className="min-h-screen p-6 md:p-12">
            <div className="mx-auto max-w-7xl space-y-8">
                {/* Header */}
                <div className="space-y-2">
                    <h1 className="text-3xl font-semibold text-foreground">Welcome back, {dashboardData.auth.user.name}</h1>
                    <p className="text-sm text-muted-foreground">{dashboardData.auth.user.email}</p>
                </div>

                {/* Quote Card */}
                <Card className="border-l-4 border-l-primary p-6">
                    <blockquote className="space-y-2">
                        <p className="text-base leading-relaxed text-foreground italic">"{dashboardData.quote.message}"</p>
                        <footer className="text-sm text-muted-foreground">— {dashboardData.quote.author}</footer>
                    </blockquote>
                </Card>

                {/* Students Section */}
                <div className="space-y-4">
                    <div className="flex items-center justify-between">
                        <div>
                            <h2 className="text-2xl font-semibold text-foreground">My Students</h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {filteredStudents.length} {filteredStudents.length === 1 ? 'student' : 'students'}
                            </p>
                        </div>
                        <div className="relative w-full max-w-sm">
                            <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                type="text"
                                placeholder="Search by name or ID..."
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="pl-10"
                            />
                        </div>
                    </div>

                    {/* Students Table */}
                    <Card className="overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="border-b bg-muted/50">
                                    <tr>
                                        <th className="w-12 px-6 py-4 text-left text-sm font-medium text-muted-foreground"></th>
                                        <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Student ID</th>
                                        <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Name</th>
                                        <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Age</th>
                                        <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Gender</th>
                                        <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Current Grade</th>
                                        <th className="px-6 py-4 text-left text-sm font-medium text-muted-foreground">Status</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {filteredStudents.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="px-6 py-12 text-center text-sm text-muted-foreground">
                                                No students found
                                            </td>
                                        </tr>
                                    ) : (
                                        filteredStudents.map((student) => (
                                            <>
                                                <tr
                                                    key={student.id}
                                                    className="cursor-pointer transition-colors hover:bg-muted/30"
                                                    onClick={() => toggleExpand(student.id)}
                                                >
                                                    <td className="px-6 py-4">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            className="h-8 w-8 p-0"
                                                            onClick={(e) => {
                                                                e.stopPropagation();
                                                                toggleExpand(student.id);
                                                            }}
                                                        >
                                                            {expandedStudent === student.id ? (
                                                                <ChevronUp className="h-4 w-4" />
                                                            ) : (
                                                                <ChevronDown className="h-4 w-4" />
                                                            )}
                                                        </Button>
                                                    </td>
                                                    <td className="px-6 py-4 font-mono text-sm text-muted-foreground">{student.studentId}</td>
                                                    <td className="px-6 py-4">
                                                        <div className="text-sm font-medium">{student.fullName}</div>
                                                    </td>
                                                    <td className="px-6 py-4 text-sm text-muted-foreground">{calculateAge(student.birthdate)}</td>
                                                    <td className="px-6 py-4 text-sm text-muted-foreground">{student.gender}</td>
                                                    <td className="px-6 py-4 text-sm">
                                                        {student.gradeLevel || <span className="text-muted-foreground">Not assigned</span>}
                                                    </td>
                                                    <td className="px-6 py-4">
                                                        {student.latestEnrollment ? (
                                                            <EnrollmentStatusBadge status={student.latestEnrollment.status} />
                                                        ) : (
                                                            <Badge variant="outline">No enrollment</Badge>
                                                        )}
                                                    </td>
                                                </tr>
                                                {expandedStudent === student.id && (
                                                    <tr className="bg-muted/20">
                                                        <td colSpan={7} className="px-6 py-6">
                                                            <div className="space-y-4">
                                                                <h3 className="text-sm font-semibold text-foreground">Student Details</h3>
                                                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                                    <div>
                                                                        <p className="text-xs text-muted-foreground">Full Name</p>
                                                                        <p className="text-sm font-medium">{student.fullName}</p>
                                                                    </div>
                                                                    <div>
                                                                        <p className="text-xs text-muted-foreground">Birthdate</p>
                                                                        <p className="text-sm font-medium">
                                                                            {new Date(student.birthdate).toLocaleDateString('en-US', {
                                                                                year: 'numeric',
                                                                                month: 'long',
                                                                                day: 'numeric',
                                                                            })}
                                                                        </p>
                                                                    </div>
                                                                    <div>
                                                                        <p className="text-xs text-muted-foreground">Gender</p>
                                                                        <p className="text-sm font-medium">{student.gender}</p>
                                                                    </div>
                                                                    <div>
                                                                        <p className="text-xs text-muted-foreground">Current Grade Level</p>
                                                                        <p className="text-sm font-medium">{student.gradeLevel || 'Not assigned'}</p>
                                                                    </div>
                                                                </div>
                                                                {student.latestEnrollment && (
                                                                    <div className="border-t pt-4">
                                                                        <h4 className="mb-3 text-sm font-semibold text-foreground">
                                                                            Latest Enrollment
                                                                        </h4>
                                                                        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                                                            <div>
                                                                                <p className="text-xs text-muted-foreground">School Year</p>
                                                                                <p className="text-sm font-medium">
                                                                                    {student.latestEnrollment.schoolYear}
                                                                                </p>
                                                                            </div>
                                                                            <div>
                                                                                <p className="text-xs text-muted-foreground">Grade Level</p>
                                                                                <p className="text-sm font-medium">
                                                                                    {student.latestEnrollment.gradeLevel}
                                                                                </p>
                                                                            </div>
                                                                            <div>
                                                                                <p className="text-xs text-muted-foreground">Status</p>
                                                                                <EnrollmentStatusBadge status={student.latestEnrollment.status} />
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </td>
                                                    </tr>
                                                )}
                                            </>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    );
}

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

interface Student {
    id: number;
    student_id: string;
    first_name: string;
    last_name: string;
}

interface Guardian {
    id: number;
    first_name: string;
    last_name: string;
    user: {
        name: string;
        email: string;
    };
}

interface Enrollment {
    id: number;
    reference_number: string;
    student_id: number;
    guardian_id: number;
    grade_level: string;
    quarter: string;
    school_year: string;
    status: string;
    student: Student;
    guardian: Guardian;
}

interface GradeLevel {
    label: string;
    value: string;
}

interface Quarter {
    label: string;
    value: string;
}

interface Status {
    label: string;
    value: string;
}

interface Props {
    enrollment: Enrollment;
    students: Student[];
    guardians: Guardian[];
    gradelevels: GradeLevel[];
    quarters: Quarter[];
    statuses: Status[];
}

interface FormData {
    student_id: string;
    guardian_id: string;
    grade_level: string;
    quarter: string;
    school_year: string;
    status: string;
}

export default function SuperAdminEnrollmentsEdit({ enrollment, students, guardians, gradelevels, quarters, statuses }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Enrollments', href: '/super-admin/enrollments' },
        { title: `Edit ${enrollment.reference_number}`, href: `/super-admin/enrollments/${enrollment.id}/edit` },
    ];

    const { data, setData, put, processing, errors } = useForm<FormData>({
        student_id: enrollment.student_id.toString(),
        guardian_id: enrollment.guardian_id.toString(),
        grade_level: enrollment.grade_level,
        quarter: enrollment.quarter,
        school_year: enrollment.school_year,
        status: enrollment.status,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/super-admin/enrollments/${enrollment.id}`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Enrollment ${enrollment.reference_number}`} />
            <div className="container mx-auto px-4 py-6">
                <div className="mb-6 flex items-center gap-4">
                    <Link href="/super-admin/enrollments">
                        <Button variant="outline" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold">Edit Enrollment</h1>
                        <p className="text-sm text-muted-foreground">Update enrollment details for {enrollment.reference_number}</p>
                    </div>
                </div>

                <form onSubmit={handleSubmit}>
                    <div className="grid gap-6 lg:grid-cols-3">
                        {/* Main Form */}
                        <div className="space-y-6 lg:col-span-2">
                            {/* Student and Guardian Selection */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Student and Guardian Information</CardTitle>
                                    <CardDescription>Update the student and guardian for this enrollment</CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {/* Student Selection */}
                                    <div className="space-y-2">
                                        <Label htmlFor="student_id">
                                            Student <span className="text-destructive">*</span>
                                        </Label>
                                        <Select value={data.student_id} onValueChange={(value) => setData('student_id', value)}>
                                            <SelectTrigger id="student_id">
                                                <SelectValue placeholder="Select a student" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {students.map((student) => (
                                                    <SelectItem key={student.id} value={student.id.toString()}>
                                                        {student.student_id} - {student.first_name} {student.last_name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.student_id && <p className="text-sm text-destructive">{errors.student_id}</p>}
                                    </div>

                                    {/* Guardian Selection */}
                                    <div className="space-y-2">
                                        <Label htmlFor="guardian_id">
                                            Guardian <span className="text-destructive">*</span>
                                        </Label>
                                        <Select value={data.guardian_id} onValueChange={(value) => setData('guardian_id', value)}>
                                            <SelectTrigger id="guardian_id">
                                                <SelectValue placeholder="Select a guardian" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {guardians.map((guardian) => (
                                                    <SelectItem key={guardian.id} value={guardian.id.toString()}>
                                                        {guardian.first_name} {guardian.last_name} ({guardian.user.email})
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.guardian_id && <p className="text-sm text-destructive">{errors.guardian_id}</p>}
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Enrollment Details */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Enrollment Details</CardTitle>
                                    <CardDescription>Update the enrollment information</CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {/* Grade Level */}
                                    <div className="space-y-2">
                                        <Label htmlFor="grade_level">
                                            Grade Level <span className="text-destructive">*</span>
                                        </Label>
                                        <Select value={data.grade_level} onValueChange={(value) => setData('grade_level', value)}>
                                            <SelectTrigger id="grade_level">
                                                <SelectValue placeholder="Select grade level" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {gradelevels.map((level) => (
                                                    <SelectItem key={level.value} value={level.value}>
                                                        {level.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.grade_level && <p className="text-sm text-destructive">{errors.grade_level}</p>}
                                    </div>

                                    {/* Quarter */}
                                    <div className="space-y-2">
                                        <Label htmlFor="quarter">
                                            Quarter <span className="text-destructive">*</span>
                                        </Label>
                                        <Select value={data.quarter} onValueChange={(value) => setData('quarter', value)}>
                                            <SelectTrigger id="quarter">
                                                <SelectValue placeholder="Select quarter" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {quarters.map((quarter) => (
                                                    <SelectItem key={quarter.value} value={quarter.value}>
                                                        {quarter.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.quarter && <p className="text-sm text-destructive">{errors.quarter}</p>}
                                    </div>

                                    {/* School Year */}
                                    <div className="space-y-2">
                                        <Label htmlFor="school_year">
                                            School Year <span className="text-destructive">*</span>
                                        </Label>
                                        <Input
                                            id="school_year"
                                            value={data.school_year}
                                            onChange={(e) => setData('school_year', e.target.value)}
                                            placeholder="YYYY-YYYY"
                                        />
                                        {errors.school_year && <p className="text-sm text-destructive">{errors.school_year}</p>}
                                        <p className="text-sm text-muted-foreground">Format: YYYY-YYYY (e.g., 2024-2025)</p>
                                    </div>

                                    {/* Status */}
                                    <div className="space-y-2">
                                        <Label htmlFor="status">
                                            Status <span className="text-destructive">*</span>
                                        </Label>
                                        <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                                            <SelectTrigger id="status">
                                                <SelectValue placeholder="Select status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {statuses.map((status) => (
                                                    <SelectItem key={status.value} value={status.value}>
                                                        {status.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.status && <p className="text-sm text-destructive">{errors.status}</p>}
                                        <p className="text-sm text-muted-foreground">Changing status will trigger appropriate workflows</p>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            <Card className="p-6">
                                <h3 className="mb-4 font-semibold">Current Enrollment</h3>
                                <div className="space-y-3 text-sm">
                                    <div>
                                        <p className="text-muted-foreground">Reference Number</p>
                                        <p className="font-medium">{enrollment.reference_number}</p>
                                    </div>
                                    <div>
                                        <p className="text-muted-foreground">Current Student</p>
                                        <p className="font-medium">
                                            {enrollment.student.first_name} {enrollment.student.last_name}
                                        </p>
                                        <p className="text-xs text-muted-foreground">ID: {enrollment.student.student_id}</p>
                                    </div>
                                    <div>
                                        <p className="text-muted-foreground">Current Guardian</p>
                                        <p className="font-medium">
                                            {enrollment.guardian.first_name} {enrollment.guardian.last_name}
                                        </p>
                                        <p className="text-xs text-muted-foreground">{enrollment.guardian.user.email}</p>
                                    </div>
                                    <div>
                                        <p className="text-muted-foreground">Current Grade Level</p>
                                        <p className="font-medium">{enrollment.grade_level}</p>
                                    </div>
                                    <div>
                                        <p className="text-muted-foreground">Current School Year</p>
                                        <p className="font-medium">{enrollment.school_year}</p>
                                    </div>
                                    <div>
                                        <p className="text-muted-foreground">Current Status</p>
                                        <p className="font-medium capitalize">{enrollment.status.replace('_', ' ')}</p>
                                    </div>
                                </div>
                            </Card>

                            <Card className="p-6">
                                <div className="space-y-4">
                                    <Button type="submit" className="w-full" disabled={processing}>
                                        <Save className="mr-2 h-4 w-4" />
                                        {processing ? 'Updating...' : 'Update Enrollment'}
                                    </Button>
                                    <Link href="/super-admin/enrollments" className="block">
                                        <Button type="button" variant="outline" className="w-full">
                                            Cancel
                                        </Button>
                                    </Link>
                                </div>
                            </Card>
                        </div>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}

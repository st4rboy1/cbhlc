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
    guardians: Guardian[];
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

interface GradeLevel {
    label: string;
    value: string;
}

interface Quarter {
    label: string;
    value: string;
}

interface Props {
    students: Student[];
    guardians: Guardian[];
    gradelevels: GradeLevel[];
    quarters: Quarter[];
}

interface FormData {
    student_id: string;
    guardian_id: string;
    grade_level: string;
    quarter: string;
    school_year: string;
}

export default function SuperAdminEnrollmentsCreate({ students, guardians, gradelevels, quarters }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Enrollments', href: '/super-admin/enrollments' },
        { title: 'Create Enrollment', href: '/super-admin/enrollments/create' },
    ];

    const currentYear = new Date().getFullYear();
    const nextYear = currentYear + 1;

    const { data, setData, post, processing, errors } = useForm<FormData>({
        student_id: '',
        guardian_id: '',
        grade_level: '',
        quarter: '',
        school_year: `${currentYear}-${nextYear}`,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/super-admin/enrollments');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Enrollment" />
            <div className="container mx-auto px-4 py-6">
                <div className="mb-6 flex items-center gap-4">
                    <Link href="/super-admin/enrollments">
                        <Button variant="outline" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold">Create Enrollment</h1>
                        <p className="text-sm text-muted-foreground">Create a new student enrollment</p>
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
                                    <CardDescription>Select the student and guardian for this enrollment</CardDescription>
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
                                    <CardDescription>Specify the enrollment information</CardDescription>
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
                                </CardContent>
                            </Card>
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            <Card className="p-6">
                                <h3 className="mb-4 font-semibold">Important Notes</h3>
                                <div className="space-y-3 text-sm text-muted-foreground">
                                    <p>• A unique reference number will be generated automatically</p>
                                    <p>• Enrollment will be auto-approved upon creation</p>
                                    <p>• Student and guardian must be registered in the system</p>
                                    <p>• All fields marked with * are required</p>
                                </div>
                            </Card>

                            <Card className="p-6">
                                <div className="space-y-4">
                                    <Button type="submit" className="w-full" disabled={processing}>
                                        <Save className="mr-2 h-4 w-4" />
                                        {processing ? 'Creating...' : 'Create Enrollment'}
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

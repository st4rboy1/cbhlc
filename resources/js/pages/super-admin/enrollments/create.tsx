import { SchoolYearSelect } from '@/components/school-year-select';
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

interface GradeLevel {
    label: string;
    value: string;
}

interface Quarter {
    label: string;
    value: string;
}

interface SchoolYear {
    id: number;
    name: string;
    status: string;
    is_active: boolean;
}

interface Props {
    students: Student[];
    gradelevels: GradeLevel[];
    quarters: Quarter[];
    schoolYears: SchoolYear[];
}

interface FormData {
    student_id: string;
    grade_level: string;
    quarter: string;
    school_year_id: string;
    type: string;
    previous_school: string;
    payment_plan: string;
}

export default function SuperAdminEnrollmentsCreate({ students, gradelevels, quarters, schoolYears }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Enrollments', href: '/super-admin/enrollments' },
        { title: 'Create Enrollment', href: '/super-admin/enrollments/create' },
    ];

    const activeSchoolYear = schoolYears.find((sy) => sy.is_active);

    const { data, setData, post, processing, errors } = useForm<FormData>({
        student_id: '',
        grade_level: '',
        quarter: '',
        school_year_id: activeSchoolYear?.id.toString() || '',
        type: '',
        previous_school: '',
        payment_plan: '',
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
                            {/* Student Selection */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Student Information</CardTitle>
                                    <CardDescription>Select the student for this enrollment</CardDescription>
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
                                        <p className="text-sm text-muted-foreground">
                                            Guardian will be automatically selected from student&apos;s primary contact
                                        </p>
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
                                    <SchoolYearSelect
                                        value={data.school_year_id}
                                        onChange={(value) => setData('school_year_id', value)}
                                        schoolYears={schoolYears}
                                        error={errors.school_year_id}
                                        required
                                    />

                                    {/* Student Type */}
                                    <div className="space-y-2">
                                        <Label htmlFor="type">
                                            Student Type <span className="text-destructive">*</span>
                                        </Label>
                                        <Select value={data.type} onValueChange={(value) => setData('type', value)}>
                                            <SelectTrigger id="type">
                                                <SelectValue placeholder="Select student type" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="new">New Student</SelectItem>
                                                <SelectItem value="continuing">Continuing Student</SelectItem>
                                                <SelectItem value="returnee">Returnee Student</SelectItem>
                                                <SelectItem value="transferee">Transferee</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        {errors.type && <p className="text-sm text-destructive">{errors.type}</p>}
                                    </div>

                                    {/* Previous School - shown only for transferees */}
                                    {data.type === 'transferee' && (
                                        <div className="space-y-2">
                                            <Label htmlFor="previous_school">
                                                Previous School <span className="text-destructive">*</span>
                                            </Label>
                                            <Input
                                                id="previous_school"
                                                value={data.previous_school}
                                                onChange={(e) => setData('previous_school', e.target.value)}
                                                placeholder="Enter previous school name"
                                            />
                                            {errors.previous_school && <p className="text-sm text-destructive">{errors.previous_school}</p>}
                                        </div>
                                    )}

                                    {/* Payment Plan */}
                                    <div className="space-y-2">
                                        <Label htmlFor="payment_plan">
                                            Payment Plan <span className="text-destructive">*</span>
                                        </Label>
                                        <Select value={data.payment_plan} onValueChange={(value) => setData('payment_plan', value)}>
                                            <SelectTrigger id="payment_plan">
                                                <SelectValue placeholder="Select payment plan" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="annual">Annual (Full Payment)</SelectItem>
                                                <SelectItem value="semestral">Semestral (2 Payments)</SelectItem>
                                                <SelectItem value="monthly">Monthly (10 Payments)</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        {errors.payment_plan && <p className="text-sm text-destructive">{errors.payment_plan}</p>}
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
                                    <p>• Guardian will be automatically selected from student&apos;s primary contact</p>
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

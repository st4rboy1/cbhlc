import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { AlertCircle, Calendar, Clock, GraduationCap, Users } from 'lucide-react';
import { useEffect } from 'react';
import { toast } from 'sonner';

interface Student {
    id: number;
    first_name: string;
    middle_name: string;
    last_name: string;
    student_id: string;
    is_new_student: boolean;
    current_grade_level: string | null;
    available_grade_levels: string[];
}

interface SchoolYear {
    id: number;
    name: string;
    start_year: number;
    end_year: number;
}

interface EnrollmentPeriod {
    id: number;
    school_year: SchoolYear;
    status: string;
    start_date: string;
    end_date: string;
    early_registration_deadline: string | null;
    regular_registration_deadline: string;
    late_registration_deadline: string | null;
}

interface Props {
    students: Student[];
    gradeLevels: string[];
    quarters: string[];
    currentSchoolYear: string;
    selectedStudentId?: string;
    activePeriod: EnrollmentPeriod;
    daysRemaining: number;
}

export default function GuardianEnrollmentsCreate({
    students,
    gradeLevels,
    quarters,
    currentSchoolYear,
    selectedStudentId,
    activePeriod,
    daysRemaining,
}: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Guardian', href: '/guardian/dashboard' },
        { title: 'Enrollments', href: '/guardian/enrollments' },
        { title: 'New Enrollment', href: '/guardian/enrollments/create' },
    ];

    const { data, setData, post, processing, errors } = useForm({
        student_id: selectedStudentId || '',
        school_year: currentSchoolYear,
        quarter: '',
        grade_level: '',
        payment_plan: 'monthly',
        enrollment_period: 'active',
    });

    // Get selected student
    const selectedStudent = students.find((s) => s.id.toString() === data.student_id);

    // Filter grade levels based on selected student
    const availableGradeLevels = selectedStudent ? selectedStudent.available_grade_levels : gradeLevels;

    // Reset grade level when student changes
    useEffect(() => {
        if (data.student_id && selectedStudent) {
            // If current grade level is not in available grade levels, reset it
            if (data.grade_level && !selectedStudent.available_grade_levels.includes(data.grade_level)) {
                setData('grade_level', '');
            }
        }
    }, [data.student_id]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/guardian/enrollments', {
            onSuccess: () => {
                toast.success('Enrollment application submitted successfully');
            },
            onError: () => {
                toast.error('Failed to submit enrollment. Please check the form.');
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="New Enrollment" />
            <div className="px-4 py-6">
                <div className="mb-6">
                    <h1 className="text-2xl font-bold">New Enrollment Application</h1>
                    <p className="text-muted-foreground">Submit an enrollment application for the current school year</p>
                </div>

                {/* Enrollment Period Info Card */}
                <Card className="mb-6 border-blue-200 bg-blue-50/50 dark:border-blue-800 dark:bg-blue-950/20">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-2">
                                <Calendar className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                <CardTitle className="text-blue-700 dark:text-blue-400">Active Enrollment Period</CardTitle>
                            </div>
                            <Badge variant="default">Open</Badge>
                        </div>
                        <CardDescription className="text-blue-600 dark:text-blue-500">School Year {activePeriod.school_year.name}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="flex items-center gap-2">
                                <Clock className="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p className="text-sm font-medium">Days Remaining</p>
                                    <p className="text-lg font-bold">{daysRemaining} days</p>
                                </div>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Registration Deadline</p>
                                <p className="text-sm font-semibold">
                                    {new Date(activePeriod.regular_registration_deadline).toLocaleDateString('en-US', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                    })}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {students.length === 0 ? (
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-12">
                            <AlertCircle className="mb-4 h-12 w-12 text-muted-foreground" />
                            <p className="text-center text-muted-foreground">
                                No students found. Please add students to your account before enrolling.
                            </p>
                        </CardContent>
                    </Card>
                ) : (
                    <Card className="mx-auto max-w-3xl">
                        <CardHeader>
                            <CardTitle>Enrollment Information</CardTitle>
                            <CardDescription>Fill in the enrollment details for your child</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-6">
                                {/* Student Selection */}
                                <div className="space-y-2">
                                    <Label htmlFor="student_id">
                                        Select Student <span className="text-destructive">*</span>
                                    </Label>
                                    <Select value={data.student_id} onValueChange={(value) => setData('student_id', value)} disabled={processing}>
                                        <SelectTrigger id="student_id" className={errors.student_id ? 'border-destructive' : ''}>
                                            <SelectValue placeholder="Choose a student" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {students.map((student) => (
                                                <SelectItem key={student.id} value={student.id.toString()}>
                                                    <div className="flex items-center gap-2">
                                                        <Users className="h-4 w-4" />
                                                        <span>
                                                            {student.first_name} {student.middle_name} {student.last_name}
                                                        </span>
                                                        <span className="text-xs text-muted-foreground">({student.student_id})</span>
                                                        {student.is_new_student && (
                                                            <Badge variant="secondary" className="text-xs">
                                                                New
                                                            </Badge>
                                                        )}
                                                    </div>
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.student_id && <p className="text-sm text-destructive">{errors.student_id}</p>}
                                    {selectedStudent && (
                                        <div className="rounded-lg border bg-muted/50 p-3">
                                            <div className="flex items-center gap-2 text-sm">
                                                <GraduationCap className="h-4 w-4 text-muted-foreground" />
                                                <span className="font-medium">Current Status:</span>
                                                <span>
                                                    {selectedStudent.is_new_student
                                                        ? 'New Student'
                                                        : `Returning Student - Current Grade: ${selectedStudent.current_grade_level}`}
                                                </span>
                                            </div>
                                        </div>
                                    )}
                                </div>

                                {/* School Year (Read-only) */}
                                <div className="space-y-2">
                                    <Label htmlFor="school_year">School Year</Label>
                                    <div className="rounded-md border bg-muted px-3 py-2 text-sm">{currentSchoolYear}</div>
                                    <p className="text-xs text-muted-foreground">School year is set by the active enrollment period</p>
                                </div>

                                {/* Grade Level and Quarter */}
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="grade_level">
                                            Grade Level <span className="text-destructive">*</span>
                                        </Label>
                                        <Select
                                            value={data.grade_level}
                                            onValueChange={(value) => setData('grade_level', value)}
                                            disabled={processing || !data.student_id}
                                        >
                                            <SelectTrigger id="grade_level" className={errors.grade_level ? 'border-destructive' : ''}>
                                                <SelectValue placeholder="Select grade level" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {availableGradeLevels.map((level) => (
                                                    <SelectItem key={level} value={level}>
                                                        {level}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.grade_level && <p className="text-sm text-destructive">{errors.grade_level}</p>}
                                        {!data.student_id && <p className="text-xs text-muted-foreground">Select a student first</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="quarter">
                                            Quarter <span className="text-destructive">*</span>
                                        </Label>
                                        <Select value={data.quarter} onValueChange={(value) => setData('quarter', value)} disabled={processing}>
                                            <SelectTrigger id="quarter" className={errors.quarter ? 'border-destructive' : ''}>
                                                <SelectValue placeholder="Select quarter" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {quarters.map((quarter) => (
                                                    <SelectItem key={quarter} value={quarter}>
                                                        {quarter === 'First' && '1st Quarter'}
                                                        {quarter === 'Second' && '2nd Quarter'}
                                                        {quarter === 'Third' && '3rd Quarter'}
                                                        {quarter === 'Fourth' && '4th Quarter'}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.quarter && <p className="text-sm text-destructive">{errors.quarter}</p>}
                                        {selectedStudent && !selectedStudent.is_new_student && (
                                            <p className="text-xs text-muted-foreground">Returning students must enroll in 1st Quarter</p>
                                        )}
                                    </div>
                                </div>

                                {/* Payment Plan */}
                                <div className="space-y-2">
                                    <Label htmlFor="payment_plan">
                                        Payment Plan <span className="text-destructive">*</span>
                                    </Label>
                                    <Select value={data.payment_plan} onValueChange={(value) => setData('payment_plan', value)} disabled={processing}>
                                        <SelectTrigger id="payment_plan" className={errors.payment_plan ? 'border-destructive' : ''}>
                                            <SelectValue placeholder="Select payment plan" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="annual">Annual (Full Year)</SelectItem>
                                            <SelectItem value="semestral">Semestral (Per Semester)</SelectItem>
                                            <SelectItem value="monthly">Monthly</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p className="text-sm text-muted-foreground">Choose how you want to split tuition fees over the school year</p>
                                    {errors.payment_plan && <p className="text-sm text-destructive">{errors.payment_plan}</p>}
                                </div>

                                {/* Important Notes */}
                                <div className="rounded-lg border border-yellow-200 bg-yellow-50/50 p-4 dark:border-yellow-800 dark:bg-yellow-950/20">
                                    <div className="flex gap-2">
                                        <AlertCircle className="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                                        <div className="flex-1">
                                            <h4 className="mb-2 font-semibold text-yellow-700 dark:text-yellow-400">Important Notes</h4>
                                            <ul className="space-y-1 text-sm text-yellow-600 dark:text-yellow-500">
                                                <li>• Enrollment is subject to approval by the school administration</li>
                                                <li>• You will be notified once your application has been reviewed</li>
                                                <li>• Students cannot enroll in a grade level lower than their previous enrollment</li>
                                                <li>• Only one pending enrollment per student is allowed at a time</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                {/* Form Actions */}
                                <div className="flex gap-4">
                                    <Button type="submit" disabled={processing}>
                                        {processing ? 'Submitting...' : 'Submit Enrollment'}
                                    </Button>
                                    <Button type="button" variant="outline" onClick={() => window.history.back()} disabled={processing}>
                                        Cancel
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}

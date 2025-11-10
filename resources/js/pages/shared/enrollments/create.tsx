import Heading from '@/components/heading';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { AlertCircle } from 'lucide-react';
import React, { FormEventHandler } from 'react';

interface Student {
    id: number;
    first_name: string;
    middle_name?: string;
    last_name: string;
    student_id: string;
    is_new_student: boolean;
    current_grade_level?: string;
    available_grade_levels: string[];
}

interface Props {
    students: Student[];
    gradeLevels: string[];
    quarters: string[];
    currentSchoolYear: string;
    selectedStudentId?: string | null;
    submitRoute?: string;
    indexRoute?: string;
}

export default function EnrollmentCreate({
    students,
    quarters,
    currentSchoolYear,
    selectedStudentId,
    submitRoute = '/enrollments',
    indexRoute = '/enrollments',
}: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Enrollments',
            href: indexRoute,
        },
        {
            title: 'New Enrollment',
            href: `${indexRoute}/create`,
        },
    ];

    // Initialize with the selectedStudentId if provided
    const { data, setData, post, processing, errors } = useForm({
        student_id: selectedStudentId ? String(selectedStudentId) : students.length > 0 ? String(students[0].id) : '', // Automatically select first student
        school_year: currentSchoolYear,
        quarter: '',
        grade_level: '',
    });

    const selectedStudent = students.find((s) => s.id.toString() === data.student_id);
    const canSelectQuarter = selectedStudent?.is_new_student ?? false;
    const availableGrades = selectedStudent?.available_grade_levels ?? [];

    // Auto-set quarter for existing students
    React.useEffect(() => {
        if (selectedStudent && !selectedStudent.is_new_student) {
            setData('quarter', 'First');
        }
    }, [selectedStudent, setData]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(submitRoute, {
            preserveScroll: false,
            onSuccess: () => {
                // The redirect is handled by the server
            },
            onError: (errors) => {
                console.error('Submission errors:', errors);
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="New Enrollment" />

            <div className="px-4 py-6">
                <Heading title="New Enrollment" description="Submit an enrollment application for the current school year" />

                <div className="mx-auto max-w-2xl space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Enrollment Application</CardTitle>
                            <CardDescription>Submit an enrollment application for the {currentSchoolYear} school year</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={submit} className="space-y-6">
                                {/* Student Selection */}
                                <div className="space-y-2">
                                    <Label htmlFor="student_id">Select Student</Label>
                                    <Select value={data.student_id} onValueChange={(value) => setData('student_id', value)}>
                                        <SelectTrigger id="student_id" className={errors.student_id ? 'border-red-500' : ''}>
                                            <SelectValue placeholder="Select a student to enroll" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {students.length > 0 ? (
                                                students.map((student) => (
                                                    <SelectItem key={student.id} value={student.id.toString()}>
                                                        {student.first_name} {student.middle_name} {student.last_name} - {student.student_id}
                                                    </SelectItem>
                                                ))
                                            ) : (
                                                <div className="p-2 text-sm text-muted-foreground">
                                                    No students found. Please add a student first.
                                                </div>
                                            )}
                                        </SelectContent>
                                    </Select>
                                    {errors.student_id && <p className="text-sm text-red-500">{errors.student_id}</p>}
                                </div>

                                {/* School Year (Read-only) */}
                                <div className="space-y-2">
                                    <Label htmlFor="school_year">School Year</Label>
                                    <div className="rounded-md border bg-muted px-3 py-2 text-sm">{data.school_year}</div>
                                </div>

                                {/* Grade Level Selection */}
                                {selectedStudent && (
                                    <div className="space-y-2">
                                        <Label htmlFor="grade_level">Grade Level</Label>
                                        <Select value={data.grade_level} onValueChange={(value) => setData('grade_level', value)}>
                                            <SelectTrigger id="grade_level" className={errors.grade_level ? 'border-red-500' : ''}>
                                                <SelectValue placeholder="Select grade level" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {availableGrades.length > 0 ? (
                                                    availableGrades.map((grade) => (
                                                        <SelectItem key={grade} value={grade}>
                                                            {grade}
                                                        </SelectItem>
                                                    ))
                                                ) : (
                                                    <div className="p-2 text-sm text-muted-foreground">
                                                        No grade levels available for this student.
                                                    </div>
                                                )}
                                            </SelectContent>
                                        </Select>
                                        {errors.grade_level && <p className="text-sm text-red-500">{errors.grade_level}</p>}
                                        {selectedStudent.current_grade_level && (
                                            <p className="text-sm text-muted-foreground">Current grade: {selectedStudent.current_grade_level}</p>
                                        )}
                                    </div>
                                )}

                                {/* Quarter Selection */}
                                {selectedStudent && (
                                    <div className="space-y-2">
                                        <Label htmlFor="quarter">Quarter</Label>
                                        {canSelectQuarter ? (
                                            <Select value={data.quarter} onValueChange={(value) => setData('quarter', value)}>
                                                <SelectTrigger id="quarter" className={errors.quarter ? 'border-red-500' : ''}>
                                                    <SelectValue placeholder="Select quarter" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {quarters.map((quarter) => (
                                                        <SelectItem key={quarter} value={quarter}>
                                                            {quarter}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        ) : (
                                            <div className="rounded-md border bg-muted px-3 py-2 text-sm">
                                                First (automatically set for existing students)
                                            </div>
                                        )}
                                        {errors.quarter && <p className="text-sm text-red-500">{errors.quarter}</p>}
                                        {!canSelectQuarter && (
                                            <p className="text-sm text-muted-foreground">
                                                Existing students are automatically enrolled in the First quarter.
                                            </p>
                                        )}
                                    </div>
                                )}

                                {/* No Students Alert */}
                                {students.length === 0 && (
                                    <Alert>
                                        <AlertCircle className="h-4 w-4" />
                                        <AlertDescription>
                                            You need to add a student before creating an enrollment. Please go to the "Add Student" section first.
                                        </AlertDescription>
                                    </Alert>
                                )}

                                {/* Submit Button */}
                                <div className="flex justify-end gap-2">
                                    <Button type="button" variant="outline" onClick={() => window.history.back()}>
                                        Cancel
                                    </Button>
                                    <Button type="submit" disabled={processing || students.length === 0 || !selectedStudent || !data.grade_level}>
                                        {processing ? 'Submitting...' : 'Submit Enrollment Application'}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>

                    {/* Enrollment Rules Card */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Enrollment Rules</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <h4 className="font-semibold">Grade Level Progression</h4>
                                <ul className="mt-2 list-inside list-disc space-y-1 text-sm text-muted-foreground">
                                    <li>New students can enroll in any available grade level</li>
                                    <li>Existing students cannot apply to grades lower than their current grade</li>
                                    <li>Students can only progress to higher grades if they passed the previous school year</li>
                                    <li>Accelerated students may apply beyond the next grade level (subject to registrar approval)</li>
                                </ul>
                            </div>
                            <div>
                                <h4 className="font-semibold">Quarter Selection</h4>
                                <ul className="mt-2 list-inside list-disc space-y-1 text-sm text-muted-foreground">
                                    <li>New students can choose their starting quarter</li>
                                    <li>Existing students are automatically enrolled in the First quarter</li>
                                </ul>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Information Card */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Important Information</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <h4 className="font-semibold">What happens next?</h4>
                                <ul className="mt-2 list-inside list-disc space-y-1 text-sm text-muted-foreground">
                                    <li>Your enrollment application will be reviewed by the registrar</li>
                                    <li>You will receive a notification once your application is processed</li>
                                    <li>Upon approval, you can proceed with payment and document submission</li>
                                </ul>
                            </div>
                            <div>
                                <h4 className="font-semibold">Required Documents</h4>
                                <p className="mt-2 text-sm text-muted-foreground">
                                    The following documents will be required during the enrollment process:
                                </p>
                                <ul className="mt-2 list-inside list-disc space-y-1 text-sm text-muted-foreground">
                                    <li>Birth Certificate</li>
                                    <li>Previous Report Card</li>
                                    <li>Form 138 (for transferees)</li>
                                    <li>Good Moral Certificate</li>
                                </ul>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}

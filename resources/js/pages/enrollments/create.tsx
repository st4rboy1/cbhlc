import PageLayout from '@/components/PageLayout';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Head, useForm } from '@inertiajs/react';
import { AlertCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

interface Student {
    id: number;
    first_name: string;
    middle_name?: string;
    last_name: string;
    student_id: string;
}

interface Props {
    students: Student[];
    gradeLevels: string[];
    quarters: string[];
    currentSchoolYear: string;
}

export default function EnrollmentCreate({ students, quarters, currentSchoolYear }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        student_id: '',
        school_year: currentSchoolYear,
        quarter: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/enrollments');
    };

    return (
        <>
            <Head title="New Enrollment" />
            <PageLayout title="NEW ENROLLMENT" currentPage="enrollments">
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

                                {/* Quarter Selection */}
                                <div className="space-y-2">
                                    <Label htmlFor="quarter">Quarter</Label>
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
                                    {errors.quarter && <p className="text-sm text-red-500">{errors.quarter}</p>}
                                </div>

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
                                    <Button type="submit" disabled={processing || students.length === 0}>
                                        {processing ? 'Submitting...' : 'Submit Enrollment Application'}
                                    </Button>
                                </div>
                            </form>
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
            </PageLayout>
        </>
    );
}

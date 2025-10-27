import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { AlertCircle } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

interface Enrollment {
    id: number;
    student: {
        id: number;
        first_name: string;
        middle_name: string;
        last_name: string;
        student_id: string;
    };
    school_year: string;
    grade_level: string;
    quarter: string;
    status: string;
}

interface Props {
    enrollment: Enrollment;
    gradeLevels: string[];
    quarters: string[];
}

export default function GuardianEnrollmentsEdit({ enrollment, gradeLevels, quarters }: Props) {
    const [cancelDialogOpen, setCancelDialogOpen] = useState(false);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Guardian', href: '/guardian/dashboard' },
        { title: 'Enrollments', href: '/guardian/enrollments' },
        { title: `Enrollment #${enrollment.id}`, href: `/guardian/enrollments/${enrollment.id}` },
        { title: 'Edit', href: `/guardian/enrollments/${enrollment.id}/edit` },
    ];

    const { data, setData, put, processing, errors } = useForm({
        grade_level: enrollment.grade_level,
        quarter: enrollment.quarter,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/guardian/enrollments/${enrollment.id}`, {
            onSuccess: () => {
                toast.success('Enrollment updated successfully');
            },
            onError: () => {
                toast.error('Failed to update enrollment. Please check the form.');
            },
        });
    };

    const handleCancel = () => {
        router.delete(`/guardian/enrollments/${enrollment.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Enrollment canceled successfully');
                setCancelDialogOpen(false);
            },
            onError: () => {
                toast.error('Failed to cancel enrollment');
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Enrollment" />
            <div className="px-4 py-6">
                <div className="mb-6">
                    <h1 className="text-2xl font-bold">Edit Enrollment</h1>
                    <p className="text-muted-foreground">Update enrollment details for pending application</p>
                </div>

                <Card className="mx-auto max-w-2xl">
                    <CardHeader>
                        <CardTitle>Enrollment Information</CardTitle>
                        <CardDescription>
                            Update grade level and quarter for{' '}
                            <span className="font-semibold">
                                {enrollment.student.first_name} {enrollment.student.middle_name} {enrollment.student.last_name}
                            </span>
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            {/* Student Info (Read-only) */}
                            <div className="rounded-lg border bg-muted/50 p-4">
                                <div className="grid gap-3 text-sm">
                                    <div className="flex justify-between">
                                        <span className="font-medium text-muted-foreground">Student:</span>
                                        <span className="font-semibold">
                                            {enrollment.student.first_name} {enrollment.student.middle_name} {enrollment.student.last_name}
                                        </span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="font-medium text-muted-foreground">Student ID:</span>
                                        <span className="font-semibold">{enrollment.student.student_id}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="font-medium text-muted-foreground">School Year:</span>
                                        <span className="font-semibold">{enrollment.school_year}</span>
                                    </div>
                                </div>
                            </div>

                            {/* Grade Level and Quarter */}
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="grade_level">
                                        Grade Level <span className="text-destructive">*</span>
                                    </Label>
                                    <Select value={data.grade_level} onValueChange={(value) => setData('grade_level', value)} disabled={processing}>
                                        <SelectTrigger id="grade_level" className={errors.grade_level ? 'border-destructive' : ''}>
                                            <SelectValue placeholder="Select grade level" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {gradeLevels.map((level) => (
                                                <SelectItem key={level} value={level}>
                                                    {level}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.grade_level && <p className="text-sm text-destructive">{errors.grade_level}</p>}
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
                                </div>
                            </div>

                            {/* Important Note */}
                            <div className="rounded-lg border border-blue-200 bg-blue-50/50 p-4 dark:border-blue-800 dark:bg-blue-950/20">
                                <div className="flex gap-2">
                                    <AlertCircle className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    <div className="flex-1">
                                        <h4 className="mb-1 font-semibold text-blue-700 dark:text-blue-400">Note</h4>
                                        <p className="text-sm text-blue-600 dark:text-blue-500">
                                            You can only edit pending enrollments. Once approved, enrollment details cannot be changed.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Form Actions */}
                            <div className="flex flex-wrap gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Updating...' : 'Update Enrollment'}
                                </Button>
                                <Button type="button" variant="outline" onClick={() => window.history.back()} disabled={processing}>
                                    Cancel
                                </Button>
                                <Button
                                    type="button"
                                    variant="destructive"
                                    onClick={() => setCancelDialogOpen(true)}
                                    disabled={processing}
                                    className="ml-auto"
                                >
                                    Cancel Enrollment
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <ConfirmationDialog
                    open={cancelDialogOpen}
                    onOpenChange={setCancelDialogOpen}
                    onConfirm={handleCancel}
                    title="Cancel Enrollment?"
                    description="Are you sure you want to cancel this enrollment? This action cannot be undone."
                    confirmText="Cancel Enrollment"
                    variant="destructive"
                />
            </div>
        </AppLayout>
    );
}

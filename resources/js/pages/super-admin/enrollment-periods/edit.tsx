import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { DatePicker } from '@/components/ui/date-picker';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { format } from 'date-fns';
import { toast } from 'sonner';

export type EnrollmentPeriod = {
    id: number;
    school_year: string;
    status: string;
    start_date: string;
    end_date: string;
    early_registration_deadline: string | null;
    regular_registration_deadline: string;
    late_registration_deadline: string | null;
    description: string | null;
    allow_new_students: boolean;
    allow_returning_students: boolean;
};

interface Props {
    period: EnrollmentPeriod;
}

export default function EnrollmentPeriodEdit({ period }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Enrollment Periods', href: '/super-admin/enrollment-periods' },
        { title: period.school_year, href: `/super-admin/enrollment-periods/${period.id}` },
        { title: 'Edit', href: `/super-admin/enrollment-periods/${period.id}/edit` },
    ];

    const { data, setData, put, processing, errors } = useForm({
        school_year: period.school_year,
        start_date: period.start_date,
        end_date: period.end_date,
        early_registration_deadline: period.early_registration_deadline || '',
        regular_registration_deadline: period.regular_registration_deadline,
        late_registration_deadline: period.late_registration_deadline || '',
        description: period.description || '',
        allow_new_students: period.allow_new_students,
        allow_returning_students: period.allow_returning_students,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/super-admin/enrollment-periods/${period.id}`, {
            onSuccess: () => {
                toast.success('Enrollment period updated successfully');
            },
            onError: () => {
                toast.error('Failed to update enrollment period. Please check the form.');
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${period.school_year}`} />
            <div className="px-4 py-6">
                <div className="mb-6">
                    <h1 className="text-2xl font-bold">Edit Enrollment Period</h1>
                    <p className="text-muted-foreground">Update the enrollment period information and settings</p>
                </div>

                <Card className="mx-auto max-w-3xl">
                    <CardHeader>
                        <CardTitle>Enrollment Period Information</CardTitle>
                        <CardDescription>Modify the details for {period.school_year}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            {/* School Year */}
                            <div className="space-y-2">
                                <Label htmlFor="school_year">
                                    School Year <span className="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="school_year"
                                    placeholder="2025-2026"
                                    value={data.school_year}
                                    onChange={(e) => setData('school_year', e.target.value)}
                                    className={errors.school_year ? 'border-destructive' : ''}
                                />
                                {errors.school_year && <p className="text-sm text-destructive">{errors.school_year}</p>}
                                <p className="text-sm text-muted-foreground">Format: YYYY-YYYY (e.g., 2025-2026)</p>
                            </div>

                            {/* Period Dates */}
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="start_date">
                                        Start Date <span className="text-destructive">*</span>
                                    </Label>
                                    <DatePicker
                                        id="start_date"
                                        value={data.start_date ? new Date(data.start_date) : undefined}
                                        onChange={(date) => setData('start_date', date ? format(date, 'yyyy-MM-dd') : '')}
                                        placeholder="Select start date"
                                        error={!!errors.start_date}
                                    />
                                    {errors.start_date && <p className="text-sm text-destructive">{errors.start_date}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="end_date">
                                        End Date <span className="text-destructive">*</span>
                                    </Label>
                                    <DatePicker
                                        id="end_date"
                                        value={data.end_date ? new Date(data.end_date) : undefined}
                                        onChange={(date) => setData('end_date', date ? format(date, 'yyyy-MM-dd') : '')}
                                        placeholder="Select end date"
                                        error={!!errors.end_date}
                                    />
                                    {errors.end_date && <p className="text-sm text-destructive">{errors.end_date}</p>}
                                </div>
                            </div>

                            {/* Registration Deadlines */}
                            <div className="space-y-4">
                                <h3 className="text-lg font-semibold">Registration Deadlines</h3>

                                <div className="grid gap-4 md:grid-cols-3">
                                    <div className="space-y-2">
                                        <Label htmlFor="early_registration_deadline">Early Registration</Label>
                                        <DatePicker
                                            id="early_registration_deadline"
                                            value={data.early_registration_deadline ? new Date(data.early_registration_deadline) : undefined}
                                            onChange={(date) => setData('early_registration_deadline', date ? format(date, 'yyyy-MM-dd') : '')}
                                            placeholder="Optional"
                                            error={!!errors.early_registration_deadline}
                                        />
                                        {errors.early_registration_deadline && (
                                            <p className="text-sm text-destructive">{errors.early_registration_deadline}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="regular_registration_deadline">
                                            Regular Registration <span className="text-destructive">*</span>
                                        </Label>
                                        <DatePicker
                                            id="regular_registration_deadline"
                                            value={data.regular_registration_deadline ? new Date(data.regular_registration_deadline) : undefined}
                                            onChange={(date) => setData('regular_registration_deadline', date ? format(date, 'yyyy-MM-dd') : '')}
                                            placeholder="Select deadline"
                                            error={!!errors.regular_registration_deadline}
                                        />
                                        {errors.regular_registration_deadline && (
                                            <p className="text-sm text-destructive">{errors.regular_registration_deadline}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="late_registration_deadline">Late Registration</Label>
                                        <DatePicker
                                            id="late_registration_deadline"
                                            value={data.late_registration_deadline ? new Date(data.late_registration_deadline) : undefined}
                                            onChange={(date) => setData('late_registration_deadline', date ? format(date, 'yyyy-MM-dd') : '')}
                                            placeholder="Optional"
                                            error={!!errors.late_registration_deadline}
                                        />
                                        {errors.late_registration_deadline && (
                                            <p className="text-sm text-destructive">{errors.late_registration_deadline}</p>
                                        )}
                                    </div>
                                </div>
                            </div>

                            {/* Description */}
                            <div className="space-y-2">
                                <Label htmlFor="description">Description</Label>
                                <Textarea
                                    id="description"
                                    placeholder="Optional description or notes about this enrollment period..."
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    rows={3}
                                    className={errors.description ? 'border-destructive' : ''}
                                />
                                {errors.description && <p className="text-sm text-destructive">{errors.description}</p>}
                                <p className="text-sm text-muted-foreground">Maximum 1000 characters</p>
                            </div>

                            {/* Student Type Settings */}
                            <div className="space-y-4">
                                <h3 className="text-lg font-semibold">Student Enrollment Settings</h3>

                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="allow_new_students"
                                        checked={data.allow_new_students}
                                        onCheckedChange={(checked) => setData('allow_new_students', checked as boolean)}
                                    />
                                    <Label htmlFor="allow_new_students" className="cursor-pointer text-sm font-normal">
                                        Allow new students to enroll
                                    </Label>
                                </div>

                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="allow_returning_students"
                                        checked={data.allow_returning_students}
                                        onCheckedChange={(checked) => setData('allow_returning_students', checked as boolean)}
                                    />
                                    <Label htmlFor="allow_returning_students" className="cursor-pointer text-sm font-normal">
                                        Allow returning students to enroll
                                    </Label>
                                </div>
                            </div>

                            {/* Actions */}
                            <div className="flex gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Updating...' : 'Update Enrollment Period'}
                                </Button>
                                <Button type="button" variant="outline" onClick={() => window.history.back()} disabled={processing}>
                                    Cancel
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

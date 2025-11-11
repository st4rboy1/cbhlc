import { SchoolYearSelect } from '@/components/school-year-select';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { DatePicker } from '@/components/ui/date-picker';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { format } from 'date-fns';
import { toast } from 'sonner';

interface SchoolYear {
    id: number;
    name: string;
    status: string;
    is_active: boolean;
}

interface Props {
    schoolYears: SchoolYear[];
}

export default function EnrollmentPeriodCreate({ schoolYears }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Administrator', href: '/admin/dashboard' },
        { title: 'Enrollment Periods', href: '/admin/enrollment-periods' },
        { title: 'Create', href: '/admin/enrollment-periods/create' },
    ];

    const { data, setData, post, processing, errors } = useForm({
        school_year_id: '',
        start_date: '',
        end_date: '',
        early_registration_deadline: '',
        regular_registration_deadline: '',
        late_registration_deadline: '',
        description: '',
        allow_new_students: true,
        allow_returning_students: true,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/enrollment-periods', {
            onSuccess: () => {
                toast.success('Enrollment period created successfully');
            },
            onError: () => {
                toast.error('Failed to create enrollment period. Please check the form.');
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Enrollment Period" />
            <div className="px-4 py-6">
                <div className="mb-6">
                    <h1 className="text-2xl font-bold">Create Enrollment Period</h1>
                    <p className="text-muted-foreground">Set up a new school year enrollment period with registration deadlines</p>
                </div>

                <Card className="mx-auto max-w-3xl">
                    <CardHeader>
                        <CardTitle>Enrollment Period Information</CardTitle>
                        <CardDescription>Fill in the details for the new enrollment period</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            {/* School Year */}
                            <SchoolYearSelect
                                value={data.school_year_id}
                                onChange={(value) => setData('school_year_id', value)}
                                schoolYears={schoolYears}
                                error={errors.school_year_id}
                                required
                            />

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
                                        allowFutureDates
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
                                        allowFutureDates
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
                                            allowFutureDates
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
                                            allowFutureDates
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
                                            allowFutureDates
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
                                    {processing ? 'Creating...' : 'Create Enrollment Period'}
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

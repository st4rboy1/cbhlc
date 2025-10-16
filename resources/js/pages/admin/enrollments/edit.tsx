import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Enrollment } from '@/types';
import { Head, useForm } from '@inertiajs/react';

interface Props {
    enrollment: Enrollment;
    statuses: Array<{ value: string; label: string }>;
}

export default function EnrollmentEdit({ enrollment, statuses }: Props) {
    const { data, setData, put, processing, errors } = useForm<{
        status: 'pending' | 'approved' | 'rejected' | 'enrolled' | 'completed';
    }>({ status: enrollment.status as 'pending' | 'approved' | 'rejected' | 'enrolled' | 'completed' });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Enrollments', href: '/admin/enrollments' },
        { title: `Enrollment #${enrollment.id}`, href: `/admin/enrollments/${enrollment.id}` },
        { title: 'Edit', href: `/admin/enrollments/${enrollment.id}/edit` },
    ];

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        put(`/admin/enrollments/${enrollment.id}`);
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Enrollment #${enrollment.id}`} />
            <div className="px-4 py-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Edit Enrollment</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <Label htmlFor="status">Status</Label>
                                <Select
                                    value={data.status}
                                    onValueChange={(value) =>
                                        setData('status', value as 'pending' | 'approved' | 'rejected' | 'enrolled' | 'completed')
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {statuses.map((status) => (
                                            <SelectItem key={status.value} value={status.value}>
                                                {status.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.status && <p className="mt-1 text-xs text-red-500">{errors.status}</p>}
                            </div>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Saving...' : 'Save Changes'}
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

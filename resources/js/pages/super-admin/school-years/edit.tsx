import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { Head, useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';

interface SchoolYear {
    id: number;
    name: string;
    start_year: number;
    end_year: number;
    start_date: string;
    end_date: string;
    status: string;
    is_active: boolean;
}

interface Props {
    schoolYear: SchoolYear;
}

export default function SchoolYearEdit({ schoolYear }: Props) {
    const { data, setData, put, processing, errors, wasSuccessful } = useForm({
        name: schoolYear.name || '',
        start_year: schoolYear.start_year || '',
        end_year: schoolYear.end_year || '',
        start_date: schoolYear.start_date || '',
        end_date: schoolYear.end_date || '',
        status: schoolYear.status || 'upcoming',
        is_active: schoolYear.is_active || false,
    });

    useEffect(() => {
        if (wasSuccessful) toast.success('School year updated successfully.');
    }, [wasSuccessful]);

    return (
        <AppLayout
            breadcrumbs={[
                { title: 'Super Admin', href: '/super-admin/dashboard' },
                { title: 'School Years', href: '/super-admin/school-years' },
                { title: 'Edit', href: '#' },
            ]}
        >
            <Head title="Edit School Year" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Edit School Year</h1>
                <Card className="mx-auto max-w-2xl">
                    <CardHeader>
                        <CardTitle>School Year Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                put(route('super-admin.school-years.update', { school_year: schoolYear.id }));
                            }}
                            className="space-y-4"
                        >
                            <div>
                                <Label>School Year Name</Label>
                                <Input
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    className={errors.name ? 'border-red-500' : ''}
                                />
                                {errors.name && <p className="text-sm text-red-500">{errors.name}</p>}
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <Label>Start Year</Label>
                                    <Input type="number" value={data.start_year} onChange={(e) => setData('start_year', e.target.value)} />
                                </div>
                                <div>
                                    <Label>End Year</Label>
                                    <Input type="number" value={data.end_year} onChange={(e) => setData('end_year', e.target.value)} />
                                </div>
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <Label>Start Date</Label>
                                    <Input type="date" value={data.start_date} onChange={(e) => setData('start_date', e.target.value)} />
                                </div>
                                <div>
                                    <Label>End Date</Label>
                                    <Input type="date" value={data.end_date} onChange={(e) => setData('end_date', e.target.value)} />
                                </div>
                            </div>
                            <div>
                                <Label>Status</Label>
                                <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="upcoming">Upcoming</SelectItem>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="completed">Completed</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="flex items-center space-x-2">
                                <Checkbox checked={data.is_active} onCheckedChange={(checked) => setData('is_active', checked as boolean)} />
                                <Label>Set as active school year</Label>
                            </div>
                            <div className="flex gap-4">
                                <Button type="submit" disabled={processing}>
                                    Update
                                </Button>
                                <Button type="button" variant="outline" onClick={() => window.history.back()}>
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

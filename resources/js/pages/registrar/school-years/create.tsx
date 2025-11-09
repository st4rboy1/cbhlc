import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';

export default function SchoolYearCreate() {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Registrar', href: '/registrar/dashboard' },
        { title: 'School Years', href: '/registrar/school-years' },
        { title: 'Create', href: '/registrar/school-years/create' },
    ];

    const { data, setData, post, processing, errors, wasSuccessful } = useForm({
        name: '',
        start_year: '',
        end_year: '',
        start_date: '',
        end_date: '',
        status: 'upcoming',
        is_active: false,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('registrar.school-years.store'));
    };

    useEffect(() => {
        if (wasSuccessful) {
            toast.success('School year created successfully.');
        }
    }, [wasSuccessful]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create School Year" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Create New School Year</h1>
                <Card className="mx-auto max-w-2xl">
                    <CardHeader>
                        <CardTitle>School Year Information</CardTitle>
                        <CardDescription>Add a new academic school year to the system.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="space-y-4">
                                <div>
                                    <Label htmlFor="name">School Year Name</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        className={errors.name ? 'border-red-500' : ''}
                                        placeholder="e.g., 2024-2025"
                                    />
                                    {errors.name && <p className="mt-1 text-sm text-red-500">{errors.name}</p>}
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <Label htmlFor="start_year">Start Year</Label>
                                        <Input
                                            id="start_year"
                                            type="number"
                                            value={data.start_year}
                                            onChange={(e) => setData('start_year', e.target.value)}
                                            className={errors.start_year ? 'border-red-500' : ''}
                                            placeholder="2024"
                                        />
                                        {errors.start_year && <p className="mt-1 text-sm text-red-500">{errors.start_year}</p>}
                                    </div>

                                    <div>
                                        <Label htmlFor="end_year">End Year</Label>
                                        <Input
                                            id="end_year"
                                            type="number"
                                            value={data.end_year}
                                            onChange={(e) => setData('end_year', e.target.value)}
                                            className={errors.end_year ? 'border-red-500' : ''}
                                            placeholder="2025"
                                        />
                                        {errors.end_year && <p className="mt-1 text-sm text-red-500">{errors.end_year}</p>}
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <Label htmlFor="start_date">Start Date</Label>
                                        <Input
                                            id="start_date"
                                            type="date"
                                            value={data.start_date}
                                            onChange={(e) => setData('start_date', e.target.value)}
                                            className={errors.start_date ? 'border-red-500' : ''}
                                        />
                                        {errors.start_date && <p className="mt-1 text-sm text-red-500">{errors.start_date}</p>}
                                    </div>

                                    <div>
                                        <Label htmlFor="end_date">End Date</Label>
                                        <Input
                                            id="end_date"
                                            type="date"
                                            value={data.end_date}
                                            onChange={(e) => setData('end_date', e.target.value)}
                                            className={errors.end_date ? 'border-red-500' : ''}
                                        />
                                        {errors.end_date && <p className="mt-1 text-sm text-red-500">{errors.end_date}</p>}
                                    </div>
                                </div>

                                <div>
                                    <Label htmlFor="status">Status</Label>
                                    <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                                        <SelectTrigger className={errors.status ? 'border-red-500' : ''}>
                                            <SelectValue placeholder="Select status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="upcoming">Upcoming</SelectItem>
                                            <SelectItem value="active">Active</SelectItem>
                                            <SelectItem value="completed">Completed</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.status && <p className="mt-1 text-sm text-red-500">{errors.status}</p>}
                                </div>

                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="is_active"
                                        checked={data.is_active}
                                        onCheckedChange={(checked) => setData('is_active', checked as boolean)}
                                    />
                                    <Label htmlFor="is_active" className="text-sm font-normal">
                                        Set as active school year (will deactivate others)
                                    </Label>
                                </div>
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    Create School Year
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

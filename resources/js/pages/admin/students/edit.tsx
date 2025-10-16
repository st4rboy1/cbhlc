import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Student } from '@/types';
import { Head, useForm } from '@inertiajs/react';

interface Props {
    student: Student;
}

export default function StudentEdit({ student }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        first_name: student.first_name,
        last_name: student.last_name,
        email: student.email,
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Students', href: '/admin/students' },
        { title: student.full_name, href: `/admin/students/${student.id}` },
        { title: 'Edit', href: `/admin/students/${student.id}/edit` },
    ];

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        put(`/admin/students/${student.id}`);
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Student - ${student.full_name}`} />
            <div className="px-4 py-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Edit Student</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <Label htmlFor="first_name">First Name</Label>
                                <Input id="first_name" value={data.first_name} onChange={(e) => setData('first_name', e.target.value)} />
                                {errors.first_name && <p className="mt-1 text-xs text-red-500">{errors.first_name}</p>}
                            </div>
                            <div>
                                <Label htmlFor="last_name">Last Name</Label>
                                <Input id="last_name" value={data.last_name} onChange={(e) => setData('last_name', e.target.value)} />
                                {errors.last_name && <p className="mt-1 text-xs text-red-500">{errors.last_name}</p>}
                            </div>
                            <div>
                                <Label htmlFor="email">Email</Label>
                                <Input id="email" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} />
                                {errors.email && <p className="mt-1 text-xs text-red-500">{errors.email}</p>}
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

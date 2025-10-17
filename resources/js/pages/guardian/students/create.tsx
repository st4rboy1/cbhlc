import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';

export default function GuardianStudentsCreate() {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Guardian', href: '/guardian/dashboard' },
        { title: 'Students', href: '/guardian/students' },
        { title: 'Create', href: '#' },
    ];

    const { data, setData, post, processing, errors } = useForm({
        first_name: '',
        last_name: '',
        middle_name: '',
        birthdate: '',
        gender: '',
        grade_level: '',
        contact_number: '',
        address: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('guardian.students.store'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Add New Student" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Add New Student</h1>

                <Card className="mx-auto max-w-2xl">
                    <CardHeader>
                        <CardTitle>Student Information</CardTitle>
                        <CardDescription>Fill in the details to add a new student.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-4">
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div className="space-y-2">
                                    <Label htmlFor="first_name">First Name</Label>
                                    <Input id="first_name" value={data.first_name} onChange={(e) => setData('first_name', e.target.value)} required />
                                    {errors.first_name && <p className="text-sm text-destructive">{errors.first_name}</p>}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="middle_name">Middle Name</Label>
                                    <Input id="middle_name" value={data.middle_name} onChange={(e) => setData('middle_name', e.target.value)} />
                                    {errors.middle_name && <p className="text-sm text-destructive">{errors.middle_name}</p>}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="last_name">Last Name</Label>
                                    <Input id="last_name" value={data.last_name} onChange={(e) => setData('last_name', e.target.value)} required />
                                    {errors.last_name && <p className="text-sm text-destructive">{errors.last_name}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="birthdate">Birthdate</Label>
                                    <Input
                                        id="birthdate"
                                        type="date"
                                        value={data.birthdate}
                                        onChange={(e) => setData('birthdate', e.target.value)}
                                        required
                                    />
                                    {errors.birthdate && <p className="text-sm text-destructive">{errors.birthdate}</p>}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="gender">Gender</Label>
                                    <Select onValueChange={(value) => setData('gender', value)} value={data.gender} required>
                                        <SelectTrigger id="gender">
                                            <SelectValue placeholder="Select gender" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="Male">Male</SelectItem>
                                            <SelectItem value="Female">Female</SelectItem>
                                            <SelectItem value="Other">Other</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.gender && <p className="text-sm text-destructive">{errors.gender}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="grade_level">Grade Level</Label>
                                    <Input
                                        id="grade_level"
                                        value={data.grade_level}
                                        onChange={(e) => setData('grade_level', e.target.value)}
                                        required
                                    />
                                    {errors.grade_level && <p className="text-sm text-destructive">{errors.grade_level}</p>}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="contact_number">Contact Number</Label>
                                    <Input
                                        id="contact_number"
                                        type="tel"
                                        value={data.contact_number}
                                        onChange={(e) => setData('contact_number', e.target.value)}
                                    />
                                    {errors.contact_number && <p className="text-sm text-destructive">{errors.contact_number}</p>}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="address">Address</Label>
                                <Textarea id="address" value={data.address} onChange={(e) => setData('address', e.target.value)} />
                                {errors.address && <p className="text-sm text-destructive">{errors.address}</p>}
                            </div>

                            <Button type="submit" disabled={processing}>
                                Add Student
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

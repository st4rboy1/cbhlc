import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';

interface Student {
    id: number;
    first_name: string;
    middle_name: string;
    last_name: string;
    birthdate: string;
    gender: string;
    address: string;
    contact_number: string;
    email: string;
    birth_place: string;
    nationality: string;
    religion: string;
    grade_level: string;
    section: string;
}

interface Props {
    student: Student;
    gradeLevels: string[];
}

export default function RegistrarStudentsEdit({ student, gradeLevels }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Registrar', href: '/registrar/dashboard' },
        { title: 'Students', href: '/registrar/students' },
        { title: student.first_name + ' ' + student.last_name, href: `/registrar/students/${student.id}` },
        { title: 'Edit', href: '#' },
    ];

    const { data, setData, put, processing, errors } = useForm({
        first_name: student.first_name || '',
        middle_name: student.middle_name || '',
        last_name: student.last_name || '',
        birthdate: student.birthdate || '',
        gender: student.gender || '',
        address: student.address || '',
        contact_number: student.contact_number || '',
        email: student.email || '',
        birth_place: student.birth_place || '',
        nationality: student.nationality || '',
        religion: student.religion || '',
        grade_level: student.grade_level || '',
        section: student.section || '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('registrar.students.update', { student: student.id }));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Student" />
            <div className="container mx-auto px-4 py-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Edit Student Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-6">
                            <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
                                <div className="space-y-2">
                                    <Label htmlFor="first_name">First Name</Label>
                                    <Input id="first_name" value={data.first_name} onChange={(e) => setData('first_name', e.target.value)} required />
                                    <InputError message={errors.first_name} />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="middle_name">Middle Name</Label>
                                    <Input id="middle_name" value={data.middle_name} onChange={(e) => setData('middle_name', e.target.value)} />
                                    <InputError message={errors.middle_name} />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="last_name">Last Name</Label>
                                    <Input id="last_name" value={data.last_name} onChange={(e) => setData('last_name', e.target.value)} required />
                                    <InputError message={errors.last_name} />
                                </div>
                            </div>

                            <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
                                <div className="space-y-2">
                                    <Label htmlFor="birthdate">Birthdate</Label>
                                    <Input
                                        id="birthdate"
                                        type="date"
                                        value={data.birthdate}
                                        onChange={(e) => setData('birthdate', e.target.value)}
                                        required
                                    />
                                    <InputError message={errors.birthdate} />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="gender">Gender</Label>
                                    <Select value={data.gender} onValueChange={(value) => setData('gender', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select gender" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="Male">Male</SelectItem>
                                            <SelectItem value="Female">Female</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.gender} />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="email">Email</Label>
                                    <Input id="email" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} />
                                    <InputError message={errors.email} />
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="address">Address</Label>
                                <Textarea id="address" value={data.address} onChange={(e) => setData('address', e.target.value)} required />
                                <InputError message={errors.address} />
                            </div>

                            <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
                                <div className="space-y-2">
                                    <Label htmlFor="contact_number">Contact Number</Label>
                                    <Input
                                        id="contact_number"
                                        value={data.contact_number}
                                        onChange={(e) => setData('contact_number', e.target.value)}
                                    />
                                    <InputError message={errors.contact_number} />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="birth_place">Birth Place</Label>
                                    <Input id="birth_place" value={data.birth_place} onChange={(e) => setData('birth_place', e.target.value)} />
                                    <InputError message={errors.birth_place} />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="nationality">Nationality</Label>
                                    <Input id="nationality" value={data.nationality} onChange={(e) => setData('nationality', e.target.value)} />
                                    <InputError message={errors.nationality} />
                                </div>
                            </div>

                            <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
                                <div className="space-y-2">
                                    <Label htmlFor="religion">Religion</Label>
                                    <Input id="religion" value={data.religion} onChange={(e) => setData('religion', e.target.value)} />
                                    <InputError message={errors.religion} />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="grade_level">Grade Level</Label>
                                    <Select value={data.grade_level} onValueChange={(value) => setData('grade_level', value)}>
                                        <SelectTrigger>
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
                                    <InputError message={errors.grade_level} />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="section">Section</Label>
                                    <Input id="section" value={data.section} onChange={(e) => setData('section', e.target.value)} />
                                    <InputError message={errors.section} />
                                </div>
                            </div>

                            <div className="flex justify-end">
                                <Button type="submit" disabled={processing}>
                                    Update Student
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

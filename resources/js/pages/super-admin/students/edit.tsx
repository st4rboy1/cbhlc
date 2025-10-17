import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';

interface GuardianOption {
    id: number;
    user: { name: string; email: string };
}

interface Props {
    student: {
        id: string | number;
        student_id: string;
        first_name: string;
        middle_name: string | null;
        last_name: string;
        grade: string;
        status: string;
        birth_date: string;
        address: string;
        phone: string;
        email: string;
        gender: string;
        nationality: string;
        religion: string;
        guardians: GuardianOption[];
    };
    guardians: GuardianOption[];
    gradelevels: string[];
}

export default function StudentEdit({ student, guardians, gradelevels }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Students', href: '/super-admin/students' },
        { title: `Edit ${student.first_name} ${student.last_name}`, href: `/super-admin/students/${student.id}/edit` },
    ];

    const { data, setData, put, processing, errors, wasSuccessful } = useForm({
        first_name: student.first_name,
        middle_name: student.middle_name || '',
        last_name: student.last_name,
        birth_date: student.birth_date,
        gender: student.gender,
        nationality: student.nationality,
        religion: student.religion,
        address: student.address,
        phone: student.phone,
        email: student.email,
        grade: student.grade,
        guardian_ids: student.guardians.map((g) => g.id),
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('super-admin.students.update', { student: student.id }));
    };

    useEffect(() => {
        if (wasSuccessful) {
            toast.success('Student updated successfully.');
        }
    }, [wasSuccessful]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${student.first_name} ${student.last_name}`} />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">
                    Edit Student: {student.first_name} {student.last_name}
                </h1>
                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="first_name">First Name</Label>
                            <Input
                                id="first_name"
                                value={data.first_name}
                                onChange={(e) => setData('first_name', e.target.value)}
                                className={errors.first_name ? 'border-red-500' : ''}
                            />
                            {errors.first_name && <p className="mt-1 text-sm text-red-500">{errors.first_name}</p>}
                        </div>
                        <div>
                            <Label htmlFor="middle_name">Middle Name</Label>
                            <Input
                                id="middle_name"
                                value={data.middle_name}
                                onChange={(e) => setData('middle_name', e.target.value)}
                                className={errors.middle_name ? 'border-red-500' : ''}
                            />
                            {errors.middle_name && <p className="mt-1 text-sm text-red-500">{errors.middle_name}</p>}
                        </div>
                        <div>
                            <Label htmlFor="last_name">Last Name</Label>
                            <Input
                                id="last_name"
                                value={data.last_name}
                                onChange={(e) => setData('last_name', e.target.value)}
                                className={errors.last_name ? 'border-red-500' : ''}
                            />
                            {errors.last_name && <p className="mt-1 text-sm text-red-500">{errors.last_name}</p>}
                        </div>
                        <div>
                            <Label htmlFor="birth_date">Birth Date</Label>
                            <Input
                                id="birth_date"
                                type="date"
                                value={data.birth_date}
                                onChange={(e) => setData('birth_date', e.target.value)}
                                className={errors.birth_date ? 'border-red-500' : ''}
                            />
                            {errors.birth_date && <p className="mt-1 text-sm text-red-500">{errors.birth_date}</p>}
                        </div>
                        <div>
                            <Label htmlFor="gender">Gender</Label>
                            <Select value={data.gender} onValueChange={(value) => setData('gender', value)}>
                                <SelectTrigger className={errors.gender ? 'border-red-500' : ''}>
                                    <SelectValue placeholder="Select Gender" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="Male">Male</SelectItem>
                                    <SelectItem value="Female">Female</SelectItem>
                                    <SelectItem value="Other">Other</SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.gender && <p className="mt-1 text-sm text-red-500">{errors.gender}</p>}
                        </div>
                        <div>
                            <Label htmlFor="nationality">Nationality</Label>
                            <Input
                                id="nationality"
                                value={data.nationality}
                                onChange={(e) => setData('nationality', e.target.value)}
                                className={errors.nationality ? 'border-red-500' : ''}
                            />
                            {errors.nationality && <p className="mt-1 text-sm text-red-500">{errors.nationality}</p>}
                        </div>
                        <div>
                            <Label htmlFor="religion">Religion</Label>
                            <Input
                                id="religion"
                                value={data.religion}
                                onChange={(e) => setData('religion', e.target.value)}
                                className={errors.religion ? 'border-red-500' : ''}
                            />
                            {errors.religion && <p className="mt-1 text-sm text-red-500">{errors.religion}</p>}
                        </div>
                        <div>
                            <Label htmlFor="phone">Phone</Label>
                            <Input
                                id="phone"
                                value={data.phone}
                                onChange={(e) => setData('phone', e.target.value)}
                                className={errors.phone ? 'border-red-500' : ''}
                            />
                            {errors.phone && <p className="mt-1 text-sm text-red-500">{errors.phone}</p>}
                        </div>
                        <div>
                            <Label htmlFor="email">Email</Label>
                            <Input
                                id="email"
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                className={errors.email ? 'border-red-500' : ''}
                            />
                            {errors.email && <p className="mt-1 text-sm text-red-500">{errors.email}</p>}
                        </div>
                        <div>
                            <Label htmlFor="grade">Grade</Label>
                            <Select value={data.grade} onValueChange={(value) => setData('grade', value)}>
                                <SelectTrigger className={errors.grade ? 'border-red-500' : ''}>
                                    <SelectValue placeholder="Select Grade Level" />
                                </SelectTrigger>
                                <SelectContent>
                                    {gradelevels.map((grade) => (
                                        <SelectItem key={grade} value={grade}>
                                            {grade}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.grade && <p className="mt-1 text-sm text-red-500">{errors.grade}</p>}
                        </div>
                        <div className="col-span-full">
                            <Label htmlFor="address">Address</Label>
                            <Textarea
                                id="address"
                                value={data.address}
                                onChange={(e) => setData('address', e.target.value)}
                                className={errors.address ? 'border-red-500' : 'h-auto w-full'}
                                rows={3}
                            />
                            {errors.address && <p className="mt-1 text-sm text-red-500">{errors.address}</p>}
                        </div>
                    </div>

                    <div>
                        <Label htmlFor="guardians">Guardians</Label>
                        <Select value={data.guardian_ids[0]?.toString() || ''} onValueChange={(value) => setData('guardian_ids', [parseInt(value)])}>
                            <SelectTrigger className={errors.guardian_ids ? 'border-red-500' : ''}>
                                <SelectValue placeholder="Select Primary Guardian" />
                            </SelectTrigger>
                            <SelectContent>
                                {guardians.map((guardian) => (
                                    <SelectItem key={guardian.id} value={guardian.id.toString()}>
                                        {guardian.user.name} ({guardian.user.email})
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.guardian_ids && <p className="mt-1 text-sm text-red-500">{errors.guardian_ids}</p>}
                    </div>

                    <div className="flex items-center gap-4">
                        <Button type="submit" disabled={processing}>
                            Update Student
                        </Button>
                        <Button type="button" variant="outline" onClick={() => window.history.back()}>
                            Cancel
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}

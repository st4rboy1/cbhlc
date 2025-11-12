import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { store } from '@/routes/registrar/students';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';

interface Guardian {
    id: number;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    user: {
        id: number;
        name: string;
        email: string;
    };
}

interface Props {
    guardians: Guardian[];
    gradelevels: string[];
}

export default function StudentCreate({ guardians, gradelevels }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Registrar', href: '/registrar/dashboard' },
        { title: 'Students', href: '/registrar/students' },
        { title: 'Create Student', href: '/registrar/students/create' },
    ];

    const { data, setData, post, processing, errors, wasSuccessful } = useForm({
        first_name: '',
        middle_name: '',
        last_name: '',
        birthdate: '',
        birth_place: '',
        gender: '',
        nationality: '',
        religion: '',
        address: '',
        phone: '',
        email: '',
        grade_level: '',
        guardian_ids: [] as number[],
    });

    const [selectedGuardians, setSelectedGuardians] = useState<number[]>([]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(store().url);
    };

    const handleGuardianToggle = (guardianId: number) => {
        const newSelection = selectedGuardians.includes(guardianId)
            ? selectedGuardians.filter((id) => id !== guardianId)
            : [...selectedGuardians, guardianId];

        setSelectedGuardians(newSelection);
        setData('guardian_ids', newSelection);
    };

    useEffect(() => {
        if (wasSuccessful) {
            toast.success('Student created successfully.');
        }
    }, [wasSuccessful]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Student" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Create New Student</h1>
                <Card className="mx-auto max-w-4xl">
                    <CardHeader>
                        <CardTitle>Student Information</CardTitle>
                        <CardDescription>Add a new student to the system.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            {/* Personal Information */}
                            <div className="space-y-4">
                                <h3 className="text-lg font-semibold">Personal Information</h3>
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                    <div>
                                        <Label htmlFor="first_name">First Name</Label>
                                        <Input
                                            id="first_name"
                                            value={data.first_name}
                                            onChange={(e) => setData('first_name', e.target.value)}
                                            className={errors.first_name ? 'border-red-500' : ''}
                                            placeholder="Enter first name"
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
                                            placeholder="Enter middle name (optional)"
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
                                            placeholder="Enter last name"
                                        />
                                        {errors.last_name && <p className="mt-1 text-sm text-red-500">{errors.last_name}</p>}
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <Label htmlFor="birthdate">Birth Date</Label>
                                        <Input
                                            id="birthdate"
                                            type="date"
                                            value={data.birthdate}
                                            onChange={(e) => setData('birthdate', e.target.value)}
                                            className={errors.birthdate ? 'border-red-500' : ''}
                                        />
                                        {errors.birthdate && <p className="mt-1 text-sm text-red-500">{errors.birthdate}</p>}
                                    </div>

                                    <div>
                                        <Label htmlFor="birth_place">Birth Place</Label>
                                        <Input
                                            id="birth_place"
                                            value={data.birth_place}
                                            onChange={(e) => setData('birth_place', e.target.value)}
                                            className={errors.birth_place ? 'border-red-500' : ''}
                                            placeholder="Enter birth place"
                                        />
                                        {errors.birth_place && <p className="mt-1 text-sm text-red-500">{errors.birth_place}</p>}
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <Label htmlFor="gender">Gender</Label>
                                        <Select value={data.gender} onValueChange={(value) => setData('gender', value)}>
                                            <SelectTrigger className={errors.gender ? 'border-red-500' : ''}>
                                                <SelectValue placeholder="Select gender" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="Male">Male</SelectItem>
                                                <SelectItem value="Female">Female</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        {errors.gender && <p className="mt-1 text-sm text-red-500">{errors.gender}</p>}
                                    </div>

                                    <div>
                                        <Label htmlFor="grade_level">Grade Level</Label>
                                        <Select value={data.grade_level} onValueChange={(value) => setData('grade_level', value)}>
                                            <SelectTrigger className={errors.grade_level ? 'border-red-500' : ''}>
                                                <SelectValue placeholder="Select grade level" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {gradelevels.map((grade) => (
                                                    <SelectItem key={grade} value={grade}>
                                                        {grade}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.grade_level && <p className="mt-1 text-sm text-red-500">{errors.grade_level}</p>}
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <Label htmlFor="nationality">Nationality</Label>
                                        <Input
                                            id="nationality"
                                            value={data.nationality}
                                            onChange={(e) => setData('nationality', e.target.value)}
                                            className={errors.nationality ? 'border-red-500' : ''}
                                            placeholder="Enter nationality"
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
                                            placeholder="Enter religion"
                                        />
                                        {errors.religion && <p className="mt-1 text-sm text-red-500">{errors.religion}</p>}
                                    </div>
                                </div>
                            </div>

                            {/* Contact Information */}
                            <div className="space-y-4">
                                <h3 className="text-lg font-semibold">Contact Information</h3>
                                <div>
                                    <Label htmlFor="address">Address</Label>
                                    <Input
                                        id="address"
                                        value={data.address}
                                        onChange={(e) => setData('address', e.target.value)}
                                        className={errors.address ? 'border-red-500' : ''}
                                        placeholder="Enter complete address"
                                    />
                                    {errors.address && <p className="mt-1 text-sm text-red-500">{errors.address}</p>}
                                </div>

                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <Label htmlFor="phone">Phone Number</Label>
                                        <Input
                                            id="phone"
                                            value={data.phone}
                                            onChange={(e) => setData('phone', e.target.value)}
                                            className={errors.phone ? 'border-red-500' : ''}
                                            placeholder="Enter phone number"
                                        />
                                        {errors.phone && <p className="mt-1 text-sm text-red-500">{errors.phone}</p>}
                                    </div>

                                    <div>
                                        <Label htmlFor="email">Email Address (Optional)</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            className={errors.email ? 'border-red-500' : ''}
                                            placeholder="student@example.com"
                                        />
                                        {errors.email && <p className="mt-1 text-sm text-red-500">{errors.email}</p>}
                                    </div>
                                </div>
                            </div>

                            {/* Guardian Selection */}
                            <div className="space-y-4">
                                <div>
                                    <h3 className="text-lg font-semibold">Guardians</h3>
                                    <p className="text-sm text-muted-foreground">
                                        Select at least one guardian for this student. The first selected will be the primary guardian.
                                    </p>
                                </div>
                                {errors.guardian_ids && <p className="text-sm text-red-500">{errors.guardian_ids}</p>}
                                <div className="grid grid-cols-1 gap-3 md:grid-cols-2">
                                    {guardians.map((guardian) => (
                                        <div
                                            key={guardian.id}
                                            className={`cursor-pointer rounded-lg border p-4 transition-colors ${
                                                selectedGuardians.includes(guardian.id)
                                                    ? 'border-primary bg-primary/5'
                                                    : 'border-border hover:bg-accent'
                                            }`}
                                            onClick={() => handleGuardianToggle(guardian.id)}
                                        >
                                            <div className="flex items-start justify-between">
                                                <div>
                                                    <p className="font-medium">
                                                        {guardian.first_name} {guardian.middle_name ? guardian.middle_name + ' ' : ''}
                                                        {guardian.last_name}
                                                    </p>
                                                    <p className="text-sm text-muted-foreground">{guardian.user.email}</p>
                                                </div>
                                                <div
                                                    className={`mt-1 h-4 w-4 rounded border ${
                                                        selectedGuardians.includes(guardian.id) ? 'border-primary bg-primary' : 'border-border'
                                                    }`}
                                                >
                                                    {selectedGuardians.includes(guardian.id) && (
                                                        <svg
                                                            className="h-full w-full text-primary-foreground"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    )}
                                                </div>
                                            </div>
                                            {selectedGuardians.indexOf(guardian.id) === 0 && selectedGuardians.length > 0 && (
                                                <p className="mt-2 text-xs text-primary">Primary Guardian</p>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    Create Student
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

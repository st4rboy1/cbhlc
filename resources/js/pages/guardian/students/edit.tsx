import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { DatePicker } from '@/components/ui/date-picker';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { destroy, update } from '@/routes/guardian/students';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { format } from 'date-fns';
import { AlertCircle } from 'lucide-react';
import { toast } from 'sonner';

interface Student {
    id: number;
    student_id: string;
    first_name: string;
    middle_name: string;
    last_name: string;
    birthdate: string;
    gender: string;
    grade_level: string;
    contact_number: string;
    email: string;
    address: string;
    birth_place: string;
    nationality: string;
    religion: string;
}

interface Props {
    student: Student;
}

export default function GuardianStudentsEdit({ student }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Guardian', href: '/guardian/dashboard' },
        { title: 'Students', href: '/guardian/students' },
        { title: `${student.first_name} ${student.last_name}`, href: `/guardian/students/${student.id}` },
        { title: 'Edit', href: '#' },
    ];

    const { data, setData, put, processing, errors } = useForm({
        first_name: student.first_name || '',
        last_name: student.last_name || '',
        middle_name: student.middle_name || '',
        birthdate: student.birthdate || '',
        gender: student.gender || '',
        grade_level: student.grade_level || '',
        contact_number: student.contact_number || '',
        email: student.email || '',
        address: student.address || '',
        birth_place: student.birth_place || '',
        nationality: student.nationality || '',
        religion: student.religion || '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(update(student.id).url, {
            onSuccess: () => {
                toast.success('Student information updated successfully');
            },
            onError: () => {
                toast.error('Failed to update student information. Please check the form.');
            },
        });
    };

    const handleDelete = () => {
        if (
            confirm(
                'Are you sure you want to remove this student from your account? This action cannot be undone. The student record will remain in the system but will no longer be linked to your account.',
            )
        ) {
            router.delete(destroy(student.id).url, {
                onSuccess: () => {
                    toast.success('Student removed successfully');
                },
                onError: (errors: Record<string, string>) => {
                    if (errors?.error) {
                        toast.error(errors.error);
                    } else {
                        toast.error('Failed to remove student');
                    }
                },
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Student" />
            <div className="px-4 py-6">
                <div className="mb-6">
                    <h1 className="text-2xl font-bold">Edit Student Information</h1>
                    <p className="text-muted-foreground">Update student details</p>
                </div>

                <Card className="mx-auto max-w-2xl">
                    <CardHeader>
                        <CardTitle>Student Information</CardTitle>
                        <CardDescription>
                            Update information for{' '}
                            <span className="font-semibold">
                                {student.first_name} {student.last_name}
                            </span>
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            {/* Student ID (Read-only) */}
                            <div className="space-y-2">
                                <Label htmlFor="student_id">Student ID</Label>
                                <div className="rounded-md border bg-muted px-3 py-2 text-sm">{student.student_id}</div>
                                <p className="text-xs text-muted-foreground">Student ID cannot be changed</p>
                            </div>

                            {/* Name Fields */}
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div className="space-y-2">
                                    <Label htmlFor="first_name">
                                        First Name <span className="text-destructive">*</span>
                                    </Label>
                                    <Input
                                        id="first_name"
                                        value={data.first_name}
                                        onChange={(e) => setData('first_name', e.target.value)}
                                        className={errors.first_name ? 'border-destructive' : ''}
                                    />
                                    {errors.first_name && <p className="text-sm text-destructive">{errors.first_name}</p>}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="middle_name">Middle Name</Label>
                                    <Input id="middle_name" value={data.middle_name} onChange={(e) => setData('middle_name', e.target.value)} />
                                    {errors.middle_name && <p className="text-sm text-destructive">{errors.middle_name}</p>}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="last_name">
                                        Last Name <span className="text-destructive">*</span>
                                    </Label>
                                    <Input
                                        id="last_name"
                                        value={data.last_name}
                                        onChange={(e) => setData('last_name', e.target.value)}
                                        className={errors.last_name ? 'border-destructive' : ''}
                                    />
                                    {errors.last_name && <p className="text-sm text-destructive">{errors.last_name}</p>}
                                </div>
                            </div>

                            {/* Birthdate and Gender */}
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="birthdate">
                                        Birthdate <span className="text-destructive">*</span>
                                    </Label>
                                    <DatePicker
                                        id="birthdate"
                                        value={data.birthdate ? new Date(data.birthdate) : undefined}
                                        onChange={(date) => setData('birthdate', date ? format(date, 'yyyy-MM-dd') : '')}
                                        placeholder="Select birthdate"
                                        error={!!errors.birthdate}
                                    />
                                    {errors.birthdate && <p className="text-sm text-destructive">{errors.birthdate}</p>}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="gender">
                                        Gender <span className="text-destructive">*</span>
                                    </Label>
                                    <Select onValueChange={(value) => setData('gender', value)} value={data.gender}>
                                        <SelectTrigger id="gender" className={errors.gender ? 'border-destructive' : ''}>
                                            <SelectValue placeholder="Select gender" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="Male">Male</SelectItem>
                                            <SelectItem value="Female">Female</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.gender && <p className="text-sm text-destructive">{errors.gender}</p>}
                                </div>
                            </div>

                            {/* Grade Level (Read-only based on enrollments) */}
                            <div className="space-y-2">
                                <Label htmlFor="grade_level">Current Grade Level</Label>
                                <div className="rounded-md border bg-muted px-3 py-2 text-sm">{student.grade_level || 'Not enrolled'}</div>
                                <p className="text-xs text-muted-foreground">Grade level is determined by enrollment status</p>
                            </div>

                            {/* Birth Place, Nationality, Religion */}
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div className="space-y-2">
                                    <Label htmlFor="birth_place">Birth Place</Label>
                                    <Input id="birth_place" value={data.birth_place} onChange={(e) => setData('birth_place', e.target.value)} />
                                    {errors.birth_place && <p className="text-sm text-destructive">{errors.birth_place}</p>}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="nationality">Nationality</Label>
                                    <Input id="nationality" value={data.nationality} onChange={(e) => setData('nationality', e.target.value)} />
                                    {errors.nationality && <p className="text-sm text-destructive">{errors.nationality}</p>}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="religion">Religion</Label>
                                    <Input id="religion" value={data.religion} onChange={(e) => setData('religion', e.target.value)} />
                                    {errors.religion && <p className="text-sm text-destructive">{errors.religion}</p>}
                                </div>
                            </div>

                            {/* Contact Information */}
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
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
                                <div className="space-y-2">
                                    <Label htmlFor="email">Email Address</Label>
                                    <Input id="email" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} />
                                    {errors.email && <p className="text-sm text-destructive">{errors.email}</p>}
                                </div>
                            </div>

                            {/* Address */}
                            <div className="space-y-2">
                                <Label htmlFor="address">
                                    Address <span className="text-destructive">*</span>
                                </Label>
                                <Textarea
                                    id="address"
                                    value={data.address}
                                    onChange={(e) => setData('address', e.target.value)}
                                    className={errors.address ? 'border-destructive' : ''}
                                />
                                {errors.address && <p className="text-sm text-destructive">{errors.address}</p>}
                            </div>

                            {/* Important Note */}
                            <div className="rounded-lg border border-blue-200 bg-blue-50/50 p-4 dark:border-blue-800 dark:bg-blue-950/20">
                                <div className="flex gap-2">
                                    <AlertCircle className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    <div className="flex-1">
                                        <h4 className="mb-1 font-semibold text-blue-700 dark:text-blue-400">Note</h4>
                                        <p className="text-sm text-blue-600 dark:text-blue-500">
                                            Changes to student information will be updated across all enrollment records. Grade level cannot be
                                            directly changed and is determined by enrollment status.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Form Actions */}
                            <div className="flex flex-wrap gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Updating...' : 'Update Student'}
                                </Button>
                                <Button type="button" variant="outline" onClick={() => window.history.back()} disabled={processing}>
                                    Cancel
                                </Button>
                                <Button type="button" variant="destructive" onClick={handleDelete} disabled={processing} className="ml-auto">
                                    Remove Student
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

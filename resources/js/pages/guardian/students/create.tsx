import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { DatePicker } from '@/components/ui/date-picker';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { store } from '@/routes/guardian/students';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { format } from 'date-fns';
import { FileText, Upload, X } from 'lucide-react';

interface Props {
    gradeLevels: string[];
}

export default function GuardianStudentsCreate({ gradeLevels }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Guardian', href: '/guardian/dashboard' },
        { title: 'Students', href: '/guardian/students' },
        { title: 'Create', href: '#' },
    ];

    const { data, setData, post, processing, errors } = useForm<{
        first_name: string;
        last_name: string;
        middle_name: string;
        birthdate: string;
        gender: string;
        grade_level: string;
        contact_number: string;
        email: string;
        address: string;
        birth_place: string;
        nationality: string;
        religion: string;
        birth_certificate?: File | null;
        report_card?: File | null;
        form_138?: File | null;
        good_moral?: File | null;
    }>({
        first_name: '',
        last_name: '',
        middle_name: '',
        birthdate: '',
        gender: '',
        grade_level: '',
        contact_number: '',
        email: '',
        address: '',
        birth_place: '',
        nationality: '',
        religion: '',
        birth_certificate: null,
        report_card: null,
        form_138: null,
        good_moral: null,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(store().url);
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
                            {/* Name Fields */}
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div className="space-y-2">
                                    <Label htmlFor="first_name">
                                        First Name <span className="text-destructive">*</span>
                                    </Label>
                                    <Input id="first_name" value={data.first_name} onChange={(e) => setData('first_name', e.target.value)} />
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
                                    <Input id="last_name" value={data.last_name} onChange={(e) => setData('last_name', e.target.value)} />
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
                                        <SelectTrigger id="gender">
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

                            {/* Grade Level */}
                            <div className="space-y-2">
                                <Label htmlFor="grade_level">
                                    Grade Level <span className="text-destructive">*</span>
                                </Label>
                                <Select onValueChange={(value) => setData('grade_level', value)} value={data.grade_level}>
                                    <SelectTrigger id="grade_level">
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
                                {errors.grade_level && <p className="text-sm text-destructive">{errors.grade_level}</p>}
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
                                <Textarea id="address" value={data.address} onChange={(e) => setData('address', e.target.value)} />
                                {errors.address && <p className="text-sm text-destructive">{errors.address}</p>}
                            </div>

                            {/* Documents Section */}
                            <div className="space-y-4 border-t pt-4">
                                <div>
                                    <h3 className="text-lg font-semibold">Required Documents</h3>
                                    <p className="text-sm text-muted-foreground">
                                        Upload scanned copies of the following documents (JPEG/PNG, max 50MB each)
                                    </p>
                                </div>

                                {/* Birth Certificate */}
                                <div className="space-y-2">
                                    <Label htmlFor="birth_certificate">
                                        Birth Certificate <span className="text-destructive">*</span>
                                    </Label>
                                    <div className="flex items-center gap-2">
                                        <Input
                                            id="birth_certificate"
                                            type="file"
                                            accept=".jpg,.jpeg,.png"
                                            onChange={(e) => setData('birth_certificate', e.target.files?.[0] || null)}
                                            className="cursor-pointer"
                                        />
                                        {data.birth_certificate && (
                                            <Button type="button" variant="ghost" size="icon" onClick={() => setData('birth_certificate', null)}>
                                                <X className="h-4 w-4" />
                                            </Button>
                                        )}
                                    </div>
                                    {data.birth_certificate && (
                                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                            <FileText className="h-4 w-4" />
                                            <span>{data.birth_certificate.name}</span>
                                            <span className="text-xs">({(data.birth_certificate.size / 1024 / 1024).toFixed(2)} MB)</span>
                                        </div>
                                    )}
                                    {errors.birth_certificate && <p className="text-sm text-destructive">{errors.birth_certificate}</p>}
                                </div>

                                {/* Report Card */}
                                <div className="space-y-2">
                                    <Label htmlFor="report_card">Report Card (Latest)</Label>
                                    <div className="flex items-center gap-2">
                                        <Input
                                            id="report_card"
                                            type="file"
                                            accept=".jpg,.jpeg,.png"
                                            onChange={(e) => setData('report_card', e.target.files?.[0] || null)}
                                            className="cursor-pointer"
                                        />
                                        {data.report_card && (
                                            <Button type="button" variant="ghost" size="icon" onClick={() => setData('report_card', null)}>
                                                <X className="h-4 w-4" />
                                            </Button>
                                        )}
                                    </div>
                                    {data.report_card && (
                                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                            <FileText className="h-4 w-4" />
                                            <span>{data.report_card.name}</span>
                                            <span className="text-xs">({(data.report_card.size / 1024 / 1024).toFixed(2)} MB)</span>
                                        </div>
                                    )}
                                    {errors.report_card && <p className="text-sm text-destructive">{errors.report_card}</p>}
                                </div>

                                {/* Form 138 */}
                                <div className="space-y-2">
                                    <Label htmlFor="form_138">Form 138 (School Records)</Label>
                                    <div className="flex items-center gap-2">
                                        <Input
                                            id="form_138"
                                            type="file"
                                            accept=".jpg,.jpeg,.png"
                                            onChange={(e) => setData('form_138', e.target.files?.[0] || null)}
                                            className="cursor-pointer"
                                        />
                                        {data.form_138 && (
                                            <Button type="button" variant="ghost" size="icon" onClick={() => setData('form_138', null)}>
                                                <X className="h-4 w-4" />
                                            </Button>
                                        )}
                                    </div>
                                    {data.form_138 && (
                                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                            <FileText className="h-4 w-4" />
                                            <span>{data.form_138.name}</span>
                                            <span className="text-xs">({(data.form_138.size / 1024 / 1024).toFixed(2)} MB)</span>
                                        </div>
                                    )}
                                    {errors.form_138 && <p className="text-sm text-destructive">{errors.form_138}</p>}
                                </div>

                                {/* Good Moral Certificate */}
                                <div className="space-y-2">
                                    <Label htmlFor="good_moral">Good Moral Certificate</Label>
                                    <div className="flex items-center gap-2">
                                        <Input
                                            id="good_moral"
                                            type="file"
                                            accept=".jpg,.jpeg,.png"
                                            onChange={(e) => setData('good_moral', e.target.files?.[0] || null)}
                                            className="cursor-pointer"
                                        />
                                        {data.good_moral && (
                                            <Button type="button" variant="ghost" size="icon" onClick={() => setData('good_moral', null)}>
                                                <X className="h-4 w-4" />
                                            </Button>
                                        )}
                                    </div>
                                    {data.good_moral && (
                                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                            <FileText className="h-4 w-4" />
                                            <span>{data.good_moral.name}</span>
                                            <span className="text-xs">({(data.good_moral.size / 1024 / 1024).toFixed(2)} MB)</span>
                                        </div>
                                    )}
                                    {errors.good_moral && <p className="text-sm text-destructive">{errors.good_moral}</p>}
                                </div>
                            </div>

                            <Button type="submit" disabled={processing}>
                                <Upload className="mr-2 h-4 w-4" />
                                Add Student
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

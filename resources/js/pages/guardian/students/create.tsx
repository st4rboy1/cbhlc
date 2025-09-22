import PageLayout from '@/components/PageLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, UserPlus } from 'lucide-react';
import { FormEvent } from 'react';

interface CreateStudentProps {
    gradeLevels: Array<{ value: string; label: string }>;
    relationshipTypes: Array<{ value: string; label: string }>;
}

export default function CreateStudent({ gradeLevels, relationshipTypes }: CreateStudentProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        first_name: '',
        middle_name: '',
        last_name: '',
        birthdate: '',
        grade_level: '',
        gender: '',
        address: '',
        phone: '',
        relationship_type: '',
        is_primary_contact: false,
        email: '',
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        post('/guardian/students', {
            onSuccess: () => reset(),
        });
    };

    return (
        <>
            <Head title="Add New Student" />
            <PageLayout title="ADD NEW STUDENT" currentPage="guardian.students.create">
                <div className="mx-auto max-w-4xl">
                    {/* Back Button */}
                    <div className="mb-6">
                        <Button variant="ghost" asChild>
                            <Link href="/guardian/dashboard">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Back to Dashboard
                            </Link>
                        </Button>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <UserPlus className="h-5 w-5" />
                                Student Registration Form
                            </CardTitle>
                            <CardDescription>Add a new student under your guardianship</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-6">
                                {/* Personal Information */}
                                <div className="space-y-4">
                                    <h3 className="text-lg font-semibold">Personal Information</h3>
                                    <div className="grid gap-4 md:grid-cols-3">
                                        <div>
                                            <Label htmlFor="first_name">
                                                First Name <span className="text-red-500">*</span>
                                            </Label>
                                            <Input
                                                id="first_name"
                                                value={data.first_name}
                                                onChange={(e) => setData('first_name', e.target.value)}
                                                className={errors.first_name ? 'border-red-500' : ''}
                                                required
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
                                            <Label htmlFor="last_name">
                                                Last Name <span className="text-red-500">*</span>
                                            </Label>
                                            <Input
                                                id="last_name"
                                                value={data.last_name}
                                                onChange={(e) => setData('last_name', e.target.value)}
                                                className={errors.last_name ? 'border-red-500' : ''}
                                                required
                                            />
                                            {errors.last_name && <p className="mt-1 text-sm text-red-500">{errors.last_name}</p>}
                                        </div>
                                    </div>

                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <Label htmlFor="birthdate">
                                                Date of Birth <span className="text-red-500">*</span>
                                            </Label>
                                            <Input
                                                id="birthdate"
                                                type="date"
                                                value={data.birthdate}
                                                onChange={(e) => setData('birthdate', e.target.value)}
                                                className={errors.birthdate ? 'border-red-500' : ''}
                                                required
                                            />
                                            {errors.birthdate && <p className="mt-1 text-sm text-red-500">{errors.birthdate}</p>}
                                        </div>

                                        <div>
                                            <Label htmlFor="gender">
                                                Gender <span className="text-red-500">*</span>
                                            </Label>
                                            <Select value={data.gender} onValueChange={(value) => setData('gender', value)}>
                                                <SelectTrigger className={errors.gender ? 'border-red-500' : ''}>
                                                    <SelectValue placeholder="Select gender" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="male">Male</SelectItem>
                                                    <SelectItem value="female">Female</SelectItem>
                                                </SelectContent>
                                            </Select>
                                            {errors.gender && <p className="mt-1 text-sm text-red-500">{errors.gender}</p>}
                                        </div>
                                    </div>
                                </div>

                                {/* Academic Information */}
                                <div className="space-y-4">
                                    <h3 className="text-lg font-semibold">Academic Information</h3>
                                    <div>
                                        <Label htmlFor="grade_level">
                                            Grade Level <span className="text-red-500">*</span>
                                        </Label>
                                        <Select value={data.grade_level} onValueChange={(value) => setData('grade_level', value)}>
                                            <SelectTrigger className={errors.grade_level ? 'border-red-500' : ''}>
                                                <SelectValue placeholder="Select grade level" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {gradeLevels.map((level) => (
                                                    <SelectItem key={level.value} value={level.value}>
                                                        {level.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.grade_level && <p className="mt-1 text-sm text-red-500">{errors.grade_level}</p>}
                                    </div>
                                </div>

                                {/* Contact Information */}
                                <div className="space-y-4">
                                    <h3 className="text-lg font-semibold">Contact Information</h3>
                                    <div>
                                        <Label htmlFor="address">
                                            Address <span className="text-red-500">*</span>
                                        </Label>
                                        <Input
                                            id="address"
                                            value={data.address}
                                            onChange={(e) => setData('address', e.target.value)}
                                            className={errors.address ? 'border-red-500' : ''}
                                            required
                                        />
                                        {errors.address && <p className="mt-1 text-sm text-red-500">{errors.address}</p>}
                                    </div>

                                    <div>
                                        <Label htmlFor="phone">Phone Number</Label>
                                        <Input
                                            id="phone"
                                            value={data.phone}
                                            onChange={(e) => setData('phone', e.target.value)}
                                            className={errors.phone ? 'border-red-500' : ''}
                                            placeholder="09XXXXXXXXX"
                                        />
                                        {errors.phone && <p className="mt-1 text-sm text-red-500">{errors.phone}</p>}
                                    </div>
                                </div>

                                {/* Guardian Relationship */}
                                <div className="space-y-4">
                                    <h3 className="text-lg font-semibold">Guardian Relationship</h3>
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <Label htmlFor="relationship_type">
                                                Relationship to Student <span className="text-red-500">*</span>
                                            </Label>
                                            <Select value={data.relationship_type} onValueChange={(value) => setData('relationship_type', value)}>
                                                <SelectTrigger className={errors.relationship_type ? 'border-red-500' : ''}>
                                                    <SelectValue placeholder="Select relationship" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {relationshipTypes.map((type) => (
                                                        <SelectItem key={type.value} value={type.value}>
                                                            {type.label}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            {errors.relationship_type && <p className="mt-1 text-sm text-red-500">{errors.relationship_type}</p>}
                                        </div>

                                        <div className="flex items-center space-x-2">
                                            <input
                                                type="checkbox"
                                                id="is_primary_contact"
                                                checked={data.is_primary_contact}
                                                onChange={(e) => setData('is_primary_contact', e.target.checked)}
                                                className="rounded border-gray-300"
                                            />
                                            <Label htmlFor="is_primary_contact">Primary Contact</Label>
                                        </div>
                                    </div>
                                </div>

                                {/* Student Account */}
                                <div className="space-y-4">
                                    <h3 className="text-lg font-semibold">Student Account</h3>
                                    <p className="text-sm text-muted-foreground">Create login credentials for the student to access the portal</p>

                                    <div>
                                        <Label htmlFor="email">
                                            Email Address <span className="text-red-500">*</span>
                                        </Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            className={errors.email ? 'border-red-500' : ''}
                                            required
                                        />
                                        {errors.email && <p className="mt-1 text-sm text-red-500">{errors.email}</p>}
                                    </div>

                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <Label htmlFor="password">
                                                Password <span className="text-red-500">*</span>
                                            </Label>
                                            <Input
                                                id="password"
                                                type="password"
                                                value={data.password}
                                                onChange={(e) => setData('password', e.target.value)}
                                                className={errors.password ? 'border-red-500' : ''}
                                                required
                                            />
                                            {errors.password && <p className="mt-1 text-sm text-red-500">{errors.password}</p>}
                                        </div>

                                        <div>
                                            <Label htmlFor="password_confirmation">
                                                Confirm Password <span className="text-red-500">*</span>
                                            </Label>
                                            <Input
                                                id="password_confirmation"
                                                type="password"
                                                value={data.password_confirmation}
                                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                                required
                                            />
                                        </div>
                                    </div>
                                </div>

                                {/* Submit Buttons */}
                                <div className="flex justify-end space-x-4">
                                    <Button type="button" variant="outline" asChild>
                                        <Link href="/guardian/dashboard">Cancel</Link>
                                    </Button>
                                    <Button type="submit" disabled={processing}>
                                        {processing ? 'Adding Student...' : 'Add Student'}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </PageLayout>
        </>
    );
}

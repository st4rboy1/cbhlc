import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import { toast } from 'sonner';

interface Guardian {
    id: number;
    first_name: string;
    middle_name?: string;
    last_name: string;
    email: string;
    phone: string;
    address: string;
    relationship: string;
    occupation?: string;
    employer?: string;
    emergency_contact: boolean;
}

interface FormData {
    first_name: string;
    middle_name: string;
    last_name: string;
    email: string;
    phone: string;
    address: string;
    relationship: string;
    occupation: string;
    employer: string;
    emergency_contact: boolean;
}

interface Props {
    guardian: Guardian;
}

export default function SuperAdminGuardiansEdit({ guardian }: Props) {
    const { data, setData, put, processing, errors } = useForm<FormData>({
        first_name: guardian.first_name,
        middle_name: guardian.middle_name || '',
        last_name: guardian.last_name,
        email: guardian.email,
        phone: guardian.phone,
        address: guardian.address,
        relationship: guardian.relationship,
        occupation: guardian.occupation || '',
        employer: guardian.employer || '',
        emergency_contact: guardian.emergency_contact,
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Registrar', href: '/registrar/dashboard' },
        { title: 'Guardians', href: '/registrar/guardians' },
        { title: 'Edit', href: '#' },
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/registrar/guardians/${guardian.id}`, {
            onSuccess: () => {
                toast.success('Guardian updated successfully');
            },
            onError: () => {
                toast.error('Failed to update guardian. Please check the form.');
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Guardian" />
            <div className="container mx-auto max-w-3xl px-4 py-6">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Edit Guardian</h1>
                    <Link href="/registrar/guardians">
                        <Button variant="outline">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back to List
                        </Button>
                    </Link>
                </div>

                <form onSubmit={handleSubmit}>
                    <div className="space-y-6">
                        {/* Personal Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Personal Information</CardTitle>
                                <CardDescription>
                                    Update the guardian's personal details for {guardian.first_name} {guardian.last_name}.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid gap-4 md:grid-cols-3">
                                    <div className="space-y-2">
                                        <Label htmlFor="first_name">
                                            First Name <span className="text-red-600">*</span>
                                        </Label>
                                        <Input
                                            id="first_name"
                                            type="text"
                                            value={data.first_name}
                                            onChange={(e) => setData('first_name', e.target.value)}
                                        />
                                        {errors.first_name && <p className="text-sm text-red-600">{errors.first_name}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="middle_name">Middle Name</Label>
                                        <Input
                                            id="middle_name"
                                            type="text"
                                            value={data.middle_name}
                                            onChange={(e) => setData('middle_name', e.target.value)}
                                        />
                                        {errors.middle_name && <p className="text-sm text-red-600">{errors.middle_name}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="last_name">
                                            Last Name <span className="text-red-600">*</span>
                                        </Label>
                                        <Input
                                            id="last_name"
                                            type="text"
                                            value={data.last_name}
                                            onChange={(e) => setData('last_name', e.target.value)}
                                        />
                                        {errors.last_name && <p className="text-sm text-red-600">{errors.last_name}</p>}
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="relationship">
                                        Relationship <span className="text-red-600">*</span>
                                    </Label>
                                    <Select value={data.relationship} onValueChange={(value) => setData('relationship', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select relationship" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="father">Father</SelectItem>
                                            <SelectItem value="mother">Mother</SelectItem>
                                            <SelectItem value="legal_guardian">Legal Guardian</SelectItem>
                                            <SelectItem value="grandparent">Grandparent</SelectItem>
                                            <SelectItem value="other">Other</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.relationship && <p className="text-sm text-red-600">{errors.relationship}</p>}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Contact Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Contact Information</CardTitle>
                                <CardDescription>Update the guardian's contact details.</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="email">
                                            Email <span className="text-red-600">*</span>
                                        </Label>
                                        <Input id="email" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} />
                                        {errors.email && <p className="text-sm text-red-600">{errors.email}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="phone">
                                            Phone <span className="text-red-600">*</span>
                                        </Label>
                                        <Input id="phone" type="tel" value={data.phone} onChange={(e) => setData('phone', e.target.value)} />
                                        {errors.phone && <p className="text-sm text-red-600">{errors.phone}</p>}
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="address">
                                        Address <span className="text-red-600">*</span>
                                    </Label>
                                    <textarea
                                        id="address"
                                        className="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                        value={data.address}
                                        onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => setData('address', e.target.value)}
                                        rows={3}
                                    />
                                    {errors.address && <p className="text-sm text-red-600">{errors.address}</p>}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Additional Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Additional Information</CardTitle>
                                <CardDescription>Update employment and emergency contact details (optional).</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="occupation">Occupation</Label>
                                        <Input
                                            id="occupation"
                                            type="text"
                                            value={data.occupation}
                                            onChange={(e) => setData('occupation', e.target.value)}
                                        />
                                        {errors.occupation && <p className="text-sm text-red-600">{errors.occupation}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="employer">Employer</Label>
                                        <Input
                                            id="employer"
                                            type="text"
                                            value={data.employer}
                                            onChange={(e) => setData('employer', e.target.value)}
                                        />
                                        {errors.employer && <p className="text-sm text-red-600">{errors.employer}</p>}
                                    </div>
                                </div>

                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="emergency_contact"
                                        checked={data.emergency_contact}
                                        onCheckedChange={(checked: boolean) => setData('emergency_contact', checked)}
                                    />
                                    <Label htmlFor="emergency_contact" className="cursor-pointer">
                                        Designated Emergency Contact
                                    </Label>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="mt-6 flex justify-end gap-4">
                        <Link href="/registrar/guardians">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                        <Button type="submit" disabled={processing}>
                            <Save className="mr-2 h-4 w-4" />
                            Update Guardian
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}

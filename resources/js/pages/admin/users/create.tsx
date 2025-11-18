import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';

interface Role {
    id: number;
    name: string;
}

interface Props {
    roles: Role[];
}

export default function UserCreate({ roles }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        { title: 'Users', href: '/admin/users' },
        { title: 'Create User', href: '/admin/users/create' },
    ];

    const { data, setData, post, processing, errors, wasSuccessful } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('admin.users.store'));
    };

    useEffect(() => {
        if (wasSuccessful) {
            toast.success('User created successfully.');
        }
    }, [wasSuccessful]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create User" />
            <div className="px-4 py-6">
                <h1 className="mb-4 text-2xl font-bold">Create New User</h1>
                <Card className="mx-auto max-w-2xl">
                    <CardHeader>
                        <CardTitle>User Information</CardTitle>
                        <CardDescription>Create a new user account for the system.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="space-y-4">
                                <div>
                                    <Label htmlFor="name">Full Name</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        className={errors.name ? 'border-red-500' : ''}
                                        placeholder="Enter full name"
                                    />
                                    {errors.name && <p className="mt-1 text-sm text-red-500">{errors.name}</p>}
                                </div>

                                <div>
                                    <Label htmlFor="email">Email Address</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        className={errors.email ? 'border-red-500' : ''}
                                        placeholder="user@example.com"
                                    />
                                    {errors.email && <p className="mt-1 text-sm text-red-500">{errors.email}</p>}
                                </div>

                                <div>
                                    <Label htmlFor="role">Role</Label>
                                    <Select value={data.role} onValueChange={(value) => setData('role', value)}>
                                        <SelectTrigger className={errors.role ? 'border-red-500' : ''}>
                                            <SelectValue placeholder="Select a role" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {roles.map((role) => (
                                                <SelectItem key={role.id} value={role.name}>
                                                    {role.name
                                                        .split('_')
                                                        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
                                                        .join(' ')}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.role && <p className="mt-1 text-sm text-red-500">{errors.role}</p>}
                                </div>

                                <div>
                                    <Label htmlFor="password">Password</Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        className={errors.password ? 'border-red-500' : ''}
                                        placeholder="Enter password"
                                    />
                                    {errors.password && <p className="mt-1 text-sm text-red-500">{errors.password}</p>}
                                </div>

                                <div>
                                    <Label htmlFor="password_confirmation">Confirm Password</Label>
                                    <Input
                                        id="password_confirmation"
                                        type="password"
                                        value={data.password_confirmation}
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                        className={errors.password_confirmation ? 'border-red-500' : ''}
                                        placeholder="Confirm password"
                                    />
                                    {errors.password_confirmation && <p className="mt-1 text-sm text-red-500">{errors.password_confirmation}</p>}
                                </div>
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    Create User
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

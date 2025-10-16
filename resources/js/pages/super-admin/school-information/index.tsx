import { Head, router } from '@inertiajs/react';
import { Save } from 'lucide-react';
import { useState } from 'react';

import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/hooks/use-toast';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

interface SchoolInformation {
    id: number;
    key: string;
    value: string | null;
    type: string;
    group: string;
    label: string;
    description: string | null;
    order: number;
}

interface GroupedInformation {
    contact?: SchoolInformation[];
    hours?: SchoolInformation[];
    social?: SchoolInformation[];
    about?: SchoolInformation[];
}

interface Props {
    information: GroupedInformation;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Super Admin', href: '/super-admin/dashboard' },
    { title: 'School Information', href: '/super-admin/school-information' },
];

const groupTitles: Record<string, { title: string; description: string }> = {
    contact: {
        title: 'Contact Information',
        description: 'Manage school contact details and address',
    },
    hours: {
        title: 'Office Hours',
        description: 'Set office hours for different days of the week',
    },
    social: {
        title: 'Social Media',
        description: 'Update social media links and profiles',
    },
    about: {
        title: 'About School',
        description: 'School tagline and description',
    },
};

export default function SchoolInformationIndex({ information }: Props) {
    const { toast } = useToast();
    const [formData, setFormData] = useState<Record<number, string>>(() => {
        const initial: Record<number, string> = {};
        Object.values(information).forEach((items) => {
            items?.forEach((item) => {
                initial[item.id] = item.value || '';
            });
        });
        return initial;
    });
    const [saving, setSaving] = useState(false);

    const handleChange = (id: number, value: string) => {
        setFormData((prev) => ({ ...prev, [id]: value }));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setSaving(true);

        const updates = Object.entries(formData).map(([id, value]) => ({
            id: parseInt(id),
            value,
        }));

        router.put(
            '/super-admin/school-information',
            { updates },
            {
                onSuccess: () => {
                    toast({
                        title: 'Success',
                        description: 'School information updated successfully',
                    });
                },
                onError: (errors) => {
                    toast({
                        title: 'Error',
                        description: errors.updates?.[0] || 'Failed to update school information',
                        variant: 'destructive',
                    });
                },
                onFinish: () => setSaving(false),
            },
        );
    };

    const renderField = (item: SchoolInformation) => {
        const value = formData[item.id] || '';

        if (item.type === 'text' && item.key.includes('description')) {
            return (
                <Textarea
                    id={item.key}
                    value={value}
                    onChange={(e) => handleChange(item.id, e.target.value)}
                    rows={3}
                    placeholder={item.description || ''}
                />
            );
        }

        return (
            <Input
                id={item.key}
                type={item.type === 'email' ? 'email' : item.type === 'url' ? 'url' : 'text'}
                value={value}
                onChange={(e) => handleChange(item.id, e.target.value)}
                placeholder={item.description || ''}
            />
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="School Information" />

            <div className="space-y-6">
                <Heading title="School Information" description="Manage school contact details, office hours, and social media links" />

                <form onSubmit={handleSubmit} className="space-y-6">
                    {Object.entries(information).map(([group, items]) => {
                        if (!items || items.length === 0) return null;

                        return (
                            <Card key={group}>
                                <CardHeader>
                                    <CardTitle>{groupTitles[group]?.title || group}</CardTitle>
                                    <CardDescription>{groupTitles[group]?.description || ''}</CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {items.map((item, index) => (
                                        <div key={item.id}>
                                            {index > 0 && <Separator className="my-4" />}
                                            <div className="space-y-2">
                                                <Label htmlFor={item.key}>{item.label}</Label>
                                                {renderField(item)}
                                                {item.description && <p className="text-sm text-muted-foreground">{item.description}</p>}
                                            </div>
                                        </div>
                                    ))}
                                </CardContent>
                            </Card>
                        );
                    })}

                    <div className="flex justify-end">
                        <Button type="submit" disabled={saving}>
                            <Save className="mr-2 h-4 w-4" />
                            {saving ? 'Saving...' : 'Save Changes'}
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}

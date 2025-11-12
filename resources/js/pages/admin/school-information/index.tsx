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

interface FormField {
    key: string;
    label: string;
    type: 'text' | 'email' | 'phone' | 'url' | 'textarea';
    placeholder?: string;
}

interface FormSection {
    title: string;
    description: string;
    fields: FormField[];
}

interface Props {
    values: Record<string, string>;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Administrator', href: '/admin/dashboard' },
    { title: 'School Information', href: '/admin/school-information' },
];

const formSections: FormSection[] = [
    {
        title: 'Contact Information',
        description: 'Manage school contact details and address',
        fields: [
            { key: 'school_name', label: 'School Name', type: 'text', placeholder: 'Enter school name' },
            { key: 'school_email', label: 'Email Address', type: 'email', placeholder: 'Enter email address' },
            { key: 'school_phone', label: 'Phone Number', type: 'phone', placeholder: 'Enter phone number' },
            { key: 'school_mobile', label: 'Mobile Number', type: 'phone', placeholder: 'Enter mobile number' },
            { key: 'school_address', label: 'School Address', type: 'text', placeholder: 'Enter school address' },
        ],
    },
    {
        title: 'Office Hours',
        description: 'Set office hours for different days of the week',
        fields: [
            { key: 'office_hours_weekday', label: 'Weekday Hours', type: 'text', placeholder: 'e.g., Monday to Friday: 8:00 AM - 5:00 PM' },
            { key: 'office_hours_saturday', label: 'Saturday Hours', type: 'text', placeholder: 'e.g., Saturday: 8:00 AM - 12:00 PM' },
            { key: 'office_hours_sunday', label: 'Sunday Hours', type: 'text', placeholder: 'e.g., Sunday: Closed' },
        ],
    },
    {
        title: 'Social Media',
        description: 'Update social media links and profiles',
        fields: [
            { key: 'facebook_url', label: 'Facebook URL', type: 'url', placeholder: 'https://facebook.com/...' },
            { key: 'instagram_url', label: 'Instagram URL', type: 'url', placeholder: 'https://instagram.com/...' },
            { key: 'youtube_url', label: 'YouTube URL', type: 'url', placeholder: 'https://youtube.com/...' },
        ],
    },
    {
        title: 'About School',
        description: 'School tagline and description',
        fields: [
            { key: 'school_tagline', label: 'School Tagline', type: 'text', placeholder: 'Enter school tagline or motto' },
            { key: 'school_description', label: 'School Description', type: 'textarea', placeholder: 'Enter brief description of the school' },
        ],
    },
];

export default function SchoolInformationIndex({ values }: Props) {
    const { toast } = useToast();
    const [formData, setFormData] = useState<Record<string, string>>(values || {});
    const [saving, setSaving] = useState(false);

    const handleChange = (key: string, value: string) => {
        setFormData((prev) => ({ ...prev, [key]: value }));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setSaving(true);

        router.put('/admin/school-information', formData, {
            onSuccess: () => {
                toast({
                    title: 'Success',
                    description: 'School information updated successfully',
                });
            },
            onError: (errors) => {
                toast({
                    title: 'Error',
                    description: (Object.values(errors)[0] as string) || 'Failed to update school information',
                    variant: 'destructive',
                });
            },
            onFinish: () => setSaving(false),
        });
    };

    const renderField = (field: FormField) => {
        const value = formData[field.key] || '';

        if (field.type === 'textarea') {
            return (
                <Textarea
                    id={field.key}
                    value={value}
                    onChange={(e) => handleChange(field.key, e.target.value)}
                    rows={3}
                    placeholder={field.placeholder}
                />
            );
        }

        return (
            <Input
                id={field.key}
                type={field.type === 'email' ? 'email' : field.type === 'url' ? 'url' : 'text'}
                value={value}
                onChange={(e) => handleChange(field.key, e.target.value)}
                placeholder={field.placeholder}
            />
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="School Information" />

            <div className="space-y-6">
                <Heading title="School Information" description="Manage school contact details, office hours, and social media links" />

                <form onSubmit={handleSubmit} className="space-y-6">
                    {formSections.map((section) => (
                        <Card key={section.title}>
                            <CardHeader>
                                <CardTitle>{section.title}</CardTitle>
                                <CardDescription>{section.description}</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {section.fields.map((field, index) => (
                                    <div key={field.key}>
                                        {index > 0 && <Separator className="my-4" />}
                                        <div className="space-y-2">
                                            <Label htmlFor={field.key}>{field.label}</Label>
                                            {renderField(field)}
                                        </div>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                    ))}

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

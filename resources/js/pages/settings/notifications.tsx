import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import { useToast } from '@/hooks/use-toast';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { reset, update } from '@/routes/settings/notifications';
import { type BreadcrumbItem } from '@/types';

interface NotificationPreference {
    id?: number;
    notification_type: string;
    email_enabled: boolean;
    database_enabled: boolean;
}

interface Props {
    preferences: Record<string, NotificationPreference>;
    availableTypes: Record<string, string>;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Notification settings',
        href: '/settings/notifications',
    },
];

// Group notification types by category
const notificationGroups = {
    enrollment: {
        title: 'Enrollment Notifications',
        description: 'Notifications about enrollment applications and status changes',
        types: ['enrollment_approved', 'enrollment_rejected', 'enrollment_pending', 'enrollment_period_changed'],
    },
    documents: {
        title: 'Document Notifications',
        description: 'Notifications about document uploads and verification',
        types: ['document_verified', 'document_rejected'],
    },
    billing: {
        title: 'Billing Notifications',
        description: 'Notifications about payments and invoices',
        types: ['payment_due', 'payment_received', 'payment_overdue'],
    },
    system: {
        title: 'System Notifications',
        description: 'General announcements and updates',
        types: ['announcement_published', 'inquiry_response'],
    },
};

export default function Notifications({ preferences, availableTypes }: Props) {
    const { toast } = useToast();
    const [localPreferences, setLocalPreferences] = useState<Record<string, NotificationPreference>>(preferences);
    const [saving, setSaving] = useState(false);

    const updatePreference = (type: string, channel: 'email' | 'database', value: boolean) => {
        setLocalPreferences((prev) => ({
            ...prev,
            [type]: {
                ...(prev[type] || {
                    notification_type: type,
                    email_enabled: true,
                    database_enabled: true,
                }),
                [`${channel}_enabled`]: value,
            },
        }));
    };

    const handleSave = () => {
        setSaving(true);

        router.put(
            update.url(),
            {
                preferences: localPreferences,
                // eslint-disable-next-line @typescript-eslint/no-explicit-any
            } as any,
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast({
                        title: 'Success',
                        description: 'Notification preferences updated successfully',
                    });
                },
                onError: () => {
                    toast({
                        title: 'Error',
                        description: 'Failed to update notification preferences',
                        variant: 'destructive',
                    });
                },
                onFinish: () => setSaving(false),
            },
        );
    };

    const handleReset = () => {
        if (!confirm('Are you sure you want to reset all notification preferences to default?')) {
            return;
        }

        router.post(
            reset.url(),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast({
                        title: 'Success',
                        description: 'Notification preferences reset to default',
                    });
                },
                onError: () => {
                    toast({
                        title: 'Error',
                        description: 'Failed to reset notification preferences',
                        variant: 'destructive',
                    });
                },
            },
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Notification settings" />

            <SettingsLayout>
                <div className="space-y-6">
                    <div>
                        <HeadingSmall
                            title="Notification settings"
                            description="Choose which notifications you want to receive and how you want to receive them"
                        />
                    </div>

                    <Separator />

                    <div className="space-y-6">
                        {Object.entries(notificationGroups).map(([groupKey, group]) => {
                            // Filter types that exist in availableTypes
                            const groupTypes = group.types.filter((type) => availableTypes[type]);

                            if (groupTypes.length === 0) {
                                return null;
                            }

                            return (
                                <Card key={groupKey}>
                                    <CardHeader>
                                        <CardTitle>{group.title}</CardTitle>
                                        <CardDescription>{group.description}</CardDescription>
                                    </CardHeader>
                                    <CardContent className="space-y-6">
                                        {groupTypes.map((type) => {
                                            const preference = localPreferences[type] || {
                                                notification_type: type,
                                                email_enabled: true,
                                                database_enabled: true,
                                            };

                                            return (
                                                <div key={type} className="flex items-start justify-between gap-4">
                                                    <div className="flex-1 space-y-1">
                                                        <Label className="text-base">{availableTypes[type]}</Label>
                                                    </div>

                                                    <div className="flex gap-6">
                                                        <div className="flex items-center space-x-2">
                                                            <Switch
                                                                id={`${type}-email`}
                                                                checked={preference.email_enabled}
                                                                onCheckedChange={(checked) => updatePreference(type, 'email', checked)}
                                                            />
                                                            <Label htmlFor={`${type}-email`} className="text-sm font-normal text-muted-foreground">
                                                                Email
                                                            </Label>
                                                        </div>

                                                        <div className="flex items-center space-x-2">
                                                            <Switch
                                                                id={`${type}-database`}
                                                                checked={preference.database_enabled}
                                                                onCheckedChange={(checked) => updatePreference(type, 'database', checked)}
                                                            />
                                                            <Label htmlFor={`${type}-database`} className="text-sm font-normal text-muted-foreground">
                                                                In-App
                                                            </Label>
                                                        </div>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </CardContent>
                                </Card>
                            );
                        })}
                    </div>

                    <div className="flex items-center justify-between pt-4">
                        <Button variant="outline" onClick={handleReset} disabled={saving}>
                            Reset to Defaults
                        </Button>

                        <Button onClick={handleSave} disabled={saving}>
                            {saving ? 'Saving...' : 'Save Changes'}
                        </Button>
                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}

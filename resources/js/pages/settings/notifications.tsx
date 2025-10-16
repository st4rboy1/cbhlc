import { Head } from '@inertiajs/react';

import HeadingSmall from '@/components/heading-small';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';

interface NotificationPreference {
    id: number;
    notification_type: string;
    email_enabled: boolean;
    database_enabled: boolean;
}

interface Props {
    preferences: Record<string, NotificationPreference>;
    availableTypes: Record<string, { label: string; description: string }>;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Notification settings',
        href: '/settings/notifications',
    },
];

export default function Notifications({ preferences, availableTypes }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Notification settings" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Notification settings" description="Manage your notification preferences" />
                    <div className="rounded bg-yellow-50 p-4 text-yellow-800">TODO: UI implementation pending</div>
                    <pre className="mt-4 overflow-auto rounded bg-gray-100 p-4">{JSON.stringify({ preferences, availableTypes }, null, 2)}</pre>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}

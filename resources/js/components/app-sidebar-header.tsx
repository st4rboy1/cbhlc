import { Breadcrumbs } from '@/components/breadcrumbs';
import NotificationsDropdown from '@/components/notifications-dropdown';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { type BreadcrumbItem as BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

interface Notification {
    id: string;
    type: string;
    data: Record<string, string | number | boolean | null>;
    read_at: string | null;
    created_at: string;
}

interface PageProps {
    auth?: {
        user?: {
            id: number;
            name: string;
            email: string;
        };
    };
}

export function AppSidebarHeader({ breadcrumbs = [] }: { breadcrumbs?: BreadcrumbItemType[] }) {
    const { auth } = usePage<PageProps>().props;
    const [notifications, setNotifications] = useState<Notification[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        // Fetch recent notifications (limit 10 for dropdown)
        fetch('/api/notifications?limit=10')
            .then((res) => res.json())
            .then((data) => {
                setNotifications(data.data || []);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    }, []);

    return (
        <header className="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/50 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
            <div className="flex flex-1 items-center gap-2">
                <SidebarTrigger className="-ml-1" />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>
            {auth?.user && !loading && <NotificationsDropdown notifications={notifications} />}
        </header>
    );
}

import { NotificationItem } from '@/components/notifications/notification-item';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type Page, type PageProps } from '@inertiajs/core';
import { Head, router } from '@inertiajs/react';
import { Bell } from 'lucide-react';
import { useState } from 'react';

interface Notification {
    id: string;
    type: string;
    data: {
        message: string;
        [key: string]: unknown;
    };
    read_at: string | null;
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface NotificationsData {
    data: Notification[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
}

interface Props extends PageProps {
    notifications: NotificationsData;
    filter: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Notifications',
        href: '/notifications',
    },
];

export default function Index({ notifications: initialNotifications, filter: initialFilter }: Props) {
    const [filter, setFilter] = useState(initialFilter);
    const [notifications, setNotifications] = useState(initialNotifications);

    const handleFilterChange = (newFilter: string) => {
        setFilter(newFilter);
        router.get(
            '/notifications',
            { filter: newFilter },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: (page) => {
                    setNotifications((page as Page<Props>).props.notifications);
                },
            },
        );
    };

    const handleRead = async (id: string) => {
        try {
            await fetch(`/api/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            // Update local state
            setNotifications((prev) => ({
                ...prev,
                data: prev.data.map((notif) => (notif.id === id ? { ...notif, read_at: new Date().toISOString() } : notif)),
            }));
        } catch (error) {
            console.error('Failed to mark as read:', error);
        }
    };

    const handleDelete = async (id: string) => {
        try {
            await fetch(`/api/notifications/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            // Remove from local state
            setNotifications((prev) => ({
                ...prev,
                data: prev.data.filter((notif) => notif.id !== id),
                total: prev.total - 1,
            }));
        } catch (error) {
            console.error('Failed to delete notification:', error);
        }
    };

    const markAllAsRead = async () => {
        try {
            await fetch('/api/notifications/read-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            // Update local state
            setNotifications((prev) => ({
                ...prev,
                data: prev.data.map((notif) => ({ ...notif, read_at: new Date().toISOString() })),
            }));
        } catch (error) {
            console.error('Failed to mark all as read:', error);
        }
    };

    const clearAll = async () => {
        if (!confirm('Are you sure you want to delete all notifications? This cannot be undone.')) {
            return;
        }

        try {
            await fetch('/api/notifications', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            // Clear local state
            setNotifications({
                ...notifications,
                data: [],
                total: 0,
            });
        } catch (error) {
            console.error('Failed to clear all notifications:', error);
        }
    };

    const handlePageChange = (url: string | null) => {
        if (!url) return;

        router.get(
            url,
            {},
            {
                preserveState: true,
                preserveScroll: false,
                onSuccess: (page) => {
                    setNotifications((page as Page<Props>).props.notifications);
                },
            },
        );
    };

    const unreadCount = notifications.data.filter((n) => !n.read_at).length;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Notifications" />

            <div className="mx-auto max-w-4xl space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold">Notifications</h1>

                    <div className="flex gap-2">
                        {unreadCount > 0 && (
                            <Button variant="outline" onClick={markAllAsRead}>
                                Mark all as read
                            </Button>
                        )}
                        {notifications.data.length > 0 && (
                            <Button variant="outline" onClick={clearAll}>
                                Clear all
                            </Button>
                        )}
                    </div>
                </div>

                <Tabs value={filter} onValueChange={handleFilterChange}>
                    <TabsList>
                        <TabsTrigger value="all">All</TabsTrigger>
                        <TabsTrigger value="unread">Unread {unreadCount > 0 && <span className="ml-1.5 text-xs">({unreadCount})</span>}</TabsTrigger>
                        <TabsTrigger value="read">Read</TabsTrigger>
                    </TabsList>

                    <TabsContent value={filter} className="mt-6">
                        {notifications.data.length === 0 ? (
                            <div className="flex flex-col items-center justify-center rounded-lg border border-dashed p-12 text-center">
                                <Bell className="mb-4 h-12 w-12 text-muted-foreground" />
                                <h3 className="mb-2 text-lg font-semibold">No notifications</h3>
                                <p className="text-sm text-muted-foreground">
                                    {filter === 'unread' ? "You're all caught up!" : 'You have no notifications yet.'}
                                </p>
                            </div>
                        ) : (
                            <>
                                <div className="space-y-2">
                                    {notifications.data.map((notification) => (
                                        <Card key={notification.id}>
                                            <CardContent className="p-0">
                                                <NotificationItem
                                                    notification={notification}
                                                    onRead={() => handleRead(notification.id)}
                                                    onDelete={() => handleDelete(notification.id)}
                                                />
                                            </CardContent>
                                        </Card>
                                    ))}
                                </div>

                                {/* Pagination */}
                                {notifications.last_page > 1 && (
                                    <div className="mt-6 flex items-center justify-center gap-2">
                                        {notifications.links.map((link, index) => (
                                            <Button
                                                key={index}
                                                variant={link.active ? 'default' : 'outline'}
                                                size="sm"
                                                onClick={() => handlePageChange(link.url)}
                                                disabled={!link.url}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                                className="min-w-[2.5rem]"
                                            />
                                        ))}
                                    </div>
                                )}

                                <div className="mt-4 text-center text-sm text-muted-foreground">
                                    Showing {notifications.data.length} of {notifications.total} notifications
                                </div>
                            </>
                        )}
                    </TabsContent>
                </Tabs>
            </div>
        </AppLayout>
    );
}

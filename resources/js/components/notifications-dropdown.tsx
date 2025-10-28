import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { ScrollArea } from '@/components/ui/scroll-area';
import { router } from '@inertiajs/react';
import { Bell, Check, CheckCheck } from 'lucide-react';
import { useState } from 'react';

interface Notification {
    id: string;
    type: string;
    data: {
        message: string;
        student_name?: string;
        student_id?: number;
        grade_level?: string;
        school_year?: string;
        application_id?: string;
        enrollment_id?: number;
        document_id?: number;
        document_type?: string;
        status?: string;
        reason?: string;
        remarks?: string;
    };
    read_at: string | null;
    created_at: string;
}

interface Props {
    notifications: Notification[];
}

export default function NotificationsDropdown({ notifications }: Props) {
    const [unreadCount, setUnreadCount] = useState(notifications.filter((n) => !n.read_at).length);

    const handleMarkAsRead = (notificationId: string, callback?: () => void) => {
        router.post(
            `/notifications/${notificationId}/mark-as-read`,
            {},
            {
                preserveScroll: false, // Allow navigation
                onSuccess: () => {
                    setUnreadCount((prev) => Math.max(0, prev - 1));
                    callback?.();
                },
            },
        );
    };

    const handleMarkAllAsRead = () => {
        router.post(
            '/notifications/mark-all-as-read',
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    setUnreadCount(0);
                },
            },
        );
    };

    const handleNotificationClick = (notification: Notification) => {
        // Backend handles navigation via Inertia::location() after marking as read
        handleMarkAsRead(notification.id);
    };

    const getNotificationTitle = (notification: Notification): string => {
        if (notification.type.includes('EnrollmentSubmitted')) {
            return 'Enrollment Submitted';
        }
        if (notification.type.includes('EnrollmentApproved')) {
            return 'Enrollment Approved';
        }
        if (notification.type.includes('EnrollmentRejected')) {
            return 'Enrollment Status Update';
        }
        if (notification.type.includes('NewEnrollmentForReview')) {
            return 'New Enrollment to Review';
        }
        if (notification.type.includes('DocumentVerified')) {
            return 'Document Verified';
        }
        if (notification.type.includes('DocumentRejected')) {
            return 'Document Rejected';
        }
        // Use the message from notification data if available
        if (notification.data.message) {
            return notification.data.message;
        }
        return 'Notification';
    };

    const formatDate = (dateString: string) => {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now.getTime() - date.getTime();
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (minutes < 1) return 'Just now';
        if (minutes < 60) return `${minutes}m ago`;
        if (hours < 24) return `${hours}h ago`;
        if (days < 7) return `${days}d ago`;
        return date.toLocaleDateString();
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="icon" className="relative">
                    <Bell className="h-5 w-5" />
                    {unreadCount > 0 && (
                        <span className="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">
                            {unreadCount > 9 ? '9+' : unreadCount}
                        </span>
                    )}
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-80">
                <DropdownMenuLabel className="flex items-center justify-between">
                    <span>Notifications</span>
                    {unreadCount > 0 && (
                        <Button variant="ghost" size="sm" onClick={handleMarkAllAsRead} className="h-auto p-1 text-xs">
                            <CheckCheck className="mr-1 h-3 w-3" />
                            Mark all read
                        </Button>
                    )}
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                <ScrollArea className="h-[400px]">
                    {notifications.length === 0 ? (
                        <div className="flex flex-col items-center justify-center py-8 text-center">
                            <Bell className="mb-2 h-8 w-8 text-muted-foreground" />
                            <p className="text-sm text-muted-foreground">No notifications</p>
                        </div>
                    ) : (
                        notifications.map((notification) => (
                            <DropdownMenuItem
                                key={notification.id}
                                className={`flex cursor-pointer flex-col items-start gap-1 p-3 ${!notification.read_at ? 'bg-blue-50' : ''}`}
                                onClick={() => handleNotificationClick(notification)}
                            >
                                <div className="flex w-full items-start justify-between gap-2">
                                    <div className="flex-1">
                                        <p className="text-sm font-semibold">{getNotificationTitle(notification)}</p>
                                        <p className="text-xs text-muted-foreground">{notification.data.message}</p>
                                    </div>
                                    {!notification.read_at && <div className="h-2 w-2 rounded-full bg-blue-600" />}
                                </div>
                                <div className="flex w-full items-center justify-between">
                                    <span className="text-xs text-muted-foreground">{formatDate(notification.created_at)}</span>
                                    {!notification.read_at && (
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            onClick={(e) => {
                                                e.stopPropagation();
                                                handleMarkAsRead(notification.id);
                                            }}
                                            className="h-auto p-1"
                                        >
                                            <Check className="h-3 w-3" />
                                        </Button>
                                    )}
                                </div>
                            </DropdownMenuItem>
                        ))
                    )}
                </ScrollArea>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { formatDistanceToNow } from 'date-fns';
import { AlertCircle, Bell, CheckCircle, FileText, Info, XCircle } from 'lucide-react';

interface NotificationItemProps {
    notification: {
        id: string;
        type: string;
        data: {
            message: string;
            [key: string]: unknown;
        };
        read_at: string | null;
        created_at: string;
    };
    onRead?: () => void;
    onDelete?: () => void;
    showActions?: boolean;
    compact?: boolean;
}

function getNotificationIcon(type: string) {
    // Map notification types to icons
    const typeMap: Record<string, React.ElementType> = {
        'App\\Notifications\\EnrollmentStatusNotification': FileText,
        'App\\Notifications\\DocumentVerificationNotification': FileText,
        'App\\Notifications\\DocumentRejectionNotification': XCircle,
        'App\\Notifications\\EnrollmentPeriodStatusNotification': Bell,
        success: CheckCircle,
        error: XCircle,
        warning: AlertCircle,
        info: Info,
    };

    const IconComponent = typeMap[type] || Bell;

    return <IconComponent className="h-5 w-5" />;
}

function getIconColor(type: string) {
    // Map notification types to colors
    if (type.includes('Rejection') || type.includes('error')) {
        return 'text-red-500';
    }
    if (type.includes('Verification') || type.includes('success')) {
        return 'text-green-500';
    }
    if (type.includes('warning')) {
        return 'text-yellow-500';
    }
    return 'text-blue-500';
}

export function NotificationItem({ notification, onRead, onDelete, showActions = true, compact = false }: NotificationItemProps) {
    const isUnread = !notification.read_at;

    return (
        <div className={cn('transition-colors hover:bg-accent', compact ? 'p-3' : 'p-4', isUnread && 'bg-blue-50/50 dark:bg-blue-950/20')}>
            <div className="flex gap-3">
                <div className={cn('mt-0.5 flex-shrink-0', getIconColor(notification.type))}>{getNotificationIcon(notification.type)}</div>

                <div className="min-w-0 flex-1 space-y-1">
                    <p className={cn('text-sm', isUnread && 'font-semibold')}>{notification.data.message}</p>

                    <p className="text-xs text-muted-foreground">{formatDistanceToNow(new Date(notification.created_at), { addSuffix: true })}</p>

                    {showActions && !compact && (
                        <div className="mt-2 flex gap-2">
                            {isUnread && onRead && (
                                <Button size="sm" variant="ghost" onClick={onRead} className="h-7 px-2 text-xs">
                                    Mark as read
                                </Button>
                            )}
                            {onDelete && (
                                <Button size="sm" variant="ghost" onClick={onDelete} className="h-7 px-2 text-xs text-destructive">
                                    Delete
                                </Button>
                            )}
                        </div>
                    )}
                </div>

                {isUnread && <div className="mt-1.5 h-2 w-2 flex-shrink-0 rounded-full bg-blue-500" />}
            </div>
        </div>
    );
}

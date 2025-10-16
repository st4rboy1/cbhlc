# Ticket #013: Notification Center UI

**Epic:** [EPIC-006 Notification System Enhancement](./EPIC-006-notification-system-enhancement.md)

**Type:** Story
**Priority:** High
**Estimated Effort:** 1.5 days
**Status:** ✅ COMPLETED
**PR:** [#108](https://github.com/st4rboy1/cbhlc/pull/108)
**Completed:** 2025-10-16

## Description

Build notification center UI with bell icon, dropdown, and full notification page for users to view and manage their notifications.

## Acceptance Criteria

- [x] Notification bell icon in header with unread badge
- [x] Dropdown showing recent 5 notifications
- [x] Full notifications page with pagination
- [x] Mark as read functionality
- [x] Mark all as read button
- [x] Delete notification functionality
- [x] Clear all notifications button
- [x] Filter by read/unread
- [x] Real-time unread count updates
- [x] Responsive design

## Implementation Details

### Backend Routes

```php
Route::middleware('auth')->prefix('api/notifications')->name('api.notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    Route::delete('/', [NotificationController::class, 'destroyAll'])->name('destroy-all');
});

// Full notifications page
Route::get('/notifications', [NotificationController::class, 'page'])->name('notifications');
```

### Controller Methods

```php
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = auth()->user()->notifications();

        if ($request->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($request->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->latest()->paginate(20);

        return response()->json($notifications);
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => auth()->user()->unreadNotifications()->count()
        ]);
    }

    public function markAsRead(string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function destroy(string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json(['success' => true]);
    }

    public function destroyAll()
    {
        auth()->user()->notifications()->delete();

        return response()->json(['success' => true]);
    }

    public function page()
    {
        $notifications = auth()->user()->notifications()
            ->latest()
            ->paginate(20);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }
}
```

### Components

**NotificationBell**
`resources/js/components/notifications/notification-bell.tsx`

```tsx
export function NotificationBell() {
    const [unreadCount, setUnreadCount] = useState(0);
    const [isOpen, setIsOpen] = useState(false);
    const [notifications, setNotifications] = useState([]);

    useEffect(() => {
        fetchUnreadCount();
        const interval = setInterval(fetchUnreadCount, 30000); // Refresh every 30s
        return () => clearInterval(interval);
    }, []);

    const fetchUnreadCount = async () => {
        const response = await fetch('/api/notifications/unread-count');
        const data = await response.json();
        setUnreadCount(data.count);
    };

    const fetchNotifications = async () => {
        const response = await fetch('/api/notifications?limit=5');
        const data = await response.json();
        setNotifications(data.data);
    };

    const handleOpen = () => {
        setIsOpen(true);
        fetchNotifications();
    };

    return (
        <DropdownMenu open={isOpen} onOpenChange={setIsOpen}>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="icon" className="relative" onClick={handleOpen}>
                    <BellIcon className="h-5 w-5" />
                    {unreadCount > 0 && (
                        <Badge className="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center p-0">
                            {unreadCount > 9 ? '9+' : unreadCount}
                        </Badge>
                    )}
                </Button>
            </DropdownMenuTrigger>

            <DropdownMenuContent align="end" className="w-80">
                <DropdownMenuLabel className="flex items-center justify-between">
                    <span>Notifications</span>
                    {unreadCount > 0 && (
                        <Button variant="ghost" size="sm" onClick={markAllAsRead}>
                            Mark all read
                        </Button>
                    )}
                </DropdownMenuLabel>
                <DropdownMenuSeparator />

                {notifications.length === 0 ? (
                    <div className="p-4 text-center text-muted-foreground">No notifications</div>
                ) : (
                    <>
                        {notifications.map((notification) => (
                            <NotificationItem
                                key={notification.id}
                                notification={notification}
                                onRead={() => handleRead(notification.id)}
                                onDelete={() => handleDelete(notification.id)}
                            />
                        ))}

                        <DropdownMenuSeparator />
                        <DropdownMenuItem asChild>
                            <Link href="/notifications" className="text-center">
                                View all notifications
                            </Link>
                        </DropdownMenuItem>
                    </>
                )}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
```

**NotificationItem**
`resources/js/components/notifications/notification-item.tsx`

```tsx
interface NotificationItemProps {
    notification: Notification;
    onRead?: () => void;
    onDelete?: () => void;
    showActions?: boolean;
}

export function NotificationItem({ notification, onRead, onDelete, showActions = true }: NotificationItemProps) {
    const isUnread = !notification.read_at;

    return (
        <div className={cn('p-3 transition-colors hover:bg-accent', isUnread && 'bg-blue-50 dark:bg-blue-950')}>
            <div className="flex gap-3">
                <NotificationIcon type={notification.type} />

                <div className="flex-1 space-y-1">
                    <p className={cn('text-sm', isUnread && 'font-semibold')}>{notification.data.message}</p>

                    <p className="text-xs text-muted-foreground">{formatDistanceToNow(new Date(notification.created_at), { addSuffix: true })}</p>

                    {showActions && (
                        <div className="mt-2 flex gap-2">
                            {isUnread && onRead && (
                                <Button size="sm" variant="ghost" onClick={onRead}>
                                    Mark as read
                                </Button>
                            )}
                            {onDelete && (
                                <Button size="sm" variant="ghost" onClick={onDelete}>
                                    Delete
                                </Button>
                            )}
                        </div>
                    )}
                </div>

                {isUnread && <div className="mt-1 h-2 w-2 rounded-full bg-blue-500" />}
            </div>
        </div>
    );
}
```

**Notifications Page**
`resources/js/pages/notifications/index.tsx`

```tsx
export default function NotificationsPage({ notifications: initialNotifications }) {
    const [filter, setFilter] = useState('all');
    const [notifications, setNotifications] = useState(initialNotifications);

    const handleFilterChange = (newFilter: string) => {
        setFilter(newFilter);
        router.get(
            '/notifications',
            { filter: newFilter },
            {
                preserveState: true,
                onSuccess: (page) => {
                    setNotifications(page.props.notifications);
                },
            },
        );
    };

    return (
        <AppLayout>
            <div className="mx-auto max-w-4xl p-6">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-3xl font-bold">Notifications</h1>

                    <div className="flex gap-2">
                        <Button variant="outline" onClick={markAllAsRead}>
                            Mark all as read
                        </Button>
                        <Button variant="outline" onClick={clearAll}>
                            Clear all
                        </Button>
                    </div>
                </div>

                <Tabs value={filter} onValueChange={handleFilterChange}>
                    <TabsList>
                        <TabsTrigger value="all">All</TabsTrigger>
                        <TabsTrigger value="unread">Unread</TabsTrigger>
                        <TabsTrigger value="read">Read</TabsTrigger>
                    </TabsList>

                    <TabsContent value={filter} className="mt-6">
                        {notifications.data.length === 0 ? (
                            <EmptyState icon={BellIcon} title="No notifications" description="You're all caught up!" />
                        ) : (
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

                                <Pagination links={notifications.links} />
                            </div>
                        )}
                    </TabsContent>
                </Tabs>
            </div>
        </AppLayout>
    );
}
```

### Integration into AppLayout

Add to header:

```tsx
<div className="flex items-center gap-4">
    <NotificationBell />
    <UserMenu />
</div>
```

## Testing Requirements

- [x] Backend test: NotificationController endpoints
- [x] Backend test: Mark as read functionality
- [x] Backend test: Mark all as read
- [x] Backend test: Delete notification
- [x] Backend test: Delete all notifications
- [x] Backend test: Filter by read/unread
- [x] Backend test: Pagination
- [x] Backend test: Authorization (user can only access own notifications)
- [x] Integration: Full notifications page route
- [x] Integration: Real-time unread count updates

**Testing Coverage:**
- 15 comprehensive tests
- 91 assertions
- All endpoints tested with success and error cases
- Authorization checks verified
- Guest access prevented

## Dependencies

- [TICKET-012](./TICKET-012-notification-preferences-backend.md) - Backend API
- shadcn/ui components: DropdownMenu, Badge, Card, Tabs
- Laravel Notifications database channel

## Notes

- Consider adding sound/browser notification for new notifications
- Add notification categories/grouping
- Consider real-time updates with Laravel Echo
- Add keyboard shortcuts (e.g., N to open notifications)

# PR #013: Notification Center UI

## Related Ticket

[TICKET-013: Notification Center UI](./TICKET-013-notification-center-ui.md)

## Epic

[EPIC-006: Notification System Enhancement](./EPIC-006-notification-system-enhancement.md)

## Description

This PR implements a comprehensive notification center UI with bell icon, dropdown preview, full notifications page, and management features (mark as read, delete) for all user roles.

## Changes Made

### Backend API

- ✅ Created `Api/NotificationController.php`
- ✅ Implemented `index()` - paginated notifications
- ✅ Implemented `unreadCount()` - get unread count
- ✅ Implemented `markAsRead()` - mark single as read
- ✅ Implemented `markAllAsRead()` - mark all as read
- ✅ Implemented `destroy()` - delete single notification
- ✅ Implemented `destroyAll()` - clear all notifications

### Frontend Components

- ✅ Created `resources/js/components/notifications/notification-bell.tsx`
- ✅ Created `resources/js/components/notifications/notification-item.tsx`
- ✅ Created `resources/js/components/notifications/notification-icon.tsx`

### Frontend Pages

- ✅ Created `resources/js/pages/notifications/index.tsx`
- ✅ Full notifications page with pagination
- ✅ Filter tabs (All, Unread, Read)

### Integration

- ✅ Integrated NotificationBell into AppLayout header
- ✅ Real-time unread count updates

## Type of Change

- [x] New feature (full-stack)
- [ ] Bug fix
- [ ] Breaking change
- [ ] Documentation update

## Testing Checklist

### Backend Tests

- [ ] Can fetch paginated notifications
- [ ] Can get unread count
- [ ] Can mark notification as read
- [ ] Can mark all as read
- [ ] Can delete notification
- [ ] Can clear all notifications
- [ ] Cannot access other users' notifications
- [ ] Filters work (read/unread)

### Frontend Tests

- [ ] Bell icon renders in header
- [ ] Unread badge shows correct count
- [ ] Dropdown opens and shows recent 5 notifications
- [ ] Notification items render correctly
- [ ] Mark as read works
- [ ] Mark all as read works
- [ ] Delete notification works
- [ ] Clear all works
- [ ] Link to full page works
- [ ] Full page renders with pagination
- [ ] Filter tabs work

### Integration Tests

- [ ] New notification updates unread count
- [ ] Marking as read decreases count
- [ ] Deleting decreases count
- [ ] Real-time updates work (if implemented)

## Verification Steps

```bash
# Run backend tests
./vendor/bin/sail pest tests/Feature/Api/NotificationControllerTest.php

# Run frontend tests
npm test -- NotificationBell
npm test -- NotificationItem

# Manual testing:
# 1. Login as any user
# 2. Trigger some notifications (enroll, verify document, etc.)
# 3. Check bell icon shows unread count
# 4. Click bell to open dropdown
# 5. See recent 5 notifications
# 6. Click "Mark as read" on one
# 7. Verify count decreases
# 8. Click "Mark all as read"
# 9. Verify count becomes 0
# 10. Click "View all notifications"
# 11. Navigate to full page
# 12. Test pagination
# 13. Test filter tabs
# 14. Delete a notification
# 15. Clear all notifications
```

## API Endpoints

### Get Notifications

```
GET /api/notifications?filter=unread&page=1
```

**Response:**

```json
{
  "data": [
    {
      "id": "uuid",
      "type": "App\\Notifications\\EnrollmentApprovedNotification",
      "data": {
        "message": "Enrollment application for John Doe has been approved.",
        "enrollment_id": 1
      },
      "read_at": null,
      "created_at": "2025-10-10T12:00:00Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

### Get Unread Count

```
GET /api/notifications/unread-count
```

**Response:**

```json
{
    "count": 5
}
```

### Mark as Read

```
POST /api/notifications/{id}/read
```

### Mark All as Read

```
POST /api/notifications/read-all
```

### Delete Notification

```
DELETE /api/notifications/{id}
```

### Clear All

```
DELETE /api/notifications
```

## Component Implementation

### NotificationBell

```tsx
export function NotificationBell() {
    const [unreadCount, setUnreadCount] = useState(0);
    const [isOpen, setIsOpen] = useState(false);
    const [notifications, setNotifications] = useState([]);

    useEffect(() => {
        fetchUnreadCount();
        const interval = setInterval(fetchUnreadCount, 30000);
        return () => clearInterval(interval);
    }, []);

    const fetchUnreadCount = async () => {
        const response = await fetch('/api/notifications/unread-count');
        const data = await response.json();
        setUnreadCount(data.count);
    };

    return (
        <DropdownMenu open={isOpen} onOpenChange={setIsOpen}>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="icon" className="relative">
                    <BellIcon className="h-5 w-5" />
                    {unreadCount > 0 && <Badge className="absolute -top-1 -right-1">{unreadCount > 9 ? '9+' : unreadCount}</Badge>}
                </Button>
            </DropdownMenuTrigger>

            <DropdownMenuContent align="end" className="w-80">
                {/* Notification list */}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
```

### NotificationItem

```tsx
interface NotificationItemProps {
    notification: Notification;
    onRead?: () => void;
    onDelete?: () => void;
    showActions?: boolean;
}

export function NotificationItem({ notification, onRead, onDelete, showActions }: NotificationItemProps) {
    const isUnread = !notification.read_at;

    return (
        <div className={cn('p-3', isUnread && 'bg-blue-50')}>
            <div className="flex gap-3">
                <NotificationIcon type={notification.type} />
                <div className="flex-1">
                    <p className={cn('text-sm', isUnread && 'font-semibold')}>{notification.data.message}</p>
                    <p className="text-xs text-muted-foreground">{formatDistanceToNow(new Date(notification.created_at), { addSuffix: true })}</p>
                    {showActions && (
                        <div className="mt-2 flex gap-2">
                            {isUnread && (
                                <Button size="sm" onClick={onRead}>
                                    Mark as read
                                </Button>
                            )}
                            <Button size="sm" variant="ghost" onClick={onDelete}>
                                Delete
                            </Button>
                        </div>
                    )}
                </div>
                {isUnread && <div className="h-2 w-2 rounded-full bg-blue-500" />}
            </div>
        </div>
    );
}
```

## Notifications Page

```tsx
export default function NotificationsPage({ notifications: initialNotifications }) {
    const [filter, setFilter] = useState('all');
    const [notifications, setNotifications] = useState(initialNotifications);

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

                <Tabs value={filter} onValueChange={setFilter}>
                    <TabsList>
                        <TabsTrigger value="all">All</TabsTrigger>
                        <TabsTrigger value="unread">Unread</TabsTrigger>
                        <TabsTrigger value="read">Read</TabsTrigger>
                    </TabsList>

                    <TabsContent value={filter}>
                        {notifications.data.map((notification) => (
                            <Card key={notification.id}>
                                <NotificationItem
                                    notification={notification}
                                    onRead={() => handleRead(notification.id)}
                                    onDelete={() => handleDelete(notification.id)}
                                />
                            </Card>
                        ))}
                    </TabsContent>
                </Tabs>
            </div>
        </AppLayout>
    );
}
```

## Integration into AppLayout

### Header Integration

```tsx
<div className="flex items-center gap-4">
    <NotificationBell />
    <UserMenu />
</div>
```

## UI/UX Features

### Unread Badge

- Shows count up to 9
- Shows "9+" for 10 or more
- Red background for visibility
- Absolute positioning over bell icon

### Dropdown Preview

- Shows latest 5 notifications
- Scrollable if more than 5
- Quick actions (mark as read, delete)
- "View all" link at bottom

### Full Notifications Page

- Paginated list (20 per page)
- Filter tabs for All/Unread/Read
- Bulk actions (mark all, clear all)
- Empty state when no notifications
- Click notification to view related item

### Notification Types with Icons

- Enrollment: CheckCircle (green)
- Document: FileCheck (blue)
- Payment: DollarSign (yellow)
- Announcement: Bell (purple)
- General: Info (gray)

## Screenshots

_[Add screenshots before merging]_

1. Bell icon with unread badge
2. Dropdown open with notifications
3. Full notifications page
4. Filter tabs
5. Empty state
6. Mobile view
7. Mark all as read confirmation
8. Clear all confirmation

## Performance Considerations

- Unread count updates every 30 seconds
- Dropdown loads notifications on open (not on mount)
- Full page uses pagination (20 items)
- Debounced filter changes
- Optimistic UI updates

## Dependencies

- [PR-012](./PR-012-notification-preferences-backend.md) - Preferences must exist
- shadcn/ui: DropdownMenu, Badge, Card, Tabs, Button
- date-fns for date formatting
- Laravel Notifications database channel

## Breaking Changes

None

## Deployment Notes

- No migration needed
- Build frontend: `npm run build`
- Clear route cache: `php artisan route:clear`

## Post-Merge Checklist

- [ ] Bell icon visible in header
- [ ] Unread count displays correctly
- [ ] Dropdown works on all pages
- [ ] Full page accessible at /notifications
- [ ] Mark as read works
- [ ] Delete works
- [ ] Pagination works
- [ ] Filters work
- [ ] Mobile view works
- [ ] No performance issues
- [ ] Next ticket (TICKET-014) can begin

## Reviewer Notes

Please verify:

1. API authorization is correct
2. Real-time updates don't cause performance issues
3. UI is intuitive and accessible
4. Notification types are properly categorized
5. Empty states are helpful
6. Mobile experience is smooth
7. Pagination doesn't skip items
8. Unread count is always accurate

## Accessibility

- [x] Keyboard navigation (Tab, Enter, Escape)
- [x] Screen reader announces unread count
- [x] Focus management in dropdown
- [x] ARIA labels for all actions
- [x] Color not the only indicator (unread dot + bold text)

---

**Ticket:** #013
**Estimated Effort:** 1.5 days
**Actual Effort:** _[To be filled after completion]_

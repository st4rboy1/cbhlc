# PR #016: Audit Log Viewer UI

## Related Ticket

[TICKET-016: Audit Log Viewer UI](./TICKET-016-audit-log-viewer-ui.md)

## Epic

[EPIC-007: Audit System Verification](./EPIC-007-audit-system-verification.md)

## Description

This PR implements a comprehensive audit log viewer interface for administrators to view, filter, search, and analyze system activity logs with visual diff for changes, export capabilities, and detailed activity tracking.

## Changes Made

### Backend Controllers

- ✅ Created `SuperAdmin/AuditLogController.php`
- ✅ Created `Admin/AuditLogController.php` (limited view)
- ✅ Implemented advanced filtering (user, model, action, date range)
- ✅ Implemented export to CSV
- ✅ Implemented detail views with related activities

### Frontend Pages

- ✅ Created `resources/js/pages/super-admin/audit-logs/index.tsx`
- ✅ Created `resources/js/pages/super-admin/audit-logs/show.tsx`
- ✅ Created `resources/js/pages/admin/audit-logs/index.tsx`

### Frontend Components

- ✅ Created `resources/js/components/audit-logs/audit-log-table.tsx`
- ✅ Created `resources/js/components/audit-logs/audit-log-filters.tsx`
- ✅ Created `resources/js/components/audit-logs/audit-log-diff.tsx`
- ✅ Created `resources/js/components/audit-logs/activity-timeline.tsx`

### Export

- ✅ Created `App\Exports\AuditLogExport.php`
- ✅ CSV export with all filter support

## Type of Change

- [x] New feature (full-stack)
- [ ] Bug fix
- [ ] Breaking change
- [ ] Documentation update

## Testing Checklist

### Backend Tests

- [ ] Super Admin can view all logs
- [ ] Admin can view relevant logs
- [ ] Guardian/Student cannot access logs
- [ ] Filters work (user, model, action, date)
- [ ] Search works on description
- [ ] Pagination works
- [ ] Export generates correct CSV
- [ ] Detail view shows related activities
- [ ] Authorization enforced

### Frontend Tests

- [ ] Index page renders with logs
- [ ] Table displays all columns
- [ ] Filters apply correctly
- [ ] Search works
- [ ] Pagination works
- [ ] Detail view opens
- [ ] Diff shows before/after
- [ ] Timeline displays correctly
- [ ] Export button works
- [ ] Responsive design

### Integration Tests

- [ ] Real-time log updates (optional)
- [ ] Related activities linked correctly
- [ ] User avatars display
- [ ] Model links work

## Verification Steps

```bash
# Run backend tests
./vendor/bin/sail pest tests/Feature/SuperAdmin/AuditLogTest.php

# Run frontend tests
npm test -- AuditLog

# Manual testing:
# 1. Login as Super Admin
# 2. Navigate to /super-admin/audit-logs
# 3. Verify all logs display
# 4. Test each filter
# 5. Test search
# 6. Click on log to view details
# 7. Verify diff display
# 8. Test export
# 9. Login as Admin
# 10. Verify limited view
# 11. Login as Guardian
# 12. Verify no access (403)
```

## Routes

```php
// Super Admin routes
Route::prefix('super-admin/audit-logs')->name('super-admin.audit-logs.')->middleware('role:super_admin')->group(function () {
    Route::get('/', [SuperAdminAuditLogController::class, 'index'])->name('index');
    Route::get('/{activity}', [SuperAdminAuditLogController::class, 'show'])->name('show');
    Route::get('/model/{type}/{id}', [SuperAdminAuditLogController::class, 'forModel'])->name('for-model');
    Route::get('/user/{user}', [SuperAdminAuditLogController::class, 'forUser'])->name('for-user');
    Route::post('/export', [SuperAdminAuditLogController::class, 'export'])->name('export');
});

// Admin routes (limited)
Route::prefix('admin/audit-logs')->name('admin.audit-logs.')->middleware('role:administrator')->group(function () {
    Route::get('/', [AdminAuditLogController::class, 'index'])->name('index');
});
```

## Controller Implementation

### Index Method

```php
public function index(Request $request)
{
    $query = Activity::with(['causer', 'subject'])->latest();

    // Apply filters
    if ($request->causer_id) {
        $query->causedBy(User::find($request->causer_id));
    }

    if ($request->subject_type) {
        $query->where('subject_type', $request->subject_type);
    }

    if ($request->log_name) {
        $query->inLog($request->log_name);
    }

    if ($request->description) {
        $query->where('description', 'like', "%{$request->description}%");
    }

    if ($request->date_from) {
        $query->where('created_at', '>=', $request->date_from);
    }

    if ($request->date_to) {
        $query->where('created_at', '<=', $request->date_to);
    }

    $activities = $query->paginate(50);

    return Inertia::render('SuperAdmin/AuditLogs/Index', [
        'activities' => $activities,
        'filters' => $request->only(['causer_id', 'subject_type', 'log_name', 'description', 'date_from', 'date_to']),
        'users' => User::select('id', 'name')->get(),
        'subjectTypes' => Activity::distinct()->pluck('subject_type'),
    ]);
}
```

### Show Method

```php
public function show(Activity $activity)
{
    $activity->load(['causer', 'subject']);

    $relatedActivities = Activity::where('subject_type', $activity->subject_type)
        ->where('subject_id', $activity->subject_id)
        ->where('id', '!=', $activity->id)
        ->latest()
        ->take(10)
        ->get();

    return Inertia::render('SuperAdmin/AuditLogs/Show', [
        'activity' => $activity,
        'relatedActivities' => $relatedActivities,
    ]);
}
```

### Export Method

```php
public function export(Request $request)
{
    $query = Activity::with(['causer', 'subject'])->latest();

    // Apply same filters as index
    // ...

    $activities = $query->get();

    return (new AuditLogExport($activities))->download('audit-log.csv');
}
```

## Frontend Index Page

```tsx
export default function AuditLogsIndex({ activities, filters, users, subjectTypes }) {
    const [localFilters, setLocalFilters] = useState(filters);

    return (
        <AppLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold">Audit Logs</h1>
                    <Button onClick={() => router.post('/super-admin/audit-logs/export', localFilters)}>Export to CSV</Button>
                </div>

                {/* Filters Card */}
                <AuditLogFilters
                    filters={localFilters}
                    users={users}
                    subjectTypes={subjectTypes}
                    onFilterChange={setLocalFilters}
                    onApply={applyFilters}
                />

                {/* Activity Table */}
                <Card>
                    <AuditLogTable activities={activities.data} />
                    <Pagination links={activities.links} />
                </Card>
            </div>
        </AppLayout>
    );
}
```

## AuditLogTable Component

```tsx
export function AuditLogTable({ activities }) {
    return (
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Timestamp</TableHead>
                    <TableHead>User</TableHead>
                    <TableHead>Action</TableHead>
                    <TableHead>Model</TableHead>
                    <TableHead>IP Address</TableHead>
                    <TableHead>Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {activities.map((activity) => (
                    <TableRow key={activity.id}>
                        <TableCell>{formatDateTime(activity.created_at)}</TableCell>
                        <TableCell>
                            <div className="flex items-center gap-2">
                                <Avatar user={activity.causer} size="sm" />
                                <span>{activity.causer?.name || 'System'}</span>
                            </div>
                        </TableCell>
                        <TableCell>
                            <Badge variant={getActionVariant(activity.description)}>{activity.description}</Badge>
                        </TableCell>
                        <TableCell>
                            {activity.subject_type && (
                                <span className="text-sm">
                                    {formatModelName(activity.subject_type)} #{activity.subject_id}
                                </span>
                            )}
                        </TableCell>
                        <TableCell className="text-xs text-muted-foreground">{activity.properties?.ip_address || '-'}</TableCell>
                        <TableCell>
                            <Button size="sm" variant="ghost" onClick={() => router.visit(`/super-admin/audit-logs/${activity.id}`)}>
                                View Details
                            </Button>
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
```

## Activity Diff Component

```tsx
export function ActivityDiff({ properties }) {
    if (!properties.old || !properties.attributes) {
        return <p className="text-muted-foreground">No changes to display</p>;
    }

    const changes = Object.keys(properties.attributes).filter((key) => properties.old[key] !== properties.attributes[key]);

    return (
        <div className="space-y-2">
            {changes.map((key) => (
                <div key={key} className="flex gap-4 text-sm">
                    <span className="w-32 font-medium">{key}:</span>
                    <div className="flex-1 space-y-1">
                        <div className="flex items-center gap-2">
                            <MinusIcon className="h-4 w-4 text-red-500" />
                            <span className="text-red-600 line-through">{String(properties.old[key])}</span>
                        </div>
                        <div className="flex items-center gap-2">
                            <PlusIcon className="h-4 w-4 text-green-500" />
                            <span className="text-green-600">{String(properties.attributes[key])}</span>
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
}
```

## Activity Timeline Component

```tsx
export function ActivityTimeline({ activities }) {
    return (
        <div className="space-y-4">
            {activities.map((activity, index) => (
                <div key={activity.id} className="flex gap-4">
                    <div className="flex flex-col items-center">
                        <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary">
                            <ActivityIcon type={activity.description} />
                        </div>
                        {index < activities.length - 1 && <div className="my-2 w-0.5 flex-1 bg-border" />}
                    </div>
                    <div className="flex-1 pb-4">
                        <div className="flex items-center justify-between">
                            <p className="font-medium">{activity.description}</p>
                            <time className="text-xs text-muted-foreground">
                                {formatDistanceToNow(new Date(activity.created_at), { addSuffix: true })}
                            </time>
                        </div>
                        <p className="text-sm text-muted-foreground">by {activity.causer?.name || 'System'}</p>
                    </div>
                </div>
            ))}
        </div>
    );
}
```

## Export Implementation

```php
class AuditLogExport implements FromCollection, WithHeadings
{
    public function __construct(protected Collection $activities) {}

    public function collection()
    {
        return $this->activities->map(function ($activity) {
            return [
                'ID' => $activity->id,
                'Timestamp' => $activity->created_at->toDateTimeString(),
                'User' => $activity->causer?->name ?? 'System',
                'User Email' => $activity->causer?->email ?? '-',
                'Action' => $activity->description,
                'Model Type' => $activity->subject_type ?? '-',
                'Model ID' => $activity->subject_id ?? '-',
                'IP Address' => $activity->properties['ip_address'] ?? '-',
                'Properties' => json_encode($activity->properties),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Timestamp',
            'User',
            'User Email',
            'Action',
            'Model Type',
            'Model ID',
            'IP Address',
            'Properties',
        ];
    }
}
```

## UI Features

### Action Badges

- Created: Blue
- Updated: Yellow
- Deleted: Red
- Approved: Green
- Rejected: Red
- Logged in: Green
- Logged out: Gray
- Failed: Red

### Filters

- User dropdown (searchable)
- Model type dropdown
- Action search
- Date range picker
- Clear all filters button

### Detail View

- Full activity information
- Before/after comparison
- Related activities timeline
- User information panel
- IP address and user agent
- Raw properties (JSON)

## Screenshots

_[Add screenshots before merging]_

1. Index page with filters
2. Table with activities
3. Detail view
4. Diff display
5. Timeline
6. Export dialog
7. Mobile view
8. Empty state

## Dependencies

- [PR-015](./PR-015-verify-audit-logging-coverage.md) - Logging must be in place
- Spatie Activity Log package
- maatwebsite/excel for export

## Breaking Changes

None

## Deployment Notes

- Install maatwebsite/excel: `composer require maatwebsite/excel`
- Build frontend: `npm run build`
- Clear cache: `php artisan cache:clear`

## Post-Merge Checklist

- [ ] Audit log pages accessible
- [ ] Filters work correctly
- [ ] Search works
- [ ] Detail view displays correctly
- [ ] Diff shows changes
- [ ] Timeline displays
- [ ] Export generates CSV
- [ ] Authorization enforced
- [ ] Responsive design works
- [ ] Epic complete! All audit system features implemented

## Reviewer Notes

Please verify:

1. All filters work correctly and efficiently
2. Search doesn't cause performance issues
3. Export handles large datasets
4. Diff display is clear and accurate
5. Authorization restricts access properly
6. UI is intuitive and informative
7. No sensitive data exposed
8. Pagination doesn't skip records

## Performance Considerations

- Paginate results (50 per page)
- Eager load relationships (causer, subject)
- Index on created_at for date filters
- Cache user list for filter dropdown
- Limit export size or queue for large datasets

## Security Considerations

- Only Super Admin and Admin can access
- Cannot modify or delete audit logs from UI
- Sensitive properties should be filtered in display
- Export contains full data (admin only)
- Activity log access is logged (meta-logging)

---

**Ticket:** #016
**Estimated Effort:** 1.5 days
**Actual Effort:** _[To be filled after completion]_
**Epic Status:** ✅ COMPLETE - Audit System Verification
**All PR Templates Complete!** 🎉

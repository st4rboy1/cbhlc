# Ticket #016: Audit Log Viewer UI

**Epic:** [EPIC-007 Audit System Verification](./EPIC-007-audit-system-verification.md)

**Type:** Story
**Priority:** High
**Estimated Effort:** 1.5 days
**Assignee:** TBD

## Description

Create comprehensive audit log viewer interface for administrators to view, filter, and search system activity logs with detailed change tracking.

## Acceptance Criteria

- [ ] Audit log index page with filterable table
- [ ] Detail view showing full activity information
- [ ] Advanced filtering (by user, model, action, date range)
- [ ] Search functionality
- [ ] Visual diff for before/after changes
- [ ] Export audit logs to CSV
- [ ] Pagination for large datasets
- [ ] Responsive design
- [ ] Permission-based access control

## Implementation Details

### Backend Routes

```php
// Super Admin routes
Route::prefix('super-admin/audit-logs')->name('super-admin.audit-logs.')->middleware('role:super_admin')->group(function () {
    Route::get('/', [SuperAdminAuditLogController::class, 'index'])->name('index');
    Route::get('/{activity}', [SuperAdminAuditLogController::class, 'show'])->name('show');
    Route::get('/model/{type}/{id}', [SuperAdminAuditLogController::class, 'forModel'])->name('for-model');
    Route::get('/user/{user}', [SuperAdminAuditLogController::class, 'forUser'])->name('for-user');
    Route::post('/export', [SuperAdminAuditLogController::class, 'export'])->name('export');
});

// Admin routes (limited view)
Route::prefix('admin/audit-logs')->name('admin.audit-logs.')->middleware('role:administrator')->group(function () {
    Route::get('/', [AdminAuditLogController::class, 'index'])->name('index');
});
```

### Controller

`app/Http/Controllers/SuperAdmin/AuditLogController.php`

```php
class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])
            ->latest();

        // Apply filters
        if ($request->causer_id) {
            $query->causedBy(User::find($request->causer_id));
        }

        if ($request->subject_type) {
            $query->forSubject($request->subject_type);
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

    public function forModel(string $type, int $id)
    {
        $activities = Activity::where('subject_type', $type)
            ->where('subject_id', $id)
            ->with('causer')
            ->latest()
            ->paginate(20);

        return Inertia::render('SuperAdmin/AuditLogs/ForModel', [
            'activities' => $activities,
            'modelType' => $type,
            'modelId' => $id,
        ]);
    }

    public function forUser(User $user)
    {
        $activities = Activity::causedBy($user)
            ->with('subject')
            ->latest()
            ->paginate(50);

        return Inertia::render('SuperAdmin/AuditLogs/ForUser', [
            'activities' => $activities,
            'user' => $user,
        ]);
    }

    public function export(Request $request)
    {
        // Apply same filters as index
        $query = Activity::with(['causer', 'subject'])->latest();

        // Apply filters...

        $activities = $query->get();

        return (new AuditLogExport($activities))->download('audit-log.csv');
    }
}
```

### Frontend Pages

**Index Page**
`resources/js/pages/super-admin/audit-logs/index.tsx`

```tsx
export default function AuditLogsIndex({ activities, filters, users, subjectTypes }) {
    const [localFilters, setLocalFilters] = useState(filters);

    const applyFilters = () => {
        router.get('/super-admin/audit-logs', localFilters, {
            preserveState: true,
        });
    };

    return (
        <AppLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold">Audit Logs</h1>
                    <Button onClick={() => router.post('/super-admin/audit-logs/export', localFilters)}>Export to CSV</Button>
                </div>

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle>Filters</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <Label>User</Label>
                                <Select
                                    value={localFilters.causer_id}
                                    onValueChange={(value) => setLocalFilters({ ...localFilters, causer_id: value })}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="All users" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {users.map((user) => (
                                            <SelectItem key={user.id} value={user.id}>
                                                {user.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div>
                                <Label>Model Type</Label>
                                <Select
                                    value={localFilters.subject_type}
                                    onValueChange={(value) => setLocalFilters({ ...localFilters, subject_type: value })}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="All models" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {subjectTypes.map((type) => (
                                            <SelectItem key={type} value={type}>
                                                {type}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div>
                                <Label>Action</Label>
                                <Input
                                    placeholder="Search actions..."
                                    value={localFilters.description}
                                    onChange={(e) => setLocalFilters({ ...localFilters, description: e.target.value })}
                                />
                            </div>

                            <div>
                                <Label>Date From</Label>
                                <DatePicker
                                    value={localFilters.date_from}
                                    onChange={(date) => setLocalFilters({ ...localFilters, date_from: date })}
                                />
                            </div>

                            <div>
                                <Label>Date To</Label>
                                <DatePicker value={localFilters.date_to} onChange={(date) => setLocalFilters({ ...localFilters, date_to: date })} />
                            </div>

                            <div className="flex items-end">
                                <Button onClick={applyFilters} className="w-full">
                                    Apply Filters
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Activity Table */}
                <Card>
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
                            {activities.data.map((activity) => (
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

                    <div className="p-4">
                        <Pagination links={activities.links} />
                    </div>
                </Card>
            </div>
        </AppLayout>
    );
}
```

**Detail Page**
`resources/js/pages/super-admin/audit-logs/show.tsx`

Display:

- Full activity details
- Properties (JSON formatted)
- Before/After comparison (if available)
- Related activities timeline
- User information and IP address

### Components

**ActivityDiff Component**

```tsx
interface ActivityDiffProps {
    properties: {
        old?: Record<string, any>;
        attributes?: Record<string, any>;
    };
}

export function ActivityDiff({ properties }: ActivityDiffProps) {
    if (!properties.old || !properties.attributes) {
        return null;
    }

    const changes = Object.keys(properties.attributes).filter((key) => properties.old[key] !== properties.attributes[key]);

    return (
        <div className="space-y-2">
            {changes.map((key) => (
                <div key={key} className="flex gap-4 text-sm">
                    <span className="w-32 font-medium">{key}:</span>
                    <div className="flex-1">
                        <div className="text-red-600 line-through">{String(properties.old[key])}</div>
                        <div className="text-green-600">{String(properties.attributes[key])}</div>
                    </div>
                </div>
            ))}
        </div>
    );
}
```

## Testing Requirements

- [ ] UI test: index page renders
- [ ] UI test: filters work correctly
- [ ] UI test: pagination works
- [ ] UI test: detail view shows full information
- [ ] UI test: export functionality
- [ ] UI test: search works
- [ ] Feature test: authorization (only admins)
- [ ] Performance test with large dataset

## Dependencies

- [TICKET-015](./TICKET-015-verify-audit-logging-coverage.md) - Logging must be in place
- Spatie Activity Log package
- CSV export library

## Notes

- Add real-time log updates (optional)
- Consider adding log filtering presets
- Add visualization for activity trends
- Consider retention policy configuration UI

# Ticket #009: Enrollment Period Management UI

**Epic:** [EPIC-002 Enrollment Period Management](./EPIC-002-enrollment-period-management.md)

**Type:** Story
**Priority:** High
**Estimated Effort:** 1 day
**Assignee:** TBD

## Description

Create user interface for Super Admin to manage enrollment periods including list view, create/edit forms, and status management.

## Acceptance Criteria

- [ ] Enrollment periods index page with list
- [ ] Create enrollment period form
- [ ] Edit enrollment period form
- [ ] Period details view
- [ ] Activate/Close buttons with confirmation
- [ ] Delete button with confirmation
- [ ] Status badges (upcoming, active, closed)
- [ ] Timeline visualization of dates
- [ ] Responsive design

## Implementation Details

### Pages

**Index Page**
`resources/js/pages/super-admin/enrollment-periods/index.tsx`

Features:

- Table with columns: School Year, Status, Start Date, End Date, Deadline, Enrollment Count, Actions
- Status badge (green for active, blue for upcoming, gray for closed)
- Quick actions: Activate, Close, Edit, Delete
- "Create New Period" button
- Filter by status
- Active period highlighted

**Create Page**
`resources/js/pages/super-admin/enrollment-periods/create.tsx`

Form fields:

- School Year (text input with format hint: 2025-2026)
- Start Date (date picker)
- End Date (date picker)
- Early Registration Deadline (optional date picker)
- Regular Registration Deadline (required date picker)
- Late Registration Deadline (optional date picker)
- Description (textarea)
- Allow New Students (checkbox, default: true)
- Allow Returning Students (checkbox, default: true)

Validation:

- Real-time validation feedback
- Date range validation
- School year format validation

**Edit Page**
`resources/js/pages/super-admin/enrollment-periods/edit.tsx`

Same as create page with:

- Pre-filled values
- Warning if period is active
- Note about impact on existing enrollments

**Show Page**
`resources/js/pages/super-admin/enrollment-periods/show.tsx`

Displays:

- Period details
- Timeline visualization
- Enrollment statistics (total, pending, approved)
- Status management buttons
- Recent enrollments table
- Activity log

### Components

**EnrollmentPeriodCard**
`resources/js/components/enrollment-periods/enrollment-period-card.tsx`

```tsx
interface EnrollmentPeriodCardProps {
    period: EnrollmentPeriod;
    onActivate?: () => void;
    onClose?: () => void;
    onEdit?: () => void;
    onDelete?: () => void;
}
```

Displays:

- School year (large text)
- Status badge
- Date range
- Days remaining (if active)
- Enrollment count
- Action buttons

**PeriodTimeline**
`resources/js/components/enrollment-periods/period-timeline.tsx`

Visual timeline showing:

- Start date
- Early deadline (if set)
- Regular deadline
- Late deadline (if set)
- End date
- Current date indicator

**PeriodStatusBadge**

```tsx
interface PeriodStatusBadgeProps {
    status: 'upcoming' | 'active' | 'closed';
}
```

Color coding:

- Active: Green with pulse animation
- Upcoming: Blue
- Closed: Gray

**ActivatePeriodDialog**
Confirmation dialog:

- Warning that other active periods will be closed
- Impact summary
- Confirm/Cancel buttons

**ClosePeriodDialog**
Confirmation dialog:

- Warning about closing period
- Summary of pending enrollments
- Confirm/Cancel buttons

### Example UI Layout

```tsx
<div className="space-y-6">
    <div className="flex items-center justify-between">
        <h1>Enrollment Periods</h1>
        <Button onClick={createNew}>Create New Period</Button>
    </div>

    {activePeriod && (
        <Alert>
            <AlertTitle>Active Period: {activePeriod.school_year}</AlertTitle>
            <AlertDescription>{activePeriod.getDaysRemaining()} days remaining until deadline</AlertDescription>
        </Alert>
    )}

    <Tabs defaultValue="all">
        <TabsList>
            <TabsTrigger value="all">All</TabsTrigger>
            <TabsTrigger value="active">Active</TabsTrigger>
            <TabsTrigger value="upcoming">Upcoming</TabsTrigger>
            <TabsTrigger value="closed">Closed</TabsTrigger>
        </TabsList>

        <TabsContent value="all">
            <div className="grid gap-4">
                {periods.map((period) => (
                    <EnrollmentPeriodCard
                        key={period.id}
                        period={period}
                        onActivate={() => handleActivate(period)}
                        onClose={() => handleClose(period)}
                        onEdit={() => router.visit(`/super-admin/enrollment-periods/${period.id}/edit`)}
                        onDelete={() => handleDelete(period)}
                    />
                ))}
            </div>
        </TabsContent>
    </Tabs>
</div>
```

## Testing Requirements

- [ ] UI test: index page renders
- [ ] UI test: create form works
- [ ] UI test: edit form works
- [ ] UI test: validation messages display
- [ ] UI test: activate confirmation dialog
- [ ] UI test: close confirmation dialog
- [ ] UI test: delete confirmation dialog
- [ ] UI test: status badges display correctly
- [ ] UI test: timeline visualization works
- [ ] Accessibility: keyboard navigation
- [ ] Accessibility: screen reader support

## Dependencies

- [TICKET-008](./TICKET-008-enrollment-period-crud-backend.md) - Backend API
- shadcn/ui components: Card, Badge, Button, Dialog, Alert, Tabs, DatePicker
- Form library: React Hook Form

## Notes

- Add tooltips explaining each deadline type
- Consider adding a wizard for creating periods
- Add duplicate period feature (copy from previous year)
- Show warning when editing active period
- Add period comparison feature

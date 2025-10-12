# Ticket #014: Notification Preferences UI

**Epic:** [EPIC-006 Notification System Enhancement](./EPIC-006-notification-system-enhancement.md)

**Type:** Story
**Priority:** High
**Estimated Effort:** 0.5 day
**Assignee:** TBD

## Description

Create settings page for users to manage their notification preferences, controlling which notifications they receive via email and in-app.

## Acceptance Criteria

- [ ] Notification preferences page at `/settings/notifications`
- [ ] Toggle switches for each notification type
- [ ] Separate controls for email and in-app notifications
- [ ] Save changes button
- [ ] Reset to defaults button
- [ ] Visual feedback for saving
- [ ] Responsive design
- [ ] Link from settings menu

## Implementation Details

### Page

`resources/js/pages/settings/notifications.tsx`

```tsx
import { useState } from 'react';
import { router } from '@inertiajs/react';
import { SettingsLayout } from '@/layouts/settings/layout';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';

interface NotificationPreference {
    notification_type: string;
    email_enabled: boolean;
    database_enabled: boolean;
}

interface Props {
    preferences: Record<string, NotificationPreference>;
    availableTypes: Record<string, string>;
}

export default function NotificationsSettings({ preferences, availableTypes }: Props) {
    const [localPreferences, setLocalPreferences] = useState(preferences);
    const [saving, setSaving] = useState(false);

    const updatePreference = (type: string, channel: 'email' | 'database', value: boolean) => {
        setLocalPreferences((prev) => ({
            ...prev,
            [type]: {
                ...prev[type],
                [`${channel}_enabled`]: value,
            },
        }));
    };

    const handleSave = () => {
        setSaving(true);
        router.put(
            '/settings/notifications',
            {
                preferences: localPreferences,
            },
            {
                onFinish: () => setSaving(false),
                onSuccess: () => {
                    toast.success('Notification preferences updated');
                },
            },
        );
    };

    const handleReset = () => {
        router.post(
            '/settings/notifications/reset',
            {},
            {
                onSuccess: () => {
                    toast.success('Notification preferences reset to defaults');
                },
            },
        );
    };

    return (
        <SettingsLayout>
            <div className="space-y-6">
                <div>
                    <h3 className="text-lg font-medium">Notification Preferences</h3>
                    <p className="text-sm text-muted-foreground">Choose which notifications you want to receive and how you want to receive them.</p>
                </div>

                <Separator />

                <Card>
                    <CardHeader>
                        <CardTitle>Notification Types</CardTitle>
                        <CardDescription>Control your notification preferences for each type of notification.</CardDescription>
                    </CardHeader>

                    <CardContent className="space-y-6">
                        {Object.entries(availableTypes).map(([type, label]) => {
                            const preference = localPreferences[type] || {
                                email_enabled: true,
                                database_enabled: true,
                            };

                            return (
                                <div key={type} className="flex items-start justify-between space-x-4">
                                    <div className="flex-1">
                                        <Label className="text-base">{label}</Label>
                                    </div>

                                    <div className="flex gap-6">
                                        <div className="flex items-center space-x-2">
                                            <Switch
                                                id={`${type}-email`}
                                                checked={preference.email_enabled}
                                                onCheckedChange={(checked) => updatePreference(type, 'email', checked)}
                                            />
                                            <Label htmlFor={`${type}-email`} className="text-sm text-muted-foreground">
                                                Email
                                            </Label>
                                        </div>

                                        <div className="flex items-center space-x-2">
                                            <Switch
                                                id={`${type}-database`}
                                                checked={preference.database_enabled}
                                                onCheckedChange={(checked) => updatePreference(type, 'database', checked)}
                                            />
                                            <Label htmlFor={`${type}-database`} className="text-sm text-muted-foreground">
                                                In-App
                                            </Label>
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </CardContent>
                </Card>

                <div className="flex justify-between">
                    <Button variant="outline" onClick={handleReset}>
                        Reset to Defaults
                    </Button>

                    <Button onClick={handleSave} disabled={saving}>
                        {saving ? 'Saving...' : 'Save Changes'}
                    </Button>
                </div>
            </div>
        </SettingsLayout>
    );
}
```

### Settings Layout Navigation

Update `resources/js/layouts/settings/layout.tsx`:

```tsx
const settingsNav = [
    { name: 'Profile', href: '/settings/profile' },
    { name: 'Password', href: '/settings/password' },
    { name: 'Notifications', href: '/settings/notifications' }, // Add this
    { name: 'Appearance', href: '/settings/appearance' },
];
```

### Route

```php
Route::get('/settings/notifications', [NotificationPreferenceController::class, 'index'])
    ->name('settings.notifications');
```

### Enhanced Features (Optional)

**Notification Groups**
Group similar notifications:

```tsx
const notificationGroups = {
  enrollment: {
    title: 'Enrollment Notifications',
    types: ['enrollment_approved', 'enrollment_rejected', 'enrollment_pending'],
  },
  documents: {
    title: 'Document Notifications',
    types: ['document_verified', 'document_rejected'],
  },
  billing: {
    title: 'Billing Notifications',
    types: ['payment_due', 'payment_received', 'payment_overdue'],
  },
  system: {
    title: 'System Notifications',
    types: ['announcement_published', 'inquiry_response'],
  },
};

// Render with accordions
{Object.entries(notificationGroups).map(([key, group]) => (
  <Accordion key={key} type="single" collapsible>
    <AccordionItem value={key}>
      <AccordionTrigger>{group.title}</AccordionTrigger>
      <AccordionContent>
        {group.types.map(type => (
          // Render preference controls
        ))}
      </AccordionContent>
    </AccordionItem>
  </Accordion>
))}
```

**Master Toggles**
Add master toggles for all emails / all in-app:

```tsx
<div className="flex items-center justify-between rounded-lg bg-muted p-4">
    <Label>Enable all email notifications</Label>
    <Switch checked={allEmailEnabled} onCheckedChange={toggleAllEmail} />
</div>
```

**Preview Notification**
Add test notification button:

```tsx
<Button variant="outline" onClick={sendTestNotification}>
    Send Test Notification
</Button>
```

## Testing Requirements

- [ ] UI test: page renders with all notification types
- [ ] UI test: toggles work correctly
- [ ] UI test: save changes works
- [ ] UI test: reset to defaults works
- [ ] UI test: feedback messages show
- [ ] UI test: responsive layout
- [ ] Integration test: preferences persist after save
- [ ] Integration test: reset restores defaults
- [ ] Accessibility: keyboard navigation
- [ ] Accessibility: screen reader labels

## Dependencies

- [TICKET-012](./TICKET-012-notification-preferences-backend.md) - Backend API
- shadcn/ui components: Switch, Card, Button, Label
- Settings layout

## Notes

- Add tooltips explaining each notification type
- Consider adding notification frequency options (immediate, digest)
- Add notification preview/samples
- Consider quiet hours feature
- Add export/import preferences functionality

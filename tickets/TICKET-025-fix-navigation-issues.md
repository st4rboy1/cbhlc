# TICKET-025: Fix Navigation Issues

**Type:** Bug/Enhancement
**Priority:** High
**Estimated Effort:** 0.5 day
**Status:** ✅ Completed
**Epic:** Frontend Completion
**PR:** #287 (Closed - already resolved)

## Description

Fix critical navigation issues found across all user role sidebars:
1. Hardcoded student ID in student sidebar
2. Duplicate navigation links in Registrar and Guardian sidebars
3. Missing "Pending Documents" link in Registrar sidebar

## Issues Found

### 1. Hardcoded Student ID (CRITICAL)
**File:** `resources/js/components/sidebars/student-sidebar.tsx:26`
**Issue:** Student report link uses hardcoded ID `/students/1/report`
**Fix:** Use dynamic student ID from authenticated user

### 2. Duplicate Navigation Links (HIGH)
**Registrar Sidebar:**
- "Students" and "Student Reports" both point to `/registrar/students`
- Should remove "Student Reports" or point to actual report page

**Guardian Sidebar:**
- "My Children" and "Student Reports" both point to `/guardian/students`
- Should remove "Student Reports" or point to actual report page

### 3. Missing Navigation Item (HIGH)
**Registrar Sidebar:**
- Page `/registrar/documents/pending` exists but no sidebar link
- Should add "Pending Documents" navigation item

## Acceptance Criteria

- [ ] Student sidebar uses dynamic student ID from auth user
- [ ] Registrar sidebar has no duplicate links
- [ ] Guardian sidebar has no duplicate links
- [ ] Registrar sidebar includes "Pending Documents" link
- [ ] All navigation links are tested and work correctly
- [ ] TypeScript types are proper for all changes

## Implementation Details

### Fix 1: Dynamic Student ID

**File:** `resources/js/components/sidebars/student-sidebar.tsx`

```tsx
import { usePage } from '@inertiajs/react';

// Inside component
const { auth } = usePage().props;
const studentId = auth.user.student_id; // or however student ID is stored

// Update nav item
{
    title: 'My Report',
    href: `/students/${studentId}/report`,
    icon: FileText,
}
```

### Fix 2: Remove Duplicate Links

**Registrar Sidebar** (`resources/js/components/sidebars/registrar-sidebar.tsx`):
Remove the "Student Reports" nav item (line ~40)

**Guardian Sidebar** (`resources/js/components/sidebars/guardian-sidebar.tsx`):
Remove the "Student Reports" nav item (line ~40)

### Fix 3: Add Pending Documents Link

**Registrar Sidebar** (`resources/js/components/sidebars/registrar-sidebar.tsx`):

```tsx
{
    title: 'Pending Documents',
    href: '/registrar/documents/pending',
    icon: FileCheck, // or appropriate icon
}
```

## Testing Requirements

- [ ] Test student sidebar with different student accounts
- [ ] Verify no duplicate links in Registrar sidebar
- [ ] Verify no duplicate links in Guardian sidebar
- [ ] Test "Pending Documents" link navigation
- [ ] Verify all links point to correct routes
- [ ] Test TypeScript compilation
- [ ] Visual regression test for all sidebars

## Files Changed

- `resources/js/components/sidebars/student-sidebar.tsx`
- `resources/js/components/sidebars/registrar-sidebar.tsx`
- `resources/js/components/sidebars/guardian-sidebar.tsx`

## Dependencies

None

## Notes

- This ticket addresses navigation consistency issues
- Should be done before adding new navigation items
- May need to verify User model has student_id relationship

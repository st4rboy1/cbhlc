# TICKET-027: Create Super Admin Guardians CRUD Pages

**Type:** Story
**Priority:** High
**Estimated Effort:** 1 day
**Status:** 🔴 To Do
**Epic:** Frontend Completion

## Description

Create all missing frontend pages for Super Admin Guardians CRUD operations. Backend routes and controllers exist, but React pages are missing.

## Missing Pages

1. **Index Page** - `/super-admin/guardians`
2. **Create Page** - `/super-admin/guardians/create`
3. **Show Page** - `/super-admin/guardians/{guardian}`
4. **Edit Page** - `/super-admin/guardians/{guardian}/edit`

## Existing Backend

**Controller:** `App\Http\Controllers\SuperAdmin\GuardianController`
**Routes:** All CRUD routes exist
**Model:** `App\Models\Guardian`

## Acceptance Criteria

- [ ] Index page displays all guardians in a table
- [ ] Index page has "Create" button
- [ ] Index page has search functionality (name, email, phone)
- [ ] Create page has form with all required fields
- [ ] Create page validates input before submission
- [ ] Show page displays guardian details and associated students
- [ ] Show page displays enrollment history
- [ ] Show page has Edit and Delete buttons
- [ ] Edit page has pre-filled form
- [ ] Edit page validates input before submission
- [ ] All pages use shadcn/ui components
- [ ] All pages are responsive
- [ ] TypeScript types are proper
- [ ] Breadcrumbs are implemented
- [ ] Success/error toast notifications work

## Implementation Details

### Directory Structure

```
resources/js/pages/super-admin/guardians/
├── index.tsx          # List all guardians
├── create.tsx         # Create new guardian
├── show.tsx           # View guardian details
└── edit.tsx           # Edit existing guardian
```

### Fields Required

Based on `Guardian` model:
- `first_name` (required)
- `middle_name` (optional)
- `last_name` (required)
- `relationship` (enum: father, mother, legal_guardian, grandparent, other)
- `occupation` (optional)
- `employer` (optional)
- `phone` (required)
- `email` (required, unique)
- `address` (required)
- `emergency_contact` (boolean)

### Component Structure

### Index Page
```tsx
- Heading with title and description
- Stats cards (total guardians, with students, without students)
- Search bar (name, email, phone)
- Data table with columns:
  - Name (full name)
  - Email
  - Phone
  - Relationship
  - # of Students
  - Emergency Contact (badge)
  - Actions (View, Edit, Delete)
- Pagination
- Create button
```

### Create/Edit Page
```tsx
- Heading
- Form with sections:
  - Personal Information
    - First Name, Middle Name, Last Name
  - Contact Information
    - Phone, Email, Address
  - Additional Information
    - Relationship
    - Occupation, Employer
    - Emergency Contact (toggle)
- Submit/Cancel buttons
- Form validation
```

### Show Page
```tsx
- Heading
- Guardian details card
  - Personal information
  - Contact information
  - Relationship and occupation
- Associated students section
  - List of students with this guardian
  - Link to student profiles
- Enrollment history
  - Table of enrollments for associated students
- Audit log section
- Action buttons (Edit, Delete, Back)
```

## Testing Requirements

- [ ] Test all CRUD operations work correctly
- [ ] Test validation errors display properly
- [ ] Test email uniqueness validation
- [ ] Test search functionality
- [ ] Test pagination works
- [ ] Test associated students display
- [ ] Test responsive design on mobile
- [ ] Test TypeScript compilation
- [ ] Test breadcrumb navigation
- [ ] Test toast notifications

## Dependencies

- [TICKET-025](./TICKET-025-fix-navigation-issues.md) - Should add navigation link after this is done

## Notes

- Guardians are linked to students via `guardian_students` pivot table
- Show page should display all associated students
- May need to add "Add Student" button on guardian show page
- Ensure proper authorization checks
- Consider adding bulk import for guardians (future enhancement)
- May need to add navigation link in Super Admin sidebar after completion

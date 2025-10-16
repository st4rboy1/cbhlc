# TICKET-026: Create Super Admin Grade Level Fees CRUD Pages

**Type:** Story
**Priority:** High
**Estimated Effort:** 1 day
**Status:** 🔴 To Do
**Epic:** Frontend Completion

## Description

Create all missing frontend pages for Super Admin Grade Level Fees CRUD operations. Backend routes and controllers exist, but React pages are missing.

## Missing Pages

1. **Index Page** - `/super-admin/grade-level-fees`
2. **Create Page** - `/super-admin/grade-level-fees/create`
3. **Show Page** - `/super-admin/grade-level-fees/{grade_level_fee}`
4. **Edit Page** - `/super-admin/grade-level-fees/{grade_level_fee}/edit`

## Existing Backend

**Controller:** `App\Http\Controllers\SuperAdmin\GradeLevelFeeController`
**Routes:** All CRUD routes exist
**Model:** `App\Models\GradeLevelFee`

**Note:** Registrar already has grade-level-fees pages that can be used as reference:
- `/home/tony/Desktop/cbhlc/resources/js/pages/registrar/grade-level-fees/`

## Acceptance Criteria

- [ ] Index page displays all grade level fees in a table
- [ ] Index page has "Create" button
- [ ] Index page has filter/search functionality
- [ ] Create page has form with all required fields
- [ ] Create page validates input before submission
- [ ] Show page displays all fee details
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
resources/js/pages/super-admin/grade-level-fees/
├── index.tsx          # List all grade level fees
├── create.tsx         # Create new fee
├── show.tsx           # View fee details
└── edit.tsx           # Edit existing fee
```

### Fields Required

Based on `GradeLevelFee` model:
- `grade_level` (enum: nursery, pre_kinder, kinder, grade_1-10, grade_11, grade_12)
- `school_year` (string, format: 2024-2025)
- `tuition_fee` (decimal)
- `miscellaneous_fee` (decimal)
- `other_fees` (json, optional)
- `total_fee` (calculated)
- `payment_terms` (json, optional)
- `is_active` (boolean)

### Reference Implementation

Use Registrar pages as reference:
- `resources/js/pages/registrar/grade-level-fees/index.tsx`
- `resources/js/pages/registrar/grade-level-fees/create.tsx`
- `resources/js/pages/registrar/grade-level-fees/edit.tsx`
- `resources/js/pages/registrar/grade-level-fees/show.tsx`

### Key Differences from Registrar

- Use `super-admin` routes instead of `registrar`
- May have additional admin-only actions (bulk operations, etc.)
- Use SuperAdmin layout
- Include audit log display on show page

## Component Structure

### Index Page
```tsx
- Heading with title and description
- Stats cards (total fees, active fees, etc.)
- Filters (school year, grade level, status)
- Data table with columns:
  - Grade Level
  - School Year
  - Tuition Fee
  - Miscellaneous Fee
  - Total Fee
  - Status
  - Actions (View, Edit, Delete)
- Pagination
- Create button
```

### Create/Edit Page
```tsx
- Heading
- Form with sections:
  - Basic Information (grade level, school year)
  - Fee Structure (tuition, miscellaneous, other fees)
  - Payment Terms (optional)
  - Status (is_active toggle)
- Submit/Cancel buttons
- Form validation
```

### Show Page
```tsx
- Heading
- Fee details card
- Fee breakdown table
- Payment terms display
- Audit log section
- Action buttons (Edit, Delete, Back)
```

## Testing Requirements

- [ ] Test all CRUD operations work correctly
- [ ] Test validation errors display properly
- [ ] Test pagination works
- [ ] Test filters work correctly
- [ ] Test responsive design on mobile
- [ ] Test TypeScript compilation
- [ ] Test breadcrumb navigation
- [ ] Test toast notifications

## Dependencies

- [TICKET-025](./TICKET-025-fix-navigation-issues.md) - Should add navigation link after this is done

## Notes

- Registrar already has working pages - can copy and adapt
- Focus on Super Admin specific features (audit logs, bulk actions)
- Ensure proper authorization checks
- May need to add navigation link in Super Admin sidebar after completion

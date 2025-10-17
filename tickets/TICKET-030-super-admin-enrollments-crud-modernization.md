# TICKET-030: Modernize Super Admin Enrollments CRUD Pages

**Type:** Enhancement
**Priority:** High
**Estimated Effort:** 1 day
**Status:** 🔴 To Do
**Epic:** Frontend Completion

## Description

Modernize the Super Admin Enrollments CRUD pages to match the pattern established in TICKET-026 through TICKET-029. The backend controller has all CRUD methods, but the frontend pages need updates:

1. Create missing `create.tsx` page
2. Modernize `index.tsx` to use shadcn data table instead of custom EnrollmentDashboard component
3. Ensure all pages follow the established patterns from other Super Admin CRUD pages

## Current State

**Backend:** ✅ Complete
- Controller: `App\Http\Controllers\SuperAdmin\EnrollmentController`
- All CRUD routes exist
- Model: `App\Models\Enrollment`
- Additional methods: `approve()`, `reject()`

**Frontend Issues:**
- ❌ `create.tsx` is missing (backend expects it at line 81)
- ⚠️ `index.tsx` uses old EnrollmentDashboard component with axios calls
- ✅ `edit.tsx` exists
- ✅ `show.tsx` exists

## Missing Page

### Create Page (`resources/js/pages/super-admin/enrollments/create.tsx`)

Backend controller returns:
```php
return Inertia::render('super-admin/enrollments/create', [
    'students' => $students,  // with guardians relationship
    'guardians' => $guardians,  // with user relationship
    'gradelevels' => \App\Enums\GradeLevel::cases(),
    'quarters' => \App\Enums\Quarter::cases(),
]);
```

## Index Page Modernization

Current `index.tsx` uses a custom `EnrollmentDashboard` component that:
- Fetches data via axios GET request
- Displays enrollment cards
- Has basic filtering
- **Problem:** Doesn't match the modern shadcn data table pattern used in other Super Admin pages

Should be modernized to:
- Use Inertia props instead of axios
- Use shadcn/ui data table component
- Follow the pattern from Invoices/Payments/Guardians pages
- Include "Create" button linking to `/super-admin/enrollments/create`
- Use proper pagination from Laravel

## Acceptance Criteria

### Create Page
- [ ] Create page displays form with all required fields
- [ ] Form includes student selection dropdown (with search)
- [ ] Form includes guardian selection dropdown (with search)
- [ ] Form includes grade level selection
- [ ] Form includes quarter selection
- [ ] Form includes school year input
- [ ] Form validates all inputs before submission
- [ ] Success creates enrollment and redirects to index
- [ ] Auto-approves enrollment (per backend logic line 109-111)
- [ ] Breadcrumbs are implemented
- [ ] Uses shadcn/ui components

### Index Page Modernization
- [ ] Replace EnrollmentDashboard component with modern shadcn data table
- [ ] Display enrollments in table format with columns:
  - Reference Number
  - Student Name
  - Guardian Name
  - Grade Level
  - School Year
  - Status (badge)
  - Actions (View, Edit, Delete)
- [ ] Include "Create Enrollment" button at top
- [ ] Add search functionality (student name, reference number)
- [ ] Add filter dropdowns (status, grade level, school year)
- [ ] Stats cards at top (Total, Pending, Approved, Rejected)
- [ ] Use Laravel pagination (not client-side)
- [ ] Use Inertia props (not axios calls)
- [ ] Match design pattern from other Super Admin CRUD pages

### Existing Pages
- [ ] Verify edit.tsx works correctly
- [ ] Verify show.tsx works correctly
- [ ] Ensure all pages use consistent styling

## Implementation Details

### Create Page Structure

```tsx
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

interface Student {
    id: number;
    student_id: string;
    first_name: string;
    last_name: string;
    guardians: Guardian[];
}

interface Guardian {
    id: number;
    first_name: string;
    last_name: string;
    user: {
        name: string;
        email: string;
    };
}

interface Props {
    students: Student[];
    guardians: Guardian[];
    gradelevels: Array<{ label: string; value: string }>;
    quarters: Array<{ label: string; value: string }>;
}

export default function SuperAdminEnrollmentsCreate({
    students,
    guardians,
    gradelevels,
    quarters
}: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Super Admin', href: '/super-admin/dashboard' },
        { title: 'Enrollments', href: '/super-admin/enrollments' },
        { title: 'Create Enrollment', href: '/super-admin/enrollments/create' },
    ];

    const { data, setData, post, processing, errors } = useForm({
        student_id: '',
        guardian_id: '',
        grade_level: '',
        quarter: '',
        school_year: new Date().getFullYear() + '-' + (new Date().getFullYear() + 1),
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/super-admin/enrollments');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Enrollment" />
            <div className="container mx-auto px-4 py-6">
                {/* Implementation details... */}
            </div>
        </AppLayout>
    );
}
```

### Index Page Modernization Structure

```tsx
import { DataTable } from '@/components/data-table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';

// Use columns pattern from other pages
// Use stats cards at top
// Use filters for search/status/grade/year
// Use Laravel pagination prop
```

### Fields Required for Create

Based on `StoreEnrollmentRequest` and backend logic:
- `student_id` (required, exists in students table)
- `guardian_id` (required, exists in guardians table)
- `grade_level` (required, enum value)
- `quarter` (required, enum value)
- `school_year` (required, format: YYYY-YYYY)
- Optional fields handled automatically by backend:
  - `reference_number` (auto-generated)
  - `status` (auto-approved for super admin)

## Testing Requirements

- [ ] Test create form validation
- [ ] Test successful enrollment creation
- [ ] Test student selection with search
- [ ] Test guardian selection with search
- [ ] Test index page loads with table
- [ ] Test index page filters work
- [ ] Test index page pagination works
- [ ] Test create button navigation
- [ ] Test edit button navigation from index
- [ ] Test view button navigation from index
- [ ] Test delete functionality (only for pending)
- [ ] Test responsive design on mobile
- [ ] Test TypeScript compilation
- [ ] Test breadcrumb navigation

## Files to Change

1. **Create:**
   - `resources/js/pages/super-admin/enrollments/create.tsx` (new file)

2. **Modernize:**
   - `resources/js/pages/super-admin/enrollments/index.tsx` (replace EnrollmentDashboard)

3. **Potentially Remove:**
   - `resources/js/components/enrollment-dashboard.tsx` (if only used by super-admin index)

4. **Review:**
   - `resources/js/pages/super-admin/enrollments/edit.tsx`
   - `resources/js/pages/super-admin/enrollments/show.tsx`

## Dependencies

- None (backend is complete)

## Notes

- Backend auto-approves enrollments created by super admin (line 109-111)
- Only pending enrollments can be deleted (line 189)
- EnrollmentService handles business logic
- Consider reusing components from other CRUD pages
- Match the pattern from TICKET-026, 027, 028, 029 for consistency
- The EnrollmentDashboard component may be used elsewhere - check before removing

## Reference Implementation

Use these as reference for patterns:
- `resources/js/pages/super-admin/payments/` - Data table pattern
- `resources/js/pages/super-admin/invoices/` - Create form pattern
- `resources/js/pages/super-admin/guardians/` - Stats cards pattern
- `resources/js/pages/super-admin/grade-level-fees/` - General CRUD pattern

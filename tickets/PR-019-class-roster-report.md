# PR #019: Class Roster Report

## Related Ticket

[TICKET-019: Class Roster Report](./TICKET-019-class-roster-report.md)

## Epic

[EPIC-003: Comprehensive Reporting System](./EPIC-003-reporting-system.md)

## Description

This PR implements class roster reporting functionality that generates detailed student lists organized by grade level with complete student and guardian contact information, optimized for printing and distribution to teaching staff.

## Changes Made

### Backend Controllers

- ✅ Added `classRoster()` method to `Registrar/ReportController.php`
- ✅ Added `classRoster()` method to `SuperAdmin/ReportController.php`
- ✅ Implemented query with eager loading of guardians
- ✅ Added sorting by name or student ID
- ✅ Implemented caching with 30-minute TTL

### Frontend Pages

- ✅ Created `resources/js/pages/registrar/reports/class-roster.tsx`
- ✅ Print-optimized table layout
- ✅ Grade level and school year selectors
- ✅ Sort functionality
- ✅ Print button with preview

### Print Styling

- ✅ A4 landscape print layout
- ✅ Print-specific CSS
- ✅ Hidden UI controls when printing
- ✅ Print header and footer

## Type of Change

- [x] New feature (full-stack)
- [ ] Bug fix
- [ ] Breaking change
- [ ] Documentation update

## Testing Checklist

### Backend Tests

- [ ] Registrar can view class roster
- [ ] Super Admin can view class roster
- [ ] Guardian/Student cannot access roster
- [ ] Students filtered by grade level correctly
- [ ] Guardian relationships loaded correctly
- [ ] Primary guardian identified correctly
- [ ] Sorting works (by name, student ID)
- [ ] Caching works
- [ ] Performance acceptable with 50+ students per grade

### Frontend Tests

- [ ] Page renders with roster
- [ ] Table displays all required columns
- [ ] Grade level selector works
- [ ] School year selector works
- [ ] Sort dropdown functions
- [ ] Print button works
- [ ] Print layout correct
- [ ] Responsive design
- [ ] Accessible

### Print Testing

- [ ] Print preview shows correctly
- [ ] A4 landscape orientation
- [ ] Headers/footers display
- [ ] All student data visible
- [ ] UI controls hidden
- [ ] Page breaks appropriate
- [ ] Works in Chrome, Firefox, Safari

## Verification Steps

```bash
# Run backend tests
./vendor/bin/sail pest tests/Feature/Registrar/ClassRosterTest.php

# Manual testing:
# 1. Login as Registrar
# 2. Navigate to /registrar/reports/class-roster
# 3. Select grade level
# 4. Select school year
# 5. Verify all students display
# 6. Check guardian information
# 7. Test sorting options
# 8. Click Print button
# 9. Verify print preview
# 10. Test actual printing
```

## Routes

```php
Route::middleware(['auth', 'role:registrar|administrator'])
    ->prefix('registrar/reports')
    ->name('registrar.reports.')
    ->group(function () {
        Route::get('/class-roster', [RegistrarReportController::class, 'classRoster'])
            ->name('class-roster');
    });

Route::middleware(['auth', 'role:super_admin'])
    ->prefix('super-admin/reports')
    ->name('super-admin.reports.')
    ->group(function () {
        Route::get('/class-roster', [SuperAdminReportController::class, 'classRoster'])
            ->name('class-roster');
    });
```

## Key Implementation Details

### Controller Query

```php
$students = Student::whereHas('enrollments', function ($query) use ($gradeLevel, $schoolYear) {
    $query->where('status', EnrollmentStatus::APPROVED)
        ->where('school_year', $schoolYear)
        ->where('grade_level', $gradeLevel);
})
->with([
    'enrollments' => function ($query) use ($schoolYear) {
        $query->where('school_year', $schoolYear);
    },
    'guardians' => function ($query) {
        $query->orderBy('pivot.is_primary', 'desc');
    },
])
->orderBy($sortBy)
->get();
```

### Print CSS

```css
@media print {
    @page {
        size: A4 landscape;
        margin: 1cm;
    }

    .print\\:hidden {
        display: none !important;
    }

    body {
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
}
```

## Dependencies

- Requires approved enrollments with guardian relationships
- shadcn/ui Select, Table, Button components
- Print CSS styling

## Breaking Changes

None

## Deployment Notes

- No database changes required
- Build frontend: `npm run build`
- Test print functionality in production

## Post-Merge Checklist

- [ ] Report accessible to authorized users
- [ ] Rosters display correctly
- [ ] Guardian data accurate
- [ ] Sorting works
- [ ] Print layout correct
- [ ] Print works in all browsers
- [ ] Caching improves performance
- [ ] Next ticket (TICKET-020) can begin

## Reviewer Notes

Please verify:

1. Guardian relationships loaded efficiently (no N+1)
2. Primary guardian logic correct
3. Print layout works across browsers
4. Student data complete and accurate
5. Sort options work correctly
6. Performance with large class sizes (30+ students)
7. Cache invalidation appropriate
8. No sensitive payment info in print view

## Performance Considerations

- Eager loading prevents N+1 queries
- Caching with 30-minute TTL
- Limited to one grade level at a time
- May need pagination for very large classes

## Security Considerations

- Only staff roles can access
- Payment status hidden in print view
- Guardian contact info visible (authorized use)
- Cache invalidates on student/enrollment changes

---

**Ticket:** #019
**Estimated Effort:** 1 day
**Actual Effort:** _[To be filled after completion]_

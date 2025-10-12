# PR #018: Student Demographics Report

## Related Ticket

[TICKET-018: Student Demographics Report](./TICKET-018-student-demographics-report.md)

## Epic

[EPIC-003: Comprehensive Reporting System](./EPIC-003-reporting-system.md)

## Description

This PR implements comprehensive student demographics reporting with breakdown by grade level, gender distribution, age ranges, geographic distribution, and new vs. returning student analysis for planning and compliance purposes.

## Changes Made

### Backend Controllers

- ✅ Added `studentDemographics()` method to `Registrar/ReportController.php`
- ✅ Added `studentDemographics()` method to `SuperAdmin/ReportController.php`
- ✅ Implemented age calculation helper methods
- ✅ Implemented new vs. returning student detection logic
- ✅ Added location parsing for distribution
- ✅ Implemented caching with 1-hour TTL

### Frontend Pages

- ✅ Created `resources/js/pages/registrar/reports/student-demographics.tsx`
- ✅ Summary cards for key metrics
- ✅ Gender distribution with progress bars
- ✅ Age distribution table
- ✅ Grade level breakdown table with gender split

### Database

- ✅ Added index on `students.birth_date` for age calculations

## Type of Change

- [x] New feature (full-stack)
- [ ] Bug fix
- [ ] Breaking change
- [ ] Documentation update

## Testing Checklist

### Backend Tests

- [ ] Registrar can view student demographics
- [ ] Super Admin can view student demographics
- [ ] Guardian/Student cannot access report
- [ ] Age distribution calculates correctly
- [ ] Gender distribution accurate
- [ ] New vs. returning detection works
- [ ] Filters apply correctly
- [ ] Caching works
- [ ] Performance acceptable with 1000+ students

### Frontend Tests

- [ ] Page renders with demographics
- [ ] Summary cards display correctly
- [ ] Gender distribution visualized properly
- [ ] Age ranges accurate
- [ ] Grade level breakdown correct
- [ ] Filters work
- [ ] Responsive design
- [ ] Accessible

## Verification Steps

```bash
# Run backend tests
./vendor/bin/sail pest tests/Feature/Registrar/StudentDemographicsTest.php

# Manual testing:
# 1. Login as Registrar
# 2. Navigate to /registrar/reports/student-demographics
# 3. Verify summary cards display
# 4. Test school year filter
# 5. Test grade level filter
# 6. Check gender distribution
# 7. Verify age ranges
# 8. Check new vs. returning calculation
# 9. Test on mobile device
```

## Routes

```php
Route::middleware(['auth', 'role:registrar|administrator'])
    ->prefix('registrar/reports')
    ->name('registrar.reports.')
    ->group(function () {
        Route::get('/student-demographics', [RegistrarReportController::class, 'studentDemographics'])
            ->name('student-demographics');
    });

Route::middleware(['auth', 'role:super_admin'])
    ->prefix('super-admin/reports')
    ->name('super-admin.reports.')
    ->group(function () {
        Route::get('/student-demographics', [SuperAdminReportController::class, 'studentDemographics'])
            ->name('student-demographics');
    });
```

## Key Implementation Details

### Age Distribution Calculation

```php
private function getAgeDistribution($query, $filters)
{
    $students = $query->get();

    $ageRanges = [
        '3-5' => 0,
        '6-8' => 0,
        '9-11' => 0,
        '12-14' => 0,
        '15-17' => 0,
        '18+' => 0,
    ];

    foreach ($students as $student) {
        $age = Carbon::parse($student->birth_date)->age;

        if ($age >= 3 && $age <= 5) $ageRanges['3-5']++;
        elseif ($age >= 6 && $age <= 8) $ageRanges['6-8']++;
        elseif ($age >= 9 && $age <= 11) $ageRanges['9-11']++;
        elseif ($age >= 12 && $age <= 14) $ageRanges['12-14']++;
        elseif ($age >= 15 && $age <= 17) $ageRanges['15-17']++;
        else $ageRanges['18+']++;
    }

    return $ageRanges;
}
```

### New vs. Returning Students

```php
private function getNewVsReturningStudents($query, $schoolYear)
{
    $students = $query->get();

    $new = 0;
    $returning = 0;

    foreach ($students as $student) {
        $previousEnrollments = $student->enrollments()
            ->where('school_year', '<', $schoolYear)
            ->where('status', EnrollmentStatus::APPROVED)
            ->count();

        if ($previousEnrollments > 0) {
            $returning++;
        } else {
            $new++;
        }
    }

    return [
        'new' => $new,
        'returning' => $returning,
        'percentage_new' => $new / ($new + $returning) * 100,
    ];
}
```

## Database Migration

```php
Schema::table('students', function (Blueprint $table) {
    $table->index('birth_date');
});
```

## Dependencies

- Requires approved enrollments
- Uses Carbon for age calculations
- shadcn/ui Card, Table components

## Breaking Changes

None

## Deployment Notes

- Run migration: `php artisan migrate`
- Clear cache: `php artisan cache:clear`
- Build frontend: `npm run build`

## Post-Merge Checklist

- [ ] Report accessible to authorized users
- [ ] Age calculations accurate
- [ ] Gender distribution correct
- [ ] New vs. returning logic works
- [ ] Filters apply correctly
- [ ] Caching improves performance
- [ ] Responsive design works
- [ ] Next ticket (TICKET-019) can begin

## Reviewer Notes

Please verify:

1. Age calculation handles edge cases (birthdays today, leap years)
2. New vs. returning logic is accurate
3. Gender enum values display correctly
4. Performance acceptable with N+1 prevention
5. Cache invalidation works properly
6. Student privacy maintained
7. Filters don't cause query issues

## Performance Considerations

- Index on birth_date for age calculations
- Eager loading of enrollments relationship
- Caching with 1-hour TTL
- May need optimization for very large student counts (1000+)

## Security Considerations

- Only authorized staff can access
- Student PII handled carefully
- No export of sensitive data without proper authorization
- Cache keys include user role for isolation

---

**Ticket:** #018
**Estimated Effort:** 1.5 days
**Actual Effort:** _[To be filled after completion]_

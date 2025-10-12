# PR #021: Financial Reports

## Related Ticket

[TICKET-021: Financial Reports](./TICKET-021-financial-reports.md)

## Epic

[EPIC-003: Comprehensive Reporting System](./EPIC-003-reporting-system.md)

## Description

This PR implements comprehensive financial reporting showing total revenue per school year, payment status summaries, outstanding balances, payment trends by grade level, and aging reports for overdue payments with accurate money calculations.

## Changes Made

### Backend Controllers

- ✅ Added `financial()` method to `Registrar/ReportController.php`
- ✅ Added `financial()` method to `SuperAdmin/ReportController.php`
- ✅ Implemented revenue calculations from invoices and payments
- ✅ Implemented payment status breakdown
- ✅ Implemented outstanding balances query
- ✅ Implemented aging report with 30/60/90-day buckets
- ✅ Added caching with 30-minute TTL

### Frontend Pages

- ✅ Created `resources/js/pages/registrar/reports/financial.tsx`
- ✅ Summary cards for key financial metrics
- ✅ Payment status breakdown visualization
- ✅ Aging report table
- ✅ Revenue by grade level table
- ✅ Outstanding balances detail table

### Money Formatting

- ✅ PHP money formatting (cents to peso)
- ✅ JavaScript money formatting (Intl.NumberFormat)
- ✅ Proper handling of integer math

## Type of Change

- [x] New feature (full-stack)
- [ ] Bug fix
- [ ] Breaking change
- [ ] Documentation update

## Testing Checklist

### Backend Tests

- [ ] Registrar can view financial reports
- [ ] Super Admin can view financial reports
- [ ] Guardian/Student cannot access reports
- [ ] Revenue calculations accurate
- [ ] Payment status breakdown correct
- [ ] Outstanding balances calculated correctly
- [ ] Aging report buckets correct
- [ ] Collection rate calculated correctly
- [ ] Money math uses integers (no floats)
- [ ] Division by zero handled

### Frontend Tests

- [ ] Page renders with financial data
- [ ] Summary cards display correct values
- [ ] Money formatted correctly (PHP peso)
- [ ] Payment status visualization works
- [ ] Aging report table displays
- [ ] Revenue by grade table displays
- [ ] Outstanding balances table displays
- [ ] Filters apply correctly
- [ ] Responsive design

### Calculation Tests

- [ ] Total billed sum correct
- [ ] Total paid sum correct
- [ ] Outstanding calculated correctly
- [ ] Collection rate percentage correct
- [ ] Aging buckets assigned correctly
- [ ] Days overdue calculated correctly

## Verification Steps

```bash
# Run backend tests
./vendor/bin/sail pest tests/Feature/Registrar/FinancialReportTest.php

# Manual testing:
# 1. Login as Registrar
# 2. Navigate to /registrar/reports/financial
# 3. Verify summary cards display
# 4. Check total billed matches invoice totals
# 5. Check total collected matches payment totals
# 6. Verify outstanding balance calculation
# 7. Check collection rate percentage
# 8. Verify payment status breakdown
# 9. Review aging report buckets
# 10. Check revenue by grade level
# 11. Review outstanding balances detail
# 12. Test filters (school year, grade, status)
```

## Routes

```php
Route::middleware(['auth', 'role:registrar|administrator'])
    ->prefix('registrar/reports')
    ->name('registrar.reports.')
    ->group(function () {
        Route::get('/financial', [RegistrarReportController::class, 'financial'])
            ->name('financial');
    });

Route::middleware(['auth', 'role:super_admin'])
    ->prefix('super-admin/reports')
    ->name('super-admin.reports.')
    ->group(function () {
        Route::get('/financial', [SuperAdminReportController::class, 'financial'])
            ->name('financial');
    });
```

## Key Implementation Details

### Financial Summary Calculation

```php
private function calculateFinancialSummary($enrollments)
{
    $totalBilled = 0;
    $totalPaid = 0;
    $totalOutstanding = 0;

    foreach ($enrollments as $enrollment) {
        $billed = $enrollment->invoices->sum('total_amount');
        $paid = $enrollment->payments->sum('amount');

        $totalBilled += $billed;
        $totalPaid += $paid;
        $totalOutstanding += ($billed - $paid);
    }

    return [
        'total_billed' => $totalBilled,
        'total_paid' => $totalPaid,
        'total_outstanding' => $totalOutstanding,
        'collection_rate' => $totalBilled > 0 ? ($totalPaid / $totalBilled) * 100 : 0,
        'total_enrollments' => $enrollments->count(),
    ];
}
```

### Aging Report Implementation

```php
private function getAgingReport($enrollments)
{
    $aging = [
        'current' => ['count' => 0, 'amount' => 0],
        '30_days' => ['count' => 0, 'amount' => 0],
        '60_days' => ['count' => 0, 'amount' => 0],
        '90_plus_days' => ['count' => 0, 'amount' => 0],
    ];

    foreach ($enrollments as $enrollment) {
        $billed = $enrollment->invoices->sum('total_amount');
        $paid = $enrollment->payments->sum('amount');
        $outstanding = $billed - $paid;

        if ($outstanding <= 0) {
            continue;
        }

        $lastInvoice = $enrollment->invoices->sortByDesc('due_date')->first();
        $daysOverdue = $lastInvoice ? now()->diffInDays($lastInvoice->due_date, false) : 0;

        if ($daysOverdue <= 0) {
            $aging['current']['count']++;
            $aging['current']['amount'] += $outstanding;
        } elseif ($daysOverdue <= 30) {
            $aging['30_days']['count']++;
            $aging['30_days']['amount'] += $outstanding;
        } elseif ($daysOverdue <= 60) {
            $aging['60_days']['count']++;
            $aging['60_days']['amount'] += $outstanding;
        } else {
            $aging['90_plus_days']['count']++;
            $aging['90_plus_days']['amount'] += $outstanding;
        }
    }

    return $aging;
}
```

### Frontend Money Formatting

```tsx
const formatMoney = (amount: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount / 100); // Convert cents to pesos
};
```

### Summary Cards Display

```tsx
<div className="grid grid-cols-1 gap-4 md:grid-cols-4">
    <Card>
        <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium text-muted-foreground">Total Billed</CardTitle>
        </CardHeader>
        <CardContent>
            <div className="text-2xl font-bold">{formatMoney(report.summary.total_billed)}</div>
        </CardContent>
    </Card>

    <Card>
        <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium text-muted-foreground">Total Collected</CardTitle>
        </CardHeader>
        <CardContent>
            <div className="text-2xl font-bold text-green-600">{formatMoney(report.summary.total_paid)}</div>
        </CardContent>
    </Card>

    <Card>
        <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium text-muted-foreground">Outstanding</CardTitle>
        </CardHeader>
        <CardContent>
            <div className="text-2xl font-bold text-red-600">{formatMoney(report.summary.total_outstanding)}</div>
        </CardContent>
    </Card>

    <Card>
        <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium text-muted-foreground">Collection Rate</CardTitle>
        </CardHeader>
        <CardContent>
            <div className="text-2xl font-bold">{report.summary.collection_rate.toFixed(1)}%</div>
        </CardContent>
    </Card>
</div>
```

## Dependencies

- Requires existing `Invoice` and `Payment` models with money casting
- Uses Laravel's Money casting for accurate calculations
- shadcn/ui Card, Table, Badge components
- Intl.NumberFormat for PHP currency formatting

## Breaking Changes

None

## Deployment Notes

- No database changes required
- Ensure money casting is configured correctly
- Build frontend: `npm run build`
- Clear cache: `php artisan cache:clear`

## Post-Merge Checklist

- [ ] Report accessible to authorized users
- [ ] Financial calculations accurate
- [ ] Money formatting correct
- [ ] Payment status breakdown displays
- [ ] Aging report correct
- [ ] Revenue by grade level accurate
- [ ] Outstanding balances correct
- [ ] Collection rate calculates properly
- [ ] Filters work correctly
- [ ] Epic #003 complete! All reporting features implemented

## Reviewer Notes

Please verify:

1. All money calculations use integer math (cents)
2. Collection rate handles division by zero
3. Aging buckets follow accounting standards
4. Outstanding balances calculated correctly
5. Money formatting displays PHP correctly
6. Performance acceptable with many invoices/payments
7. No floating point precision errors
8. Cache invalidation appropriate

## Performance Considerations

- Eager loading of invoices and payments
- Caching with 30-minute TTL
- Outstanding balances list limited to top 20 in UI
- Full list available in export
- May need optimization for 1000+ enrollments

## Security Considerations

- Only staff roles can access
- Financial data restricted by role
- No export without proper authorization
- Cache keys include role for isolation
- Audit log for financial report access

## Financial Data Accuracy

- All amounts stored as integers (cents)
- Summation uses integer addition
- Percentages calculated last to avoid rounding errors
- Division by zero checked before calculation
- Outstanding balances = billed - paid (accurate to cent)

---

**Ticket:** #021
**Estimated Effort:** 1.5 days
**Actual Effort:** _[To be filled after completion]_
**Epic Status:** ✅ COMPLETE - Comprehensive Reporting System (EPIC-003)

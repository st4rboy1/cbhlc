# TICKET-021: Financial Reports

## Epic

[EPIC-003: Comprehensive Reporting System](./EPIC-003-reporting-system.md)

## Priority

Medium (Could Have)

## User Story

As a registrar or administrator, I want to generate financial reports showing revenue, payment status, and outstanding balances so that I can track school finances and identify accounts requiring follow-up.

## Related SRS Requirements

- **FR-7.1:** System shall generate enrollment statistics reports (includes financial data)
- **FR-7.4:** System shall support report filtering by date, grade, and status

## Description

Implement comprehensive financial reporting showing total revenue per school year, payment status summaries, outstanding balances, payment trends by grade level, and aging reports for overdue payments.

## Acceptance Criteria

- ✅ Registrar can view financial summary dashboard
- ✅ Report shows total revenue per school year
- ✅ Report displays payment status breakdown (paid, partial, unpaid)
- ✅ Report shows outstanding balances
- ✅ Report displays revenue by grade level
- ✅ Report includes aging report for overdue payments
- ✅ Filters work for date range, school year, grade level, payment status
- ✅ Financial data is calculated accurately from invoices and payments
- ✅ Report displays money in proper format (Philippine Peso)

## Technical Requirements

### Backend

1. Add `financial()` method to report controllers
2. Aggregate data from `enrollments`, `invoices`, and `payments` tables
3. Calculate totals, outstanding balances, and payment rates
4. Implement aging buckets (current, 30 days, 60 days, 90+ days overdue)
5. Add caching with proper invalidation
6. Ensure money calculations use integer math (cents)

### Frontend

1. Create `/resources/js/pages/registrar/reports/financial.tsx`
2. Display financial summary cards
3. Show payment status breakdown chart
4. Create outstanding balances table
5. Display aging report
6. Add revenue trends chart

### Database

- No new tables required
- Uses existing `enrollments`, `invoices`, `payments` tables
- Requires proper money casting (already implemented)

## Implementation Details

### Controller Method

```php
public function financial(Request $request)
{
    $schoolYear = $request->input('school_year', EnrollmentPeriod::active()->first()?->school_year);
    $gradeLevel = $request->input('grade_level');
    $paymentStatus = $request->input('payment_status');

    $cacheKey = "financial-report-{$schoolYear}-{$gradeLevel}-{$paymentStatus}";

    $report = Cache::tags(['reports', 'financial'])
        ->remember($cacheKey, 1800, function () use ($schoolYear, $gradeLevel, $paymentStatus) {
            $query = Enrollment::where('school_year', $schoolYear)
                ->where('status', EnrollmentStatus::APPROVED);

            if ($gradeLevel) {
                $query->where('grade_level', $gradeLevel);
            }

            if ($paymentStatus) {
                $query->where('payment_status', $paymentStatus);
            }

            $enrollments = $query->with(['invoices', 'payments'])->get();

            return [
                'summary' => $this->calculateFinancialSummary($enrollments),
                'by_grade_level' => $this->calculateRevenueByGradeLevel($enrollments),
                'payment_status_breakdown' => $this->getPaymentStatusBreakdown($enrollments),
                'outstanding_balances' => $this->getOutstandingBalances($enrollments),
                'aging_report' => $this->getAgingReport($enrollments),
                'payment_trends' => $this->getPaymentTrends($schoolYear),
            ];
        });

    return Inertia::render('Registrar/Reports/Financial', [
        'report' => $report,
        'schoolYear' => $schoolYear,
        'gradeLevel' => $gradeLevel,
        'paymentStatus' => $paymentStatus,
        'schoolYears' => EnrollmentPeriod::pluck('school_year'),
        'gradeLevels' => GradeLevel::all(),
    ]);
}

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

private function calculateRevenueByGradeLevel($enrollments)
{
    $revenueByGrade = [];

    foreach ($enrollments->groupBy('grade_level') as $gradeLevel => $gradeEnrollments) {
        $billed = $gradeEnrollments->sum(fn($e) => $e->invoices->sum('total_amount'));
        $paid = $gradeEnrollments->sum(fn($e) => $e->payments->sum('amount'));

        $revenueByGrade[] = [
            'grade_level' => $gradeLevel,
            'total_billed' => $billed,
            'total_paid' => $paid,
            'outstanding' => $billed - $paid,
            'enrollment_count' => $gradeEnrollments->count(),
        ];
    }

    return collect($revenueByGrade)->sortBy('grade_level')->values();
}

private function getPaymentStatusBreakdown($enrollments)
{
    return [
        'paid' => $enrollments->where('payment_status', PaymentStatus::PAID)->count(),
        'partial' => $enrollments->where('payment_status', PaymentStatus::PARTIAL)->count(),
        'unpaid' => $enrollments->where('payment_status', PaymentStatus::UNPAID)->count(),
        'overdue' => $enrollments->where('payment_status', PaymentStatus::OVERDUE)->count(),
    ];
}

private function getOutstandingBalances($enrollments)
{
    return $enrollments
        ->filter(function ($enrollment) {
            $billed = $enrollment->invoices->sum('total_amount');
            $paid = $enrollment->payments->sum('amount');
            return $billed > $paid;
        })
        ->map(function ($enrollment) {
            $billed = $enrollment->invoices->sum('total_amount');
            $paid = $enrollment->payments->sum('amount');
            $outstanding = $billed - $paid;

            $lastInvoice = $enrollment->invoices->sortByDesc('due_date')->first();
            $daysOverdue = $lastInvoice ? now()->diffInDays($lastInvoice->due_date, false) : 0;

            return [
                'enrollment_id' => $enrollment->enrollment_id,
                'student_name' => $enrollment->student->full_name,
                'grade_level' => $enrollment->grade_level->value,
                'total_billed' => $billed,
                'total_paid' => $paid,
                'outstanding' => $outstanding,
                'days_overdue' => $daysOverdue > 0 ? $daysOverdue : 0,
                'last_payment_date' => $enrollment->payments->sortByDesc('payment_date')->first()?->payment_date,
            ];
        })
        ->sortByDesc('outstanding')
        ->values();
}

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

private function getPaymentTrends(string $schoolYear)
{
    // Get payments grouped by month for the school year
    $startDate = Carbon::parse($schoolYear);
    $endDate = $startDate->copy()->addYear();

    $payments = Payment::whereBetween('payment_date', [$startDate, $endDate])
        ->selectRaw('DATE_FORMAT(payment_date, "%Y-%m") as month, SUM(amount) as total')
        ->groupBy('month')
        ->orderBy('month')
        ->get();

    return $payments->map(function ($payment) {
        return [
            'month' => Carbon::parse($payment->month)->format('M Y'),
            'total' => $payment->total,
        ];
    });
}
```

### Frontend Page

```tsx
export default function FinancialReports({ report, schoolYear, gradeLevel, paymentStatus, schoolYears, gradeLevels }) {
    const [localFilters, setLocalFilters] = useState({ schoolYear, gradeLevel, paymentStatus });

    const formatMoney = (amount: number) => {
        return new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
        }).format(amount / 100);
    };

    return (
        <AppLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold">Financial Reports</h1>
                    <ExportButton reportType="financial" currentFilters={localFilters} />
                </div>

                <ReportFilter
                    filters={localFilters}
                    onFilterChange={setLocalFilters}
                    schoolYears={schoolYears}
                    gradeLevels={gradeLevels}
                    showPaymentStatus
                />

                {/* Summary Cards */}
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

                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {/* Payment Status Breakdown */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Payment Status Breakdown</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <div className="h-4 w-4 rounded-full bg-green-500" />
                                        <span>Paid</span>
                                    </div>
                                    <span className="font-medium">{report.payment_status_breakdown.paid}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <div className="h-4 w-4 rounded-full bg-yellow-500" />
                                        <span>Partial</span>
                                    </div>
                                    <span className="font-medium">{report.payment_status_breakdown.partial}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <div className="h-4 w-4 rounded-full bg-gray-500" />
                                        <span>Unpaid</span>
                                    </div>
                                    <span className="font-medium">{report.payment_status_breakdown.unpaid}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <div className="h-4 w-4 rounded-full bg-red-500" />
                                        <span>Overdue</span>
                                    </div>
                                    <span className="font-medium">{report.payment_status_breakdown.overdue}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Aging Report */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Aging Report</CardTitle>
                            <CardDescription>Outstanding balances by age</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Age</TableHead>
                                        <TableHead className="text-right">Count</TableHead>
                                        <TableHead className="text-right">Amount</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow>
                                        <TableCell>Current</TableCell>
                                        <TableCell className="text-right">{report.aging_report.current.count}</TableCell>
                                        <TableCell className="text-right">{formatMoney(report.aging_report.current.amount)}</TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>1-30 Days</TableCell>
                                        <TableCell className="text-right">{report.aging_report['30_days'].count}</TableCell>
                                        <TableCell className="text-right">{formatMoney(report.aging_report['30_days'].amount)}</TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>31-60 Days</TableCell>
                                        <TableCell className="text-right">{report.aging_report['60_days'].count}</TableCell>
                                        <TableCell className="text-right">{formatMoney(report.aging_report['60_days'].amount)}</TableCell>
                                    </TableRow>
                                    <TableRow className="font-medium text-red-600">
                                        <TableCell>90+ Days</TableCell>
                                        <TableCell className="text-right">{report.aging_report['90_plus_days'].count}</TableCell>
                                        <TableCell className="text-right">{formatMoney(report.aging_report['90_plus_days'].amount)}</TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </div>

                {/* Revenue by Grade Level */}
                <Card>
                    <CardHeader>
                        <CardTitle>Revenue by Grade Level</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Grade Level</TableHead>
                                    <TableHead className="text-right">Enrollments</TableHead>
                                    <TableHead className="text-right">Billed</TableHead>
                                    <TableHead className="text-right">Collected</TableHead>
                                    <TableHead className="text-right">Outstanding</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {report.by_grade_level.map((grade) => (
                                    <TableRow key={grade.grade_level}>
                                        <TableCell>{grade.grade_level}</TableCell>
                                        <TableCell className="text-right">{grade.enrollment_count}</TableCell>
                                        <TableCell className="text-right">{formatMoney(grade.total_billed)}</TableCell>
                                        <TableCell className="text-right">{formatMoney(grade.total_paid)}</TableCell>
                                        <TableCell className="text-right">{formatMoney(grade.outstanding)}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                {/* Outstanding Balances Detail */}
                <Card>
                    <CardHeader>
                        <CardTitle>Outstanding Balances Detail</CardTitle>
                        <CardDescription>Accounts with unpaid balances</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Enrollment ID</TableHead>
                                    <TableHead>Student Name</TableHead>
                                    <TableHead>Grade</TableHead>
                                    <TableHead className="text-right">Billed</TableHead>
                                    <TableHead className="text-right">Paid</TableHead>
                                    <TableHead className="text-right">Outstanding</TableHead>
                                    <TableHead className="text-right">Days Overdue</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {report.outstanding_balances.slice(0, 20).map((balance) => (
                                    <TableRow key={balance.enrollment_id}>
                                        <TableCell className="font-mono text-xs">{balance.enrollment_id}</TableCell>
                                        <TableCell>{balance.student_name}</TableCell>
                                        <TableCell>{balance.grade_level}</TableCell>
                                        <TableCell className="text-right">{formatMoney(balance.total_billed)}</TableCell>
                                        <TableCell className="text-right">{formatMoney(balance.total_paid)}</TableCell>
                                        <TableCell className="text-right font-medium">{formatMoney(balance.outstanding)}</TableCell>
                                        <TableCell className="text-right">
                                            {balance.days_overdue > 0 ? (
                                                <Badge variant="destructive">{balance.days_overdue} days</Badge>
                                            ) : (
                                                <span className="text-muted-foreground">Current</span>
                                            )}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
```

## Testing Requirements

### Feature Tests

```php
test('registrar can view financial reports', function () {
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    $enrollment = Enrollment::factory()->create([
        'status' => EnrollmentStatus::APPROVED,
    ]);

    Invoice::factory()->create([
        'enrollment_id' => $enrollment->id,
        'total_amount' => 50000, // PHP 500.00
    ]);

    Payment::factory()->create([
        'enrollment_id' => $enrollment->id,
        'amount' => 25000, // PHP 250.00
    ]);

    actingAs($registrar)
        ->get(route('registrar.reports.financial'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Registrar/Reports/Financial')
            ->has('report.summary')
            ->where('report.summary.total_billed', 50000)
            ->where('report.summary.total_paid', 25000)
            ->where('report.summary.total_outstanding', 25000)
        );
});

test('financial report calculates aging correctly', function () {
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    $enrollment = Enrollment::factory()->create();

    // Create overdue invoice
    Invoice::factory()->create([
        'enrollment_id' => $enrollment->id,
        'total_amount' => 50000,
        'due_date' => now()->subDays(45),
    ]);

    $response = actingAs($registrar)
        ->get(route('registrar.reports.financial'));

    $aging = $response->viewData('report')['aging_report'];

    expect($aging['60_days']['count'])->toBe(1);
    expect($aging['60_days']['amount'])->toBe(50000);
});
```

## Routes

```php
Route::middleware(['auth', 'role:registrar|administrator'])->prefix('registrar/reports')->name('registrar.reports.')->group(function () {
    Route::get('/financial', [RegistrarReportController::class, 'financial'])->name('financial');
});

Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin/reports')->name('super-admin.reports.')->group(function () {
    Route::get('/financial', [SuperAdminReportController::class, 'financial'])->name('financial');
});
```

## Dependencies

- Requires existing `Invoice` and `Payment` models with money casting
- Uses Laravel's Money casting for accurate calculations
- shadcn/ui Card, Table, Badge components

## Estimated Effort

**1.5 days**

## Implementation Checklist

- [ ] Add `financial()` method to both controllers
- [ ] Implement financial summary calculations
- [ ] Implement revenue by grade level aggregation
- [ ] Implement payment status breakdown
- [ ] Implement outstanding balances query
- [ ] Implement aging report with buckets
- [ ] Implement payment trends calculation
- [ ] Add caching with proper tags
- [ ] Create frontend page component
- [ ] Display summary cards with formatted money
- [ ] Create payment status breakdown visualization
- [ ] Create aging report table
- [ ] Create revenue by grade level table
- [ ] Create outstanding balances detail table
- [ ] Write feature tests for calculations
- [ ] Test money formatting
- [ ] Test aging calculations
- [ ] Test with various payment scenarios

## Notes

- All money calculations must use integer math (cents) to avoid floating point errors
- Collection rate percentage should handle division by zero
- Aging buckets follow common accounting practice (30/60/90 days)
- Consider adding payment trend chart in future
- Outstanding balances list limited to top 20 for performance
- Full list available in Excel export

# TICKET-018: Student Demographics Report

## Epic

[EPIC-003: Comprehensive Reporting System](./EPIC-003-reporting-system.md)

## Priority

Medium (Could Have)

## User Story

As a registrar or administrator, I want to generate student demographics reports so that I can analyze student distribution by grade level, gender, age, and location for planning and compliance purposes.

## Related SRS Requirements

- **FR-7.2:** System shall produce student demographic reports
- **FR-7.4:** System shall support report filtering by date, grade, and status

## Description

Implement comprehensive student demographics reporting with breakdown by grade level, gender distribution, age ranges, geographic distribution, and new vs. returning student analysis.

## Acceptance Criteria

- ✅ Registrar can view student demographics dashboard
- ✅ Report shows total students by grade level
- ✅ Report displays gender distribution
- ✅ Report shows age distribution with ranges
- ✅ Report displays address/location distribution
- ✅ Report distinguishes new vs. returning students
- ✅ Filters work for school year, grade level, age range
- ✅ Data visualization is clear and informative
- ✅ Report loads within 3 seconds

## Technical Requirements

### Backend

1. Add `studentDemographics()` method to report controllers
2. Implement queries for:
    - Students grouped by grade level
    - Gender distribution
    - Age calculation and grouping
    - Address parsing for location distribution
    - New vs. returning student detection
3. Add caching with 1-hour TTL
4. Implement filter logic

### Frontend

1. Create `/resources/js/pages/registrar/reports/student-demographics.tsx`
2. Display demographic breakdown tables
3. Add visualization cards for key metrics
4. Implement filter interface
5. Create responsive layout

### Database

- No new tables required
- Consider adding `returning_student` boolean field to `students` table (optional)
- Add index on `students.birth_date` for age calculations

## Implementation Details

### Controller Method

```php
public function studentDemographics(Request $request)
{
    $filters = $request->only(['school_year', 'grade_level', 'age_from', 'age_to']);

    $cacheKey = 'student-demographics-' . md5(json_encode($filters));

    $demographics = Cache::tags(['reports', 'students'])
        ->remember($cacheKey, 3600, function () use ($filters) {
            $query = Student::query()
                ->whereHas('enrollments', function ($q) use ($filters) {
                    $q->where('status', EnrollmentStatus::APPROVED);

                    if (!empty($filters['school_year'])) {
                        $q->where('school_year', $filters['school_year']);
                    }

                    if (!empty($filters['grade_level'])) {
                        $q->where('grade_level', $filters['grade_level']);
                    }
                });

            return [
                'total_students' => $query->count(),
                'by_grade_level' => $this->getStudentsByGradeLevel($query),
                'gender_distribution' => $this->getGenderDistribution($query),
                'age_distribution' => $this->getAgeDistribution($query, $filters),
                'location_distribution' => $this->getLocationDistribution($query),
                'new_vs_returning' => $this->getNewVsReturningStudents($query, $filters['school_year'] ?? null),
            ];
        });

    return Inertia::render('Registrar/Reports/StudentDemographics', [
        'demographics' => $demographics,
        'filters' => $filters,
        'schoolYears' => EnrollmentPeriod::pluck('school_year'),
        'gradeLevels' => GradeLevel::all(),
    ]);
}

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

### Frontend Page

```tsx
export default function StudentDemographics({ demographics, filters, schoolYears, gradeLevels }) {
    const [localFilters, setLocalFilters] = useState(filters);

    return (
        <AppLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold">Student Demographics</h1>
                </div>

                <ReportFilter filters={localFilters} onFilterChange={setLocalFilters} schoolYears={schoolYears} gradeLevels={gradeLevels} />

                {/* Summary Cards */}
                <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <ReportSummaryCard title="Total Students" value={demographics.total_students} />
                    <ReportSummaryCard
                        title="New Students"
                        value={`${demographics.new_vs_returning.new} (${demographics.new_vs_returning.percentage_new.toFixed(1)}%)`}
                    />
                    <ReportSummaryCard title="Returning Students" value={demographics.new_vs_returning.returning} />
                </div>

                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {/* Gender Distribution */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Gender Distribution</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2">
                                {Object.entries(demographics.gender_distribution).map(([gender, count]) => (
                                    <div key={gender} className="flex items-center justify-between">
                                        <span className="font-medium">{gender}</span>
                                        <div className="flex items-center gap-2">
                                            <div className="h-2 w-32 rounded-full bg-gray-200">
                                                <div
                                                    className="h-2 rounded-full bg-primary"
                                                    style={{ width: `${(count / demographics.total_students) * 100}%` }}
                                                />
                                            </div>
                                            <span className="text-sm text-muted-foreground">{count}</span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Age Distribution */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Age Distribution</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Age Range</TableHead>
                                        <TableHead className="text-right">Students</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {Object.entries(demographics.age_distribution).map(([range, count]) => (
                                        <TableRow key={range}>
                                            <TableCell>{range} years</TableCell>
                                            <TableCell className="text-right">{count}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </div>

                {/* Grade Level Distribution */}
                <Card>
                    <CardHeader>
                        <CardTitle>Students by Grade Level</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Grade Level</TableHead>
                                    <TableHead className="text-right">Male</TableHead>
                                    <TableHead className="text-right">Female</TableHead>
                                    <TableHead className="text-right">Total</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {demographics.by_grade_level.map((grade) => (
                                    <TableRow key={grade.level}>
                                        <TableCell>{grade.level_name}</TableCell>
                                        <TableCell className="text-right">{grade.male}</TableCell>
                                        <TableCell className="text-right">{grade.female}</TableCell>
                                        <TableCell className="text-right font-medium">{grade.total}</TableCell>
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
test('registrar can view student demographics', function () {
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    Student::factory()->count(30)->create();

    actingAs($registrar)
        ->get(route('registrar.reports.student-demographics'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Registrar/Reports/StudentDemographics')
            ->has('demographics')
            ->has('demographics.total_students')
            ->has('demographics.gender_distribution')
        );
});

test('demographics report calculates age distribution correctly', function () {
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    // Create students with specific ages
    Student::factory()->create(['birth_date' => now()->subYears(5)]);
    Student::factory()->create(['birth_date' => now()->subYears(10)]);
    Student::factory()->create(['birth_date' => now()->subYears(15)]);

    $response = actingAs($registrar)
        ->get(route('registrar.reports.student-demographics'));

    $demographics = $response->viewData('demographics');

    expect($demographics['age_distribution']['3-5'])->toBe(1);
    expect($demographics['age_distribution']['9-11'])->toBe(1);
    expect($demographics['age_distribution']['15-17'])->toBe(1);
});
```

## Routes

```php
Route::middleware(['auth', 'role:registrar|administrator'])->prefix('registrar/reports')->name('registrar.reports.')->group(function () {
    Route::get('/student-demographics', [RegistrarReportController::class, 'studentDemographics'])->name('student-demographics');
});

Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin/reports')->name('super-admin.reports.')->group(function () {
    Route::get('/student-demographics', [SuperAdminReportController::class, 'studentDemographics'])->name('student-demographics');
});
```

## Dependencies

- Requires approved enrollments
- Uses Carbon for age calculations
- shadcn/ui Card, Table components

## Estimated Effort

**1.5 days**

## Implementation Checklist

- [ ] Add `studentDemographics()` method to both controllers
- [ ] Implement age calculation helper methods
- [ ] Implement new vs. returning student detection
- [ ] Add location parsing logic
- [ ] Implement caching with proper tags
- [ ] Create frontend page component
- [ ] Add progress bars for gender distribution
- [ ] Create grade level breakdown table
- [ ] Add database index on `birth_date`
- [ ] Write feature tests
- [ ] Test age calculation edge cases
- [ ] Test with large datasets for performance

## Notes

- Consider adding religion distribution if required
- May want to add nationality breakdown for diversity reporting
- Location distribution may need address parsing/geocoding enhancements
- Ensure student privacy is maintained in exports

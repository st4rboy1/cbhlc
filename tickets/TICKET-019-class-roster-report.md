# TICKET-019: Class Roster Report

## Epic

[EPIC-003: Comprehensive Reporting System](./EPIC-003-reporting-system.md)

## Priority

Medium (Could Have)

## User Story

As a registrar or administrator, I want to generate class roster reports so that I can have comprehensive lists of students by grade level/section with contact information for distribution to teachers and staff.

## Related SRS Requirements

- **FR-7.3:** System shall create class roster reports
- **FR-7.4:** System shall support report filtering by date, grade, and status

## Description

Implement class roster reporting functionality that generates detailed student lists organized by grade level with complete student and guardian contact information, suitable for printing and distribution to teaching staff.

## Acceptance Criteria

- ✅ Registrar can generate class rosters by grade level
- ✅ Report includes student personal information
- ✅ Report includes guardian contact details
- ✅ Report shows enrollment and payment status
- ✅ Roster is sortable by student name or ID
- ✅ Report is print-friendly with proper formatting
- ✅ Filters work for school year and grade level
- ✅ Report can be viewed on-screen or printed directly

## Technical Requirements

### Backend

1. Add `classRoster()` method to report controllers
2. Implement query for students with related data:
    - Student information
    - Guardian relationships
    - Enrollment details
    - Payment status
3. Support sorting by name, student_id, or enrollment date
4. Add caching with cache invalidation on enrollment changes
5. Implement grade level filtering

### Frontend

1. Create `/resources/js/pages/registrar/reports/class-roster.tsx`
2. Display roster in table format with all required columns
3. Add print-specific CSS styling
4. Implement grade level selector
5. Add sort functionality
6. Create print button with print preview

### Database

- No new tables required
- Requires joins across students, enrollments, guardians, student_guardian tables

## Implementation Details

### Controller Method

```php
public function classRoster(Request $request)
{
    $gradeLevel = $request->input('grade_level');
    $schoolYear = $request->input('school_year', EnrollmentPeriod::active()->first()?->school_year);
    $sortBy = $request->input('sort_by', 'last_name');

    $cacheKey = "class-roster-{$gradeLevel}-{$schoolYear}-{$sortBy}";

    $roster = Cache::tags(['reports', 'rosters'])
        ->remember($cacheKey, 1800, function () use ($gradeLevel, $schoolYear, $sortBy) {
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

            return $students->map(function ($student) {
                $enrollment = $student->enrollments->first();
                $primaryGuardian = $student->guardians->first();
                $secondaryGuardian = $student->guardians->skip(1)->first();

                return [
                    'student_id' => $student->student_id,
                    'full_name' => $student->full_name,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'birth_date' => $student->birth_date,
                    'age' => Carbon::parse($student->birth_date)->age,
                    'gender' => $student->gender->value,
                    'address' => $student->address,
                    'phone' => $student->phone,
                    'email' => $student->email,
                    'enrollment_id' => $enrollment->enrollment_id,
                    'enrollment_date' => $enrollment->created_at,
                    'payment_status' => $enrollment->payment_status->value,
                    'primary_guardian' => [
                        'name' => $primaryGuardian?->full_name,
                        'relationship' => $primaryGuardian?->relationship->value,
                        'phone' => $primaryGuardian?->phone,
                        'email' => $primaryGuardian?->email,
                    ],
                    'secondary_guardian' => $secondaryGuardian ? [
                        'name' => $secondaryGuardian->full_name,
                        'relationship' => $secondaryGuardian->relationship->value,
                        'phone' => $secondaryGuardian->phone,
                        'email' => $secondaryGuardian->email,
                    ] : null,
                ];
            });
        });

    return Inertia::render('Registrar/Reports/ClassRoster', [
        'roster' => $roster,
        'gradeLevel' => GradeLevel::where('level_code', $gradeLevel)->first(),
        'schoolYear' => $schoolYear,
        'sortBy' => $sortBy,
        'availableGradeLevels' => GradeLevel::where('active', true)->get(),
        'schoolYears' => EnrollmentPeriod::pluck('school_year'),
    ]);
}
```

### Frontend Page

```tsx
export default function ClassRoster({ roster, gradeLevel, schoolYear, sortBy, availableGradeLevels, schoolYears }) {
    const [selectedGrade, setSelectedGrade] = useState(gradeLevel?.level_code);
    const [selectedYear, setSelectedYear] = useState(schoolYear);
    const [selectedSort, setSelectedSort] = useState(sortBy);

    const handlePrint = () => {
        window.print();
    };

    const handleGradeChange = (grade: string) => {
        router.get(
            route('registrar.reports.class-roster'),
            { grade_level: grade, school_year: selectedYear, sort_by: selectedSort },
            { preserveState: true },
        );
    };

    return (
        <AppLayout>
            <div className="space-y-6">
                {/* Controls - hidden when printing */}
                <div className="space-y-4 print:hidden">
                    <div className="flex items-center justify-between">
                        <h1 className="text-3xl font-bold">Class Roster</h1>
                        <Button onClick={handlePrint} variant="outline">
                            <PrinterIcon className="mr-2 h-4 w-4" />
                            Print Roster
                        </Button>
                    </div>

                    <Card>
                        <CardContent className="pt-6">
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <Label>Grade Level</Label>
                                    <Select value={selectedGrade} onValueChange={handleGradeChange}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select grade level" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {availableGradeLevels.map((grade) => (
                                                <SelectItem key={grade.level_code} value={grade.level_code}>
                                                    {grade.level_name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div>
                                    <Label>School Year</Label>
                                    <Select value={selectedYear} onValueChange={setSelectedYear}>
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {schoolYears.map((year) => (
                                                <SelectItem key={year} value={year}>
                                                    {year}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div>
                                    <Label>Sort By</Label>
                                    <Select value={selectedSort} onValueChange={setSelectedSort}>
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="last_name">Last Name</SelectItem>
                                            <SelectItem value="first_name">First Name</SelectItem>
                                            <SelectItem value="student_id">Student ID</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Print Header - only visible when printing */}
                <div className="mb-6 hidden text-center print:block">
                    <h2 className="text-2xl font-bold">Christian Bible Heritage Learning Center</h2>
                    <p className="text-lg">Class Roster - {gradeLevel?.level_name}</p>
                    <p className="text-sm text-gray-600">School Year: {schoolYear}</p>
                    <p className="text-sm text-gray-600">Total Students: {roster.length}</p>
                </div>

                {/* Roster Table */}
                <Card className="print:shadow-none">
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="print:border-t-2 print:border-b-2">
                                    <TableHead className="print:py-2">#</TableHead>
                                    <TableHead className="print:py-2">Student ID</TableHead>
                                    <TableHead className="print:py-2">Student Name</TableHead>
                                    <TableHead className="print:py-2">Age/Gender</TableHead>
                                    <TableHead className="print:py-2">Primary Guardian</TableHead>
                                    <TableHead className="print:py-2">Guardian Contact</TableHead>
                                    <TableHead className="print:hidden">Payment Status</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {roster.map((student, index) => (
                                    <TableRow key={student.student_id} className="print:border-b">
                                        <TableCell className="print:py-2">{index + 1}</TableCell>
                                        <TableCell className="font-mono text-xs print:py-2">{student.student_id}</TableCell>
                                        <TableCell className="print:py-2">
                                            <div>
                                                <p className="font-medium">{student.full_name}</p>
                                                <p className="text-xs text-muted-foreground print:hidden">{student.email}</p>
                                            </div>
                                        </TableCell>
                                        <TableCell className="print:py-2">
                                            {student.age} / {student.gender}
                                        </TableCell>
                                        <TableCell className="print:py-2">
                                            <div>
                                                <p className="font-medium">{student.primary_guardian.name}</p>
                                                <p className="text-xs text-muted-foreground">{student.primary_guardian.relationship}</p>
                                            </div>
                                        </TableCell>
                                        <TableCell className="print:py-2">
                                            <div className="text-sm">
                                                <p>{student.primary_guardian.phone}</p>
                                                <p className="text-xs text-muted-foreground print:hidden">{student.primary_guardian.email}</p>
                                            </div>
                                        </TableCell>
                                        <TableCell className="print:hidden">
                                            <Badge variant={getPaymentStatusVariant(student.payment_status)}>{student.payment_status}</Badge>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                {/* Print Footer */}
                <div className="mt-8 hidden text-center text-xs text-gray-600 print:block">
                    <p>Printed on: {new Date().toLocaleDateString()}</p>
                </div>
            </div>

            <style>{`
        @media print {
          @page {
            size: A4 landscape;
            margin: 1cm;
          }
          body {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
          }
        }
      `}</style>
        </AppLayout>
    );
}
```

## Testing Requirements

### Feature Tests

```php
test('registrar can view class roster', function () {
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    $gradeLevel = GradeLevel::factory()->create(['level_code' => 'grade_1']);
    $students = Student::factory()->count(10)->create();

    foreach ($students as $student) {
        Enrollment::factory()->create([
            'student_id' => $student->id,
            'grade_level' => 'grade_1',
            'status' => EnrollmentStatus::APPROVED,
        ]);

        Guardian::factory()->count(2)->create()->each(function ($guardian) use ($student) {
            $student->guardians()->attach($guardian->id, ['is_primary' => true]);
        });
    }

    actingAs($registrar)
        ->get(route('registrar.reports.class-roster', ['grade_level' => 'grade_1']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Registrar/Reports/ClassRoster')
            ->has('roster', 10)
            ->has('roster.0.primary_guardian')
        );
});

test('class roster sorts students correctly', function () {
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    Student::factory()->create(['last_name' => 'Zulu']);
    Student::factory()->create(['last_name' => 'Alpha']);

    $response = actingAs($registrar)
        ->get(route('registrar.reports.class-roster', ['sort_by' => 'last_name']));

    $roster = $response->viewData('roster');

    expect($roster[0]['last_name'])->toBe('Alpha');
    expect($roster[1]['last_name'])->toBe('Zulu');
});
```

## Routes

```php
Route::middleware(['auth', 'role:registrar|administrator'])->prefix('registrar/reports')->name('registrar.reports.')->group(function () {
    Route::get('/class-roster', [RegistrarReportController::class, 'classRoster'])->name('class-roster');
});

Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin/reports')->name('super-admin.reports.')->group(function () {
    Route::get('/class-roster', [SuperAdminReportController::class, 'classRoster'])->name('class-roster');
});
```

## Dependencies

- Requires approved enrollments with guardian relationships
- shadcn/ui Select, Table, Button components
- Print CSS styling

## Estimated Effort

**1 day**

## Implementation Checklist

- [ ] Add `classRoster()` method to both controllers
- [ ] Implement student query with guardian relationships
- [ ] Add sorting logic
- [ ] Implement caching with appropriate tags
- [ ] Create frontend page component
- [ ] Add print-specific CSS
- [ ] Implement grade level selector
- [ ] Add sort dropdown
- [ ] Create print button with functionality
- [ ] Write feature tests
- [ ] Test print layout in multiple browsers
- [ ] Test with large class sizes (30+ students)
- [ ] Verify guardian data displays correctly

## Notes

- Print layout optimized for A4 landscape orientation
- Consider adding section grouping if school uses sections
- May want to add student photos if available
- Ensure sensitive information (payment status) is hidden in print view
- Cache should invalidate when enrollments or student/guardian info changes

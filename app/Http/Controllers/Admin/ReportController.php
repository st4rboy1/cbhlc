<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\EnrollmentPeriod;
use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    /**
     * Display the reports dashboard.
     */
    public function index(): Response
    {
        return Inertia::render('admin/reports/index');
    }

    /**
     * Get enrollment statistics report data.
     */
    public function enrollmentStatistics(Request $request)
    {
        $validated = $request->validate([
            'school_year_id' => 'nullable|exists:school_years,id',
            'enrollment_period_id' => 'nullable|exists:enrollment_periods,id',
            'grade_level' => 'nullable|string',
            'status' => 'nullable|in:pending,approved,rejected,ready_for_payment,paid,enrolled,completed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = Enrollment::query();

        // Apply filters
        if (! empty($validated['school_year_id'])) {
            $query->whereHas('enrollmentPeriod', fn ($q) => $q->where('school_year_id', $validated['school_year_id']));
        }

        if (! empty($validated['enrollment_period_id'])) {
            $query->where('enrollment_period_id', $validated['enrollment_period_id']);
        }

        if (! empty($validated['grade_level'])) {
            $query->where('grade_level', $validated['grade_level']);
        }

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (! empty($validated['start_date'])) {
            $query->whereDate('created_at', '>=', $validated['start_date']);
        }

        if (! empty($validated['end_date'])) {
            $query->whereDate('created_at', '<=', $validated['end_date']);
        }

        $totalEnrollments = $query->count();

        // Status breakdown
        $statusBreakdown = (clone $query)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->status->value => $item->count]);

        // By grade level
        $byGradeLevel = (clone $query)
            ->select('grade_level as grade', DB::raw('count(*) as count'))
            ->groupBy('grade_level')
            ->orderBy('grade_level')
            ->get();

        // Enrollment trend (monthly) - cross-database compatible
        $enrollmentTrend = (clone $query)
            ->select(
                DB::raw("strftime('%Y-%m', created_at) as month"),
                DB::raw('count(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'summary' => [
                'total' => $totalEnrollments,
                'pending' => $statusBreakdown[EnrollmentStatus::PENDING->value] ?? 0,
                'approved' => $statusBreakdown[EnrollmentStatus::APPROVED->value] ?? 0,
                'rejected' => $statusBreakdown[EnrollmentStatus::REJECTED->value] ?? 0,
                'ready_for_payment' => $statusBreakdown[EnrollmentStatus::READY_FOR_PAYMENT->value] ?? 0,
                'paid' => $statusBreakdown[EnrollmentStatus::PAID->value] ?? 0,
                'enrolled' => $statusBreakdown[EnrollmentStatus::ENROLLED->value] ?? 0,
                'completed' => $statusBreakdown[EnrollmentStatus::COMPLETED->value] ?? 0,
            ],
            'byGradeLevel' => $byGradeLevel,
            'trend' => $enrollmentTrend,
            'filters' => $validated,
        ]);
    }

    /**
     * Get student demographics report data.
     */
    public function studentDemographics(Request $request)
    {
        $validated = $request->validate([
            'school_year_id' => 'nullable|exists:school_years,id',
            'grade_level' => 'nullable|string',
            'enrollment_status' => 'nullable|in:pending,approved,rejected,ready_for_payment,paid,enrolled,completed',
        ]);

        $query = Student::query();

        // Filter by enrolled students
        if (! empty($validated['school_year_id']) || ! empty($validated['enrollment_status'])) {
            $query->whereHas('enrollments', function ($q) use ($validated) {
                if (! empty($validated['school_year_id'])) {
                    $q->whereHas('enrollmentPeriod', fn ($query) => $query->where('school_year_id', $validated['school_year_id']));
                }
                if (! empty($validated['enrollment_status'])) {
                    $q->where('status', $validated['enrollment_status']);
                }
            });
        }

        if (! empty($validated['grade_level'])) {
            $query->whereHas('enrollments', fn ($q) => $q->where('grade_level', $validated['grade_level']));
        }

        $totalStudents = $query->count();

        // Gender distribution
        $byGender = (clone $query)
            ->select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->get()
            ->mapWithKeys(fn ($item) => [ucfirst($item->gender) => $item->count]);

        // Age distribution - temporarily disabled due to birthdate accessor complexity
        // TODO: Re-implement age distribution with proper handling of null birthdates
        $byAge = collect([]);

        // Religion distribution
        $byReligion = (clone $query)
            ->select('religion', DB::raw('count(*) as count'))
            ->whereNotNull('religion')
            ->groupBy('religion')
            ->orderByDesc('count')
            ->get();

        // Nationality distribution
        $byNationality = (clone $query)
            ->select('nationality', DB::raw('count(*) as count'))
            ->whereNotNull('nationality')
            ->groupBy('nationality')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'total' => $totalStudents,
            'byGender' => $byGender,
            'byAge' => $byAge,
            'byReligion' => $byReligion,
            'byNationality' => $byNationality,
            'filters' => $validated,
        ]);
    }

    /**
     * Get class roster report data.
     */
    public function classRoster(Request $request)
    {
        $validated = $request->validate([
            'school_year_id' => 'required|exists:school_years,id',
            'grade_level' => 'required|string',
            'status' => 'nullable|in:pending,approved,rejected,ready_for_payment,paid,enrolled,completed',
        ]);

        $schoolYear = SchoolYear::findOrFail($validated['school_year_id']);

        $query = Enrollment::with('student')
            ->whereHas('enrollmentPeriod', fn ($q) => $q->where('school_year_id', $validated['school_year_id']))
            ->where('enrollments.grade_level', $validated['grade_level']);

        if (! empty($validated['status'])) {
            $query->where('enrollments.status', $validated['status']);
        } else {
            $query->where('enrollments.status', EnrollmentStatus::APPROVED);
        }

        $enrollments = $query->join('students', 'enrollments.student_id', '=', 'students.id')
            ->orderBy('students.last_name')
            ->select('enrollments.*')
            ->get();

        /** @phpstan-ignore-next-line */
        $roster = $enrollments->map(fn (Enrollment $enrollment): array => [
            'enrollment_id' => $enrollment->id,
            'student_id' => $enrollment->student->id,
            'student_number' => $enrollment->student->student_number,
            'first_name' => $enrollment->student->first_name,
            'middle_name' => $enrollment->student->middle_name,
            'last_name' => $enrollment->student->last_name,
            'full_name' => $enrollment->student->first_name.' '.$enrollment->student->last_name,
            'gender' => $enrollment->student->gender,
            'date_of_birth' => $enrollment->student->date_of_birth?->format('Y-m-d'),
            'age' => $enrollment->student->date_of_birth?->age,
            'email' => $enrollment->student->email,
            'phone' => $enrollment->student->phone,
            'status' => $enrollment->status->value,
            'enrollment_date' => $enrollment->created_at->format('Y-m-d'),
        ]);

        return response()->json([
            'school_year' => ['id' => $schoolYear->id, 'name' => $schoolYear->name],
            'grade_level' => ['grade' => $validated['grade_level'], 'name' => $validated['grade_level']],
            'total_students' => $roster->count(),
            'roster' => $roster,
            'filters' => $validated,
        ]);
    }

    /**
     * Get filter options for reports.
     */
    public function filterOptions()
    {
        $schoolYears = SchoolYear::orderBy('start_year', 'desc')->get(['id', 'name', 'start_year', 'end_year']);

        // Get grade levels from GradeLevel enum
        $gradeLevels = collect(GradeLevel::cases())->map(fn ($grade) => [
            'value' => $grade->value,
            'label' => $grade->label(),
        ]);

        $enrollmentPeriods = EnrollmentPeriod::with('schoolYear')->orderBy('start_date', 'desc')->get(['id', 'school_year_id', 'start_date', 'end_date']);

        return response()->json([
            'schoolYears' => $schoolYears,
            'gradeLevels' => $gradeLevels,
            'enrollmentPeriods' => $enrollmentPeriods,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\EnrollmentPeriod;
use App\Models\GradeLevel;
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
        return Inertia::render('Admin/Reports/Index');
    }

    /**
     * Get enrollment statistics report data.
     */
    public function enrollmentStatistics(Request $request)
    {
        $validated = $request->validate([
            'school_year_id' => 'nullable|exists:school_years,id',
            'enrollment_period_id' => 'nullable|exists:enrollment_periods,id',
            'grade_level_id' => 'nullable|exists:grade_levels,id',
            'status' => 'nullable|in:pending,approved,rejected,withdrawn',
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

        if (! empty($validated['grade_level_id'])) {
            $query->where('grade_level_id', $validated['grade_level_id']);
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
            ->join('grade_levels', 'enrollments.grade_level_id', '=', 'grade_levels.id')
            ->select('grade_levels.name as grade', DB::raw('count(*) as count'))
            ->groupBy('grade_levels.id', 'grade_levels.name')
            ->orderBy('grade_levels.name')
            ->get();

        // Enrollment trend (monthly)
        $enrollmentTrend = (clone $query)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
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
                'withdrawn' => $statusBreakdown[EnrollmentStatus::WITHDRAWN->value] ?? 0,
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
            'grade_level_id' => 'nullable|exists:grade_levels,id',
            'enrollment_status' => 'nullable|in:pending,approved,rejected,withdrawn',
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

        if (! empty($validated['grade_level_id'])) {
            $query->whereHas('enrollments', fn ($q) => $q->where('grade_level_id', $validated['grade_level_id']));
        }

        $totalStudents = $query->count();

        // Gender distribution
        $byGender = (clone $query)
            ->select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->get()
            ->mapWithKeys(fn ($item) => [ucfirst($item->gender) => $item->count]);

        // Age distribution
        $byAge = (clone $query)
            ->select(DB::raw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) as age'), DB::raw('count(*) as count'))
            ->groupBy('age')
            ->orderBy('age')
            ->get();

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
            'grade_level_id' => 'required|exists:grade_levels,id',
            'status' => 'nullable|in:pending,approved,rejected,withdrawn',
        ]);

        $gradeLevel = GradeLevel::findOrFail($validated['grade_level_id']);
        $schoolYear = SchoolYear::findOrFail($validated['school_year_id']);

        $query = Enrollment::with('student')
            ->whereHas('enrollmentPeriod', fn ($q) => $q->where('school_year_id', $validated['school_year_id']))
            ->where('grade_level_id', $validated['grade_level_id']);

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        } else {
            $query->where('status', EnrollmentStatus::APPROVED);
        }

        $enrollments = $query->join('students', 'enrollments.student_id', '=', 'students.id')
            ->orderBy('students.last_name')
            ->select('enrollments.*')
            ->get();

        $roster = $enrollments->map(fn ($enrollment) => [
            'enrollment_id' => $enrollment->id,
            'student_id' => $enrollment->student->id,
            'student_number' => $enrollment->student->student_number,
            'first_name' => $enrollment->student->first_name,
            'middle_name' => $enrollment->student->middle_name,
            'last_name' => $enrollment->student->last_name,
            'full_name' => $enrollment->student->first_name.' '.$enrollment->student->last_name,
            'gender' => $enrollment->student->gender,
            'date_of_birth' => $enrollment->student->date_of_birth->format('Y-m-d'),
            'age' => $enrollment->student->date_of_birth->age,
            'email' => $enrollment->student->email,
            'phone' => $enrollment->student->phone,
            'status' => $enrollment->status->value,
            'enrollment_date' => $enrollment->created_at->format('Y-m-d'),
        ]);

        return response()->json([
            'school_year' => ['id' => $schoolYear->id, 'name' => $schoolYear->name],
            'grade_level' => ['id' => $gradeLevel->id, 'name' => $gradeLevel->name],
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
        $gradeLevels = GradeLevel::orderBy('name')->get(['id', 'name']);
        $enrollmentPeriods = EnrollmentPeriod::with('schoolYear')->orderBy('start_date', 'desc')->get(['id', 'school_year_id', 'start_date', 'end_date']);

        return response()->json([
            'schoolYears' => $schoolYears,
            'gradeLevels' => $gradeLevels,
            'enrollmentPeriods' => $enrollmentPeriods,
        ]);
    }
}

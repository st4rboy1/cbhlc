<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEnrollmentRequest;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\SchoolInformation;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Services\EnrollmentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    public function __construct(
        protected EnrollmentService $enrollmentService
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny-enrollment');

        $enrollmentsQuery = Enrollment::query();

        // Exclude completed and enrolled enrollments from count (they should appear in Students page only)
        $activeEnrollmentsQuery = Enrollment::whereNotIn('status', [
            EnrollmentStatus::COMPLETED,
            EnrollmentStatus::ENROLLED,
        ]);

        $statusCounts = [
            'all' => (clone $activeEnrollmentsQuery)->count(),
            'pending' => (clone $enrollmentsQuery)->where('status', 'pending')->count(),
            'approved' => (clone $enrollmentsQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $enrollmentsQuery)->where('status', 'rejected')->count(),
            'enrolled' => (clone $enrollmentsQuery)->where('status', 'enrolled')->count(),
            'completed' => (clone $enrollmentsQuery)->where('status', 'completed')->count(),
        ];

        // Exclude completed and enrolled enrollments from index (they should appear in Students page only)
        $enrollments = $enrollmentsQuery
            ->whereNotIn('status', [
                EnrollmentStatus::COMPLETED,
                EnrollmentStatus::ENROLLED,
            ])
            ->with(['student', 'guardian.user'])
            ->when($request->input('search'), function ($query, $search) {
                $query->whereHas('student', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->when($request->input('status'), function ($query, $status) {
                if ($status !== 'all') {
                    $query->where('status', $status);
                }
            })
            ->when($request->input('grade'), function ($query, $grade) {
                $query->where('grade_level', $grade);
            })
            ->latest()
            ->paginate(2)
            ->withQueryString();

        return Inertia::render('admin/enrollments/index', [
            'enrollments' => $enrollments,
            'filters' => $request->only(['search', 'status', 'grade']),
            'statusCounts' => $statusCounts,
            'statuses' => collect(EnrollmentStatus::cases())->map(fn ($status) => ['value' => $status->value, 'label' => $status->label()]),
            'schoolYears' => SchoolYear::orderBy('start_year', 'desc')->get(),
        ]);
    }

    public function create(Request $request)
    {
        Gate::authorize('create-enrollment');

        // Get active school year for filtering
        $activeSchoolYear = SchoolYear::active();
        $activeSchoolYearId = $activeSchoolYear?->id;

        // Exclude students who already have enrollments for active school year
        $students = Student::with('guardians')
            ->when($activeSchoolYearId, function ($query) use ($activeSchoolYearId) {
                $query->whereDoesntHave('enrollments', function ($q) use ($activeSchoolYearId) {
                    $q->where('school_year_id', $activeSchoolYearId);
                });
            })
            ->get();

        $guardians = Guardian::with('user')->get();

        // Get available school years (active and upcoming)
        $schoolYears = SchoolYear::whereIn('status', ['active', 'upcoming'])
            ->orderBy('start_year', 'desc')
            ->get();

        return Inertia::render('admin/enrollments/create', [
            'students' => $students,
            'guardians' => $guardians,
            'schoolYears' => $schoolYears,
            'gradelevels' => array_map(fn ($grade) => [
                'label' => $grade->label(),
                'value' => $grade->value,
            ], \App\Enums\GradeLevel::cases()),
            'quarters' => array_map(fn ($quarter) => [
                'label' => $quarter->label(),
                'value' => $quarter->value,
            ], \App\Enums\Quarter::cases()),
        ]);
    }

    public function store(StoreEnrollmentRequest $request)
    {
        Gate::authorize('create-enrollment');

        $validated = $request->validated();

        // Check if student can enroll
        $student = Student::findOrFail($validated['student_id']);
        $schoolYear = SchoolYear::findOrFail($validated['school_year_id']);
        if (! $this->enrollmentService->canEnroll($student, $schoolYear->name)) {
            return redirect()->back()
                ->withErrors(['student_id' => 'Student already has a pending enrollment for this school year.'])
                ->withInput();
        }

        // Automatically get primary guardian from student
        /** @var Guardian|null $primaryGuardian */
        $primaryGuardian = $student->guardians()
            ->wherePivot('is_primary_contact', true)
            ->first();

        if (! $primaryGuardian) {
            // If no primary guardian, get any guardian
            /** @var Guardian|null $primaryGuardian */
            $primaryGuardian = $student->guardians()->first();
        }

        if (! $primaryGuardian) {
            return redirect()->back()
                ->withErrors(['student_id' => 'Selected student has no associated guardian.'])
                ->withInput();
        }

        // Add guardian_id to validated data
        $validated['guardian_id'] = $primaryGuardian->id;

        DB::transaction(function () use ($validated) {
            $enrollment = $this->enrollmentService->createEnrollment($validated);

            // Auto-approve if created by admin
            if (auth()->user()->hasRole('administrator')) {
                $this->enrollmentService->approveEnrollment($enrollment);
            }
        });

        return redirect()->route('admin.enrollments.index')
            ->with('success', 'Enrollment created successfully.');
    }

    public function show($id)
    {
        $enrollment = Enrollment::with(['student', 'guardian', 'schoolYear'])->findOrFail($id);
        Gate::authorize('view-enrollment', $enrollment);

        return Inertia::render('admin/enrollments/show', [
            'enrollment' => $enrollment,
        ]);
    }

    public function edit($id)
    {
        $enrollment = Enrollment::findOrFail($id);
        Gate::authorize('update-enrollment', $enrollment);
        $enrollment->load(['student', 'guardian']);
        $students = Student::with('guardians')->get();
        $guardians = Guardian::with('user')->get();

        // Get available school years (active and upcoming)
        $schoolYears = SchoolYear::whereIn('status', ['active', 'upcoming'])
            ->orderBy('start_year', 'desc')
            ->get();

        return Inertia::render('admin/enrollments/edit', [
            'enrollment' => $enrollment,
            'students' => $students,
            'guardians' => $guardians,
            'schoolYears' => $schoolYears,
            'gradelevels' => array_map(fn ($grade) => [
                'label' => $grade->label(),
                'value' => $grade->value,
            ], \App\Enums\GradeLevel::cases()),
            'quarters' => array_map(fn ($quarter) => [
                'label' => $quarter->label(),
                'value' => $quarter->value,
            ], \App\Enums\Quarter::cases()),
            'statuses' => array_map(fn ($status) => [
                'label' => $status->label(),
                'value' => $status->value,
            ], EnrollmentStatus::cases()),
            'paymentStatuses' => array_map(fn ($status) => [
                'label' => $status->label(),
                'value' => $status->value,
            ], \App\Enums\PaymentStatus::cases()),
            'types' => [
                ['label' => 'New Student', 'value' => 'new'],
                ['label' => 'Continuing Student', 'value' => 'continuing'],
                ['label' => 'Returnee', 'value' => 'returnee'],
                ['label' => 'Transferee', 'value' => 'transferee'],
            ],
            'paymentPlans' => [
                ['label' => 'Annual', 'value' => 'annual'],
                ['label' => 'Semestral', 'value' => 'semestral'],
                ['label' => 'Monthly', 'value' => 'monthly'],
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $enrollment = Enrollment::findOrFail($id);
        Gate::authorize('update-enrollment', $enrollment);

        $validated = $request->validate([
            'status' => 'required|string|in:'.implode(',', array_column(EnrollmentStatus::cases(), 'value')),
        ]);

        $enrollment->update($validated);

        return redirect()->route('admin.enrollments.index')->with('success', 'Enrollment updated successfully.');
    }

    public function destroy($id)
    {
        $enrollment = Enrollment::findOrFail($id);
        Gate::authorize('delete-enrollment', $enrollment);
        $enrollment->delete();

        return redirect()->route('admin.enrollments.index')->with('success', 'Enrollment deleted successfully.');
    }

    /**
     * Download enrollment certificate PDF
     * Admins can download certificates for any enrolled student
     */
    public function downloadCertificate(Enrollment $enrollment)
    {
        Gate::authorize('download-certificate', $enrollment);

        // Only allow certificate download for enrolled status
        if ($enrollment->status !== EnrollmentStatus::ENROLLED) {
            abort(403, 'Certificate only available for enrolled students.');
        }

        $enrollment->load('student', 'guardian', 'schoolYear');

        $schoolAddress = SchoolInformation::getByKey('school_address', 'Lantapan, Bukidnon');
        $schoolPhone = SchoolInformation::getByKey('school_phone', '');

        $pdf = Pdf::loadView('pdf.enrollment-certificate', [
            'enrollment' => $enrollment,
            'schoolAddress' => $schoolAddress,
            'schoolPhone' => $schoolPhone,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->stream("enrollment-certificate-{$enrollment->enrollment_id}.pdf");
    }

    /**
     * Download payment history PDF
     * Admins can download payment history for any enrollment
     */
    public function downloadPaymentHistory(Enrollment $enrollment)
    {
        Gate::authorize('download-payment-history', $enrollment);

        // Load enrollment with related data
        $enrollment->load([
            'student',
            'invoices.payments',
            'schoolYear',
        ]);

        $schoolAddress = SchoolInformation::getByKey('school_address', 'Lantapan, Bukidnon');
        $schoolPhone = SchoolInformation::getByKey('school_phone', '');
        $schoolEmail = SchoolInformation::getByKey('school_email', 'cbhlc@example.com');

        // Get all payments for this enrollment through invoices
        $payments = collect();
        foreach ($enrollment->invoices as $invoice) {
            $payments = $payments->merge($invoice->payments);
        }
        $payments = $payments->sortBy('payment_date');

        $pdf = Pdf::loadView('pdf.payment-history', [
            'enrollment' => $enrollment,
            'payments' => $payments,
            'schoolAddress' => $schoolAddress,
            'schoolPhone' => $schoolPhone,
            'schoolEmail' => $schoolEmail,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->stream("payment-history-{$enrollment->student->last_name}-{$enrollment->enrollment_id}.pdf");
    }
}

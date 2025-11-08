<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreEnrollmentRequest;
use App\Http\Requests\SuperAdmin\UpdateEnrollmentRequest;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\SchoolInformation; // Added this line
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

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Enrollment::class);

        $query = Enrollment::with(['student', 'guardian.user']);

        // Exclude completed and enrolled enrollments (they should appear in Students page only)
        $query->whereNotIn('status', [
            EnrollmentStatus::COMPLETED,
            EnrollmentStatus::ENROLLED,
        ]);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%");
            })->orWhere('enrollment_id', 'like', "%{$search}%");
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by grade level
        if ($request->filled('grade')) {
            $query->where('grade_level', $request->get('grade'));
        }

        // Filter by school year
        if ($request->filled('school_year_id')) {
            $query->where('school_year_id', $request->get('school_year_id'));
        }

        $enrollments = $query->latest()->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['enrollments' => $enrollments]);
        }

        return Inertia::render('super-admin/enrollments/index', [
            'enrollments' => $enrollments,
            'filters' => $request->only(['search', 'status', 'grade', 'school_year_id']),
            'statuses' => array_map(fn ($status) => [
                'label' => $status->label(),
                'value' => $status->value,
            ], EnrollmentStatus::cases()),
            'schoolYears' => SchoolYear::orderBy('start_year', 'desc')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Enrollment::class);

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

        return Inertia::render('super-admin/enrollments/create', [
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEnrollmentRequest $request)
    {
        Gate::authorize('create', Enrollment::class);

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

            // Auto-approve if created by super admin
            if (auth()->user()->hasRole('super_admin')) {
                $this->enrollmentService->approveEnrollment($enrollment);
            }
        });

        return redirect()->route('super-admin.enrollments.index')
            ->with('success', 'Enrollment created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Enrollment $enrollment)
    {
        Gate::authorize('view', $enrollment);

        $enrollment->load(['student', 'guardian.user', 'schoolYear']);

        return Inertia::render('super-admin/enrollments/show', [
            'enrollment' => $enrollment,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Enrollment $enrollment)
    {
        Gate::authorize('update', $enrollment);

        $enrollment->load(['student', 'guardian']);
        $students = Student::with('guardians')->get();
        $guardians = Guardian::with('user')->get();

        // Get available school years (active and upcoming)
        $schoolYears = SchoolYear::whereIn('status', ['active', 'upcoming'])
            ->orderBy('start_year', 'desc')
            ->get();

        return Inertia::render('super-admin/enrollments/edit', [
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

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEnrollmentRequest $request, Enrollment $enrollment)
    {
        Gate::authorize('update', $enrollment);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $enrollment) {
            $oldStatus = $enrollment->status;
            $newStatus = $validated['status'] ?? $oldStatus->value;

            // Update enrollment without status (status will be handled separately)
            $dataToUpdate = collect($validated)->except('status')->toArray();
            $enrollment->update($dataToUpdate);

            // Handle status changes
            if ($oldStatus->value !== $newStatus) {
                if ($newStatus === EnrollmentStatus::APPROVED->value) {
                    $this->enrollmentService->approveEnrollment($enrollment);
                } elseif ($newStatus === EnrollmentStatus::REJECTED->value) {
                    $this->enrollmentService->rejectEnrollment($enrollment, 'Updated by admin');
                } else {
                    // For all other status changes (ENROLLED, COMPLETED, PAID, READY_FOR_PAYMENT, etc.)
                    // Update the status directly
                    $enrollment->update(['status' => $newStatus]);
                }
            }
        });

        return redirect()->route('super-admin.enrollments.index')
            ->with('success', 'Enrollment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enrollment $enrollment)
    {
        Gate::authorize('delete', $enrollment);

        // Only allow deletion of pending enrollments
        if ($enrollment->status !== EnrollmentStatus::PENDING) {
            return redirect()->route('super-admin.enrollments.index')
                ->with('error', 'Can only delete pending enrollments.');
        }

        $enrollment->delete();

        return redirect()->route('super-admin.enrollments.index')
            ->with('success', 'Enrollment deleted successfully.');
    }

    /**
     * Approve an enrollment
     */
    public function approve(Enrollment $enrollment)
    {
        Gate::authorize('approve-enrollment');

        try {
            $this->enrollmentService->approveEnrollment($enrollment);

            return redirect()->route('super-admin.enrollments.index')
                ->with('success', 'Enrollment approved successfully.');
        } catch (\Exception $e) {
            return redirect()->route('super-admin.enrollments.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Reject an enrollment
     */
    public function reject(Request $request, Enrollment $enrollment)
    {
        Gate::authorize('reject-enrollment');

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        try {
            $this->enrollmentService->rejectEnrollment($enrollment, $validated['reason']);

            return redirect()->route('super-admin.enrollments.index')
                ->with('success', 'Enrollment rejected successfully.');
        } catch (\Exception $e) {
            return redirect()->route('super-admin.enrollments.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Download enrollment certificate PDF
     * Super Admins can download certificates for any enrolled student
     */
    public function downloadCertificate(Enrollment $enrollment)
    {
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
     * Super Admins can download payment history for any enrollment
     */
    public function downloadPaymentHistory(Enrollment $enrollment)
    {
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

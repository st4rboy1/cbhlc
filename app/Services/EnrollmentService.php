<?php

namespace App\Services;

use App\Contracts\Services\EnrollmentServiceInterface;
use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Mail\EnrollmentApproved;
use App\Mail\EnrollmentRejected;
use App\Models\Enrollment;
use App\Models\GradeLevelFee;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EnrollmentService extends BaseService implements EnrollmentServiceInterface
{
    /**
     * EnrollmentService constructor.
     */
    public function __construct(Enrollment $model, protected InvoiceService $invoiceService)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated enrollments with filters
     */
    public function getPaginatedEnrollments(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['student', 'guardian', 'approver']);

        // Apply status filter
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply school year filter
        if (! empty($filters['school_year'])) {
            // Convert school year name to ID if needed
            if (is_string($filters['school_year'])) {
                $schoolYear = \App\Models\SchoolYear::where('name', $filters['school_year'])->first();
                if ($schoolYear) {
                    $query->where('school_year_id', $schoolYear->id);
                }
            } else {
                $query->where('school_year_id', $filters['school_year']);
            }
        }

        // Apply grade level filter
        if (! empty($filters['grade_level'])) {
            $query->where('grade_level', $filters['grade_level']);
        }

        // Apply student filter
        if (! empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        // Apply date range filters
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Apply search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $this->logActivity('getPaginatedEnrollments', ['filters' => $filters]);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get enrollments by guardian
     */
    public function getEnrollmentsByGuardian(int $guardianId): Collection
    {
        $this->logActivity('getEnrollmentsByGuardian', ['guardian_id' => $guardianId]);

        return $this->model->where('guardian_id', $guardianId)
            ->with(['student', 'approver'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get enrollments by student
     */
    public function getEnrollmentsByStudent(int $studentId): Collection
    {
        $this->logActivity('getEnrollmentsByStudent', ['student_id' => $studentId]);

        return $this->model->where('student_id', $studentId)
            ->with(['guardian', 'approver'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create enrollment application
     */
    public function createEnrollment(array $data): Enrollment
    {
        return DB::transaction(function () use ($data) {
            // Check if student can enroll
            $student = Student::findOrFail($data['student_id']);

            // Get school year name from ID if provided
            if (isset($data['school_year_id'])) {
                $schoolYear = \App\Models\SchoolYear::find($data['school_year_id']);
                $schoolYearName = $schoolYear?->name;
            } else {
                $schoolYearName = $data['school_year'] ?? null;
            }

            if ($schoolYearName && ! $this->canEnroll($student, $schoolYearName)) {
                throw new \Exception('Student cannot enroll for this school year');
            }

            // Calculate fees
            $fees = $this->calculateFees($data['grade_level']);

            // Generate enrollment ID
            $enrollmentId = $this->generateEnrollmentId();

            // Prepare enrollment data
            $enrollmentData = array_merge($data, [
                'enrollment_id' => $enrollmentId,
                'status' => EnrollmentStatus::PENDING,
                'payment_status' => PaymentStatus::PENDING,
                'tuition_fee_cents' => $fees['tuition'] * 100,
                'miscellaneous_fee_cents' => $fees['miscellaneous'] * 100,
                'laboratory_fee_cents' => $fees['laboratory'] * 100,
                'library_fee_cents' => $fees['library'] * 100,
                'sports_fee_cents' => $fees['sports'] * 100,
                'total_amount_cents' => $fees['total'] * 100,
                'net_amount_cents' => $fees['total'] * 100, // Assuming no discount at creation
                'amount_paid_cents' => 0,
                'balance_cents' => $fees['total'] * 100,
            ]);

            // Create enrollment
            /** @var Enrollment $enrollment */
            $enrollment = $this->model->create($enrollmentData);

            $this->logActivity('createEnrollment', [
                'enrollment_id' => $enrollment->id,
                'student_id' => $student->id,
            ]);

            // Email notification is handled by EnrollmentObserver

            return $enrollment->fresh(['student', 'guardian']);
        });
    }

    /**
     * Approve enrollment
     */
    public function approveEnrollment(Enrollment $enrollment): Enrollment
    {
        return DB::transaction(function () use ($enrollment) {
            if ($enrollment->status !== EnrollmentStatus::PENDING) {
                throw new \Exception('Only pending enrollments can be approved');
            }

            $enrollment->update([
                'status' => EnrollmentStatus::APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Send notification to guardian about approval and payment requirements
            $enrollment->load(['student', 'guardian.user', 'schoolYear']);
            if ($enrollment->guardian && $enrollment->guardian->user) {
                $enrollment->guardian->user->notify(
                    new \App\Notifications\EnrollmentApprovedNotification($enrollment)
                );
                // Also send email
                if ($enrollment->guardian->user->email) {
                    Mail::to($enrollment->guardian->user->email)->queue(
                        new \App\Mail\EnrollmentApproved($enrollment)
                    );
                }
            }

            $enrollment->update([
                'status' => EnrollmentStatus::READY_FOR_PAYMENT,
                'ready_for_payment_at' => now(),
            ]);

            $this->logActivity('approveEnrollment', ['enrollment_id' => $enrollment->id]);

            return $enrollment->fresh(['student', 'guardian', 'approver']);
        });
    }

    /**
     * Find enrollment with relationships
     */
    public function findWithRelations(int $id): Enrollment
    {
        /** @var Enrollment $enrollment */
        $enrollment = $this->model->with(['student', 'invoices', 'payments'])
            ->findOrFail($id);

        $this->logActivity('findWithRelations', ['enrollment_id' => $id]);

        return $enrollment;
    }

    /**
     * Reject enrollment
     */
    public function rejectEnrollment(Enrollment $enrollment, ?string $reason = null): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $reason) {
            if ($enrollment->status !== EnrollmentStatus::PENDING) {
                throw new \Exception('Only pending enrollments can be rejected');
            }

            $enrollment->update([
                'status' => EnrollmentStatus::REJECTED,
                'remarks' => $reason,
                'approved_by' => auth()->id(),
                'rejected_at' => now(),
            ]);

            $this->logActivity('rejectEnrollment', [
                'enrollment_id' => $enrollment->id,
                'reason' => $reason,
            ]);

            // Email notification is handled by EnrollmentObserver
            $enrollment->load(['student', 'guardian.user', 'schoolYear']);
            if ($enrollment->guardian && $enrollment->guardian->user && $enrollment->guardian->user->email) {
                Mail::to($enrollment->guardian->user->email)->queue(
                    new EnrollmentRejected($enrollment, $reason)
                );
            }

            return $enrollment->fresh(['student', 'guardian', 'approver']);
        });
    }

    /**
     * Bulk approve enrollments
     *
     * @return int Number of approved enrollments
     */
    public function bulkApproveEnrollments(array $enrollmentIds): int
    {
        return DB::transaction(function () use ($enrollmentIds) {
            $count = 0;
            $enrollments = $this->model->whereIn('id', $enrollmentIds)
                ->where('status', EnrollmentStatus::PENDING)
                ->get();

            foreach ($enrollments as $enrollment) {
                /** @var Enrollment $enrollment */
                if ($enrollment->status === EnrollmentStatus::PENDING) {
                    $enrollment->update([
                        'status' => EnrollmentStatus::ENROLLED,
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);

                    $enrollment->load(['student', 'guardian.user', 'schoolYear']);
                    if ($enrollment->guardian && $enrollment->guardian->user && $enrollment->guardian->user->email) {
                        Mail::to($enrollment->guardian->user->email)->queue(
                            new EnrollmentApproved($enrollment)
                        );
                    }

                    $count++;
                }
            }

            $this->logActivity('bulkApproveEnrollments', [
                'count' => $count,
                'enrollment_ids' => $enrollmentIds,
            ]);

            return $count;
        });
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(Enrollment $enrollment, PaymentStatus|string $status, ?float $amount = null): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $status, $amount) {
            $updateData = ['payment_status' => $status];

            if ($amount !== null) {
                $amountCents = $amount * 100;
                $updateData['amount_paid_cents'] = $enrollment->amount_paid_cents + $amountCents;
                $updateData['balance_cents'] = $enrollment->total_amount_cents - $updateData['amount_paid_cents'];

                // Determine payment status based on balance
                if ($updateData['balance_cents'] <= 0) {
                    $updateData['payment_status'] = PaymentStatus::PAID;
                } elseif ($updateData['amount_paid_cents'] > 0) {
                    $updateData['payment_status'] = PaymentStatus::PARTIAL;
                }
            }

            $enrollment->update($updateData);

            $this->logActivity('updatePaymentStatus', [
                'enrollment_id' => $enrollment->id,
                'status' => $status,
                'amount' => $amount,
            ]);

            return $enrollment->fresh();
        });
    }

    /**
     * Calculate enrollment fees
     */
    public function calculateFees(string $gradeLevel, array $options = []): array
    {
        // Find the current enrollment period
        $activeEnrollmentPeriod = \App\Models\EnrollmentPeriod::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderBy('start_date', 'desc')
            ->first();

        if (! $activeEnrollmentPeriod) {
            $activeEnrollmentPeriod = \App\Models\EnrollmentPeriod::where('status', 'active')->first();
        }

        if (! $activeEnrollmentPeriod) {
            $activeEnrollmentPeriod = \App\Models\EnrollmentPeriod::orderBy('start_date', 'desc')->first();
        }

        $gradeLevelFee = GradeLevelFee::where('grade_level', $gradeLevel)
            ->where('enrollment_period_id', $activeEnrollmentPeriod?->id)
            ->first();

        if (! $gradeLevelFee) {
            // Default fees if not configured
            return [
                'tuition' => 0.0,
                'registration' => 0.0,
                'miscellaneous' => 0.0,
                'laboratory' => 0.0,
                'library' => 0.0,
                'sports' => 0.0,
                'total' => 0.0,
            ];
        }

        $tuition = $gradeLevelFee->tuition_fee_cents / 100;
        $registration = $gradeLevelFee->registration_fee_cents / 100;
        $miscellaneous = $gradeLevelFee->miscellaneous_fee_cents / 100;
        $laboratory = $gradeLevelFee->laboratory_fee_cents / 100;
        $library = $gradeLevelFee->library_fee_cents / 100;
        $sports = $gradeLevelFee->sports_fee_cents / 100;

        // Apply any discounts from options
        $discount = $options['discount'] ?? 0;
        $discountAmount = ($tuition + $registration + $miscellaneous) * ($discount / 100);

        $total = $tuition + $registration + $miscellaneous + $laboratory + $library + $sports - $discountAmount;

        return [
            'tuition' => $tuition,
            'registration' => $registration,
            'miscellaneous' => $miscellaneous,
            'laboratory' => $laboratory,
            'library' => $library,
            'sports' => $sports,
            'total' => $total,
        ];
    }

    /**
     * Check if student can enroll
     */
    public function canEnroll(Student $student, string $schoolYear): bool
    {
        // Get school year ID
        $schoolYearModel = \App\Models\SchoolYear::where('name', $schoolYear)->first();

        // Check for existing enrollment in the same school year
        $existingEnrollment = $this->model
            ->where('student_id', $student->id)
            ->where('school_year_id', $schoolYearModel?->id)
            ->first();

        if ($existingEnrollment) {
            return false;
        }

        // Check for pending enrollments
        $pendingEnrollment = $this->model
            ->where('student_id', $student->id)
            ->where('status', EnrollmentStatus::PENDING)
            ->exists();

        if ($pendingEnrollment) {
            return false;
        }

        // Check for active enrollment
        $activeEnrollment = $this->model
            ->where('student_id', $student->id)
            ->where('status', EnrollmentStatus::ENROLLED)
            ->exists();

        return ! $activeEnrollment;
    }

    /**
     * Get enrollment statistics
     */
    public function getStatistics(): array
    {
        $currentYearName = date('Y').'-'.(date('Y') + 1);
        $currentYear = \App\Models\SchoolYear::where('name', $currentYearName)->first();
        $currentYearId = $currentYear?->id;

        return [
            'total' => $this->model->where('school_year_id', $currentYearId)->count(),
            'pending' => $this->model->where('school_year_id', $currentYearId)
                ->where('status', EnrollmentStatus::PENDING)->count(),
            'approved' => $this->model->where('school_year_id', $currentYearId)
                ->where('status', EnrollmentStatus::ENROLLED)->count(),
            'rejected' => $this->model->where('school_year_id', $currentYearId)
                ->where('status', EnrollmentStatus::REJECTED)->count(),
            'paid' => $this->model->where('school_year_id', $currentYearId)
                ->where('payment_status', PaymentStatus::PAID)->count(),
            'partial' => $this->model->where('school_year_id', $currentYearId)
                ->where('payment_status', PaymentStatus::PARTIAL)->count(),
        ];
    }

    /**
     * Get pending enrollments count
     */
    public function getPendingCount(): int
    {
        return $this->model->where('status', EnrollmentStatus::PENDING)->count();
    }

    /**
     * Generate unique enrollment ID
     */
    protected function generateEnrollmentId(): string
    {
        do {
            $id = 'ENR-'.str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while ($this->model->where('enrollment_id', $id)->exists());

        return $id;
    }
}

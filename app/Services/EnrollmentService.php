<?php

namespace App\Services;

use App\Contracts\Services\EnrollmentServiceInterface;
use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Enrollment;
use App\Models\GradeLevelFee;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EnrollmentService extends BaseService implements EnrollmentServiceInterface
{
    /**
     * EnrollmentService constructor.
     */
    public function __construct(Enrollment $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated enrollments with filters
     */
    public function getPaginatedEnrollments(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['student', 'guardian', 'approvedBy']);

        // Apply status filter
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply school year filter
        if (! empty($filters['school_year'])) {
            $query->where('school_year', $filters['school_year']);
        }

        // Apply grade level filter
        if (! empty($filters['grade_level'])) {
            $query->where('grade_level', $filters['grade_level']);
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
            ->with(['student', 'approvedBy'])
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
            ->with(['guardian', 'approvedBy'])
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
            if (! $this->canEnroll($student, $data['school_year'])) {
                throw new \Exception('Student cannot enroll for this school year');
            }

            // Calculate fees
            $fees = $this->calculateFees($data['grade_level']);

            // Prepare enrollment data
            $enrollmentData = array_merge($data, [
                'status' => EnrollmentStatus::PENDING,
                'payment_status' => PaymentStatus::PENDING,
                'tuition_fee_cents' => $fees['tuition_fee'] * 100,
                'miscellaneous_fee_cents' => $fees['miscellaneous_fee'] * 100,
                'laboratory_fee_cents' => $fees['laboratory_fee'] * 100,
                'total_amount_cents' => $fees['total_amount'] * 100,
                'net_amount_cents' => $fees['total_amount'] * 100,
                'amount_paid_cents' => 0,
                'balance_cents' => $fees['total_amount'] * 100,
            ]);

            // Create enrollment
            $enrollment = $this->model->create($enrollmentData);

            $this->logActivity('createEnrollment', [
                'enrollment_id' => $enrollment->id,
                'student_id' => $student->id,
            ]);

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
                'status' => EnrollmentStatus::ENROLLED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Update student's current grade level
            $enrollment->student->update([
                'grade_level' => $enrollment->grade_level,
            ]);

            $this->logActivity('approveEnrollment', ['enrollment_id' => $enrollment->id]);

            return $enrollment->fresh(['student', 'guardian', 'approvedBy']);
        });
    }

    /**
     * Reject enrollment
     */
    public function rejectEnrollment(Enrollment $enrollment, string $reason): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $reason) {
            if ($enrollment->status !== EnrollmentStatus::PENDING) {
                throw new \Exception('Only pending enrollments can be rejected');
            }

            $enrollment->update([
                'status' => EnrollmentStatus::REJECTED,
                'remarks' => $reason,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $this->logActivity('rejectEnrollment', [
                'enrollment_id' => $enrollment->id,
                'reason' => $reason,
            ]);

            return $enrollment->fresh(['student', 'guardian', 'approvedBy']);
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
                $this->approveEnrollment($enrollment);
                $count++;
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
    public function updatePaymentStatus(Enrollment $enrollment, string $status, ?float $amount = null): Enrollment
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
        $gradeLevelFee = GradeLevelFee::where('grade_level', $gradeLevel)->first();

        if (! $gradeLevelFee) {
            // Default fees if not configured
            return [
                'tuition_fee' => 0,
                'miscellaneous_fee' => 0,
                'laboratory_fee' => 0,
                'total_amount' => 0,
            ];
        }

        $tuitionFee = $gradeLevelFee->tuition_fee / 100; // Convert from cents
        $miscellaneousFee = $gradeLevelFee->miscellaneous_fee / 100;
        $laboratoryFee = $gradeLevelFee->laboratory_fee / 100;

        // Apply any discounts from options
        $discount = $options['discount'] ?? 0;
        $discountAmount = ($tuitionFee + $miscellaneousFee + $laboratoryFee) * ($discount / 100);

        $totalAmount = $tuitionFee + $miscellaneousFee + $laboratoryFee - $discountAmount;

        return [
            'tuition_fee' => $tuitionFee,
            'miscellaneous_fee' => $miscellaneousFee,
            'laboratory_fee' => $laboratoryFee,
            'discount' => $discountAmount,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Check if student can enroll
     */
    public function canEnroll(Student $student, string $schoolYear): bool
    {
        // Check for existing enrollment in the same school year
        $existingEnrollment = $this->model
            ->where('student_id', $student->id)
            ->where('school_year', $schoolYear)
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
        $currentYear = date('Y').'-'.(date('Y') + 1);

        return [
            'total' => $this->model->where('school_year', $currentYear)->count(),
            'pending' => $this->model->where('school_year', $currentYear)
                ->where('status', EnrollmentStatus::PENDING)->count(),
            'approved' => $this->model->where('school_year', $currentYear)
                ->where('status', EnrollmentStatus::ENROLLED)->count(),
            'rejected' => $this->model->where('school_year', $currentYear)
                ->where('status', EnrollmentStatus::REJECTED)->count(),
            'paid' => $this->model->where('school_year', $currentYear)
                ->where('payment_status', PaymentStatus::PAID)->count(),
            'partial' => $this->model->where('school_year', $currentYear)
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
}

<?php

namespace App\Contracts\Services;

use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface EnrollmentServiceInterface
{
    /**
     * Get paginated enrollments with filters
     */
    public function getPaginatedEnrollments(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Get enrollments by guardian
     */
    public function getEnrollmentsByGuardian(int $guardianId): Collection;

    /**
     * Get enrollments by student
     */
    public function getEnrollmentsByStudent(int $studentId): Collection;

    /**
     * Create enrollment application
     */
    public function createEnrollment(array $data): Enrollment;

    /**
     * Approve enrollment
     */
    public function approveEnrollment(Enrollment $enrollment): Enrollment;

    /**
     * Reject enrollment
     */
    public function rejectEnrollment(Enrollment $enrollment, string $reason): Enrollment;

    /**
     * Bulk approve enrollments
     *
     * @return int Number of approved enrollments
     */
    public function bulkApproveEnrollments(array $enrollmentIds): int;

    /**
     * Update payment status
     */
    public function updatePaymentStatus(Enrollment $enrollment, string $status, ?float $amount = null): Enrollment;

    /**
     * Calculate enrollment fees
     */
    public function calculateFees(string $gradeLevel, array $options = []): array;

    /**
     * Check if student can enroll
     */
    public function canEnroll(Student $student, string $schoolYear): bool;

    /**
     * Get enrollment statistics
     */
    public function getStatistics(): array;

    /**
     * Get pending enrollments count
     */
    public function getPendingCount(): int;
}

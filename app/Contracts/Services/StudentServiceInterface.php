<?php

namespace App\Contracts\Services;

use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface StudentServiceInterface
{
    /**
     * Get paginated students with filters
     */
    public function getPaginatedStudents(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Find a student by ID with relationships
     */
    public function findWithRelations(int $id, array $relations = []): Student;

    /**
     * Create a new student
     */
    public function createStudent(array $data): Student;

    /**
     * Update a student
     */
    public function updateStudent(Student $student, array $data): Student;

    /**
     * Delete a student
     */
    public function deleteStudent(Student $student): bool;

    /**
     * Get students by guardian
     */
    public function getStudentsByGuardian(int $guardianId): Collection;

    /**
     * Search students
     */
    public function searchStudents(string $query, array $filters = []): LengthAwarePaginator;

    /**
     * Generate unique student ID
     */
    public function generateStudentId(): string;

    /**
     * Check if student can be deleted
     */
    public function canDelete(Student $student): bool;
}

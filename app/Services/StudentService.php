<?php

namespace App\Services;

use App\Contracts\Services\StudentServiceInterface;
use App\Models\GuardianStudent;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StudentService extends BaseService implements StudentServiceInterface
{
    /**
     * StudentService constructor.
     */
    public function __construct(Student $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated students with filters
     */
    public function getPaginatedStudents(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['guardianStudents.guardian']);

        // Apply search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply grade level filter
        if (! empty($filters['grade_level'])) {
            $query->where('grade_level', $filters['grade_level']);
        }

        // Apply section filter
        if (! empty($filters['section'])) {
            $query->where('section', $filters['section']);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $this->logActivity('getPaginatedStudents', ['filters' => $filters]);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Find a student by ID with relationships
     */
    public function findWithRelations(int $id, array $relations = []): Student
    {
        $defaultRelations = ['guardianStudents.guardian', 'enrollments'];
        $relations = array_merge($defaultRelations, $relations);

        $this->logActivity('findWithRelations', ['id' => $id, 'relations' => $relations]);

        /** @var Student */
        return $this->model->with($relations)->findOrFail($id);
    }

    /**
     * Create a new student
     */
    public function createStudent(array $data): Student
    {
        return DB::transaction(function () use ($data) {
            // Generate student ID if not provided
            if (empty($data['student_id'])) {
                $data['student_id'] = $this->generateStudentId();
            }

            // Extract guardian-related data before creating student
            $guardianId = $data['guardian_id'] ?? null;
            $relationship = $data['relationship'] ?? 'guardian';
            unset($data['guardian_id'], $data['relationship']);

            // Create the student
            /** @var Student $student */
            $student = $this->model->create($data);

            // Associate with guardian if guardian_id is provided
            if (! empty($guardianId)) {
                $this->associateGuardian($student, $guardianId, $relationship);
            }

            $this->logActivity('createStudent', ['student_id' => $student->id]);

            /** @var Student $student */
            return $student->fresh(['guardianStudents.guardian']);
        });
    }

    /**
     * Update a student
     */
    public function updateStudent(Student $student, array $data): Student
    {
        return DB::transaction(function () use ($student, $data) {
            // Remove guardian-related data from update
            $guardianData = [];
            if (isset($data['guardian_id'])) {
                $guardianData['guardian_id'] = $data['guardian_id'];
                unset($data['guardian_id']);
            }
            if (isset($data['relationship'])) {
                $guardianData['relationship'] = $data['relationship'];
                unset($data['relationship']);
            }

            // Update student data
            $student->update($data);

            // Update guardian association if provided
            if (! empty($guardianData['guardian_id'])) {
                $this->updateGuardianAssociation(
                    $student,
                    $guardianData['guardian_id'],
                    $guardianData['relationship'] ?? 'guardian'
                );
            }

            $this->logActivity('updateStudent', ['student_id' => $student->id]);

            /** @var Student $student */
            return $student->fresh(['guardianStudents.guardian']);
        });
    }

    /**
     * Delete a student
     */
    public function deleteStudent(Student $student): bool
    {
        if (! $this->canDelete($student)) {
            throw new \Exception('Cannot delete student with existing enrollments');
        }

        return DB::transaction(function () use ($student) {
            // Delete guardian associations
            $student->guardianStudents()->delete();

            $result = $student->delete();

            $this->logActivity('deleteStudent', ['student_id' => $student->id]);

            return $result;
        });
    }

    /**
     * Get students by guardian
     */
    public function getStudentsByGuardian(int $guardianId): Collection
    {
        $this->logActivity('getStudentsByGuardian', ['guardian_id' => $guardianId]);

        return $this->model->whereHas('guardianStudents', function ($query) use ($guardianId) {
            $query->where('guardian_id', $guardianId);
        })->with(['enrollments', 'guardianStudents'])->get();
    }

    /**
     * Search students
     */
    public function searchStudents(string $query, array $filters = []): LengthAwarePaginator
    {
        $filters['search'] = $query;

        return $this->getPaginatedStudents($filters);
    }

    /**
     * Generate unique student ID
     */
    public function generateStudentId(): string
    {
        $year = date('Y');
        $lastStudent = $this->model
            ->where('student_id', 'like', $year.'%')
            ->orderBy('student_id', 'desc')
            ->first();

        if ($lastStudent) {
            $lastNumber = (int) substr($lastStudent->student_id, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $year.str_pad((string) $newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check if student can be deleted
     */
    public function canDelete(Student $student): bool
    {
        return $student->enrollments()->count() === 0;
    }

    /**
     * Associate guardian with student
     */
    protected function associateGuardian(Student $student, int $guardianId, string $relationship = 'guardian'): GuardianStudent
    {
        // Check if guardian exists
        $guardian = User::findOrFail($guardianId);

        // Check if association already exists
        $existing = GuardianStudent::where('student_id', $student->id)
            ->where('guardian_id', $guardianId)
            ->first();

        if ($existing) {
            $existing->update(['relationship_type' => $relationship]);

            return $existing;
        }

        // Create new association
        return GuardianStudent::create([
            'student_id' => $student->id,
            'guardian_id' => $guardianId,
            'relationship_type' => $relationship,
            'is_primary_contact' => GuardianStudent::where('student_id', $student->id)->count() === 0,
        ]);
    }

    /**
     * Update guardian association
     */
    protected function updateGuardianAssociation(Student $student, int $guardianId, string $relationship = 'parent'): void
    {
        $this->associateGuardian($student, $guardianId, $relationship);
    }
}

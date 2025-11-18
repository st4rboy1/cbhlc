<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\DocumentType;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guardian\StoreStudentRequest;
use App\Http\Requests\Guardian\UpdateStudentRequest;
use App\Models\Document;
use App\Models\EnrollmentPeriod;
use App\Models\Guardian;
use App\Models\GuardianStudent;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class StudentController extends Controller
{
    /**
     * Display a listing of guardian's students.
     */
    public function index()
    {
        // Get Guardian model for authenticated user
        $guardian = Guardian::where('user_id', Auth::id())->firstOrFail();

        // Get all students for this guardian
        $studentIds = GuardianStudent::where('guardian_id', $guardian->id)
            ->pluck('student_id');

        /** @var \Illuminate\Support\Collection<int, array<string, mixed>> $students */
        $students = Student::with(['enrollments' => function ($query) {
            $query->with('schoolYear')->latest('created_at')->limit(1);
        }])
            ->whereIn('id', $studentIds)
            ->get()
            /** @phpstan-ignore-next-line */
            ->map(function (Student $student) {
                $latestEnrollment = $student->enrollments->first();

                return [
                    'id' => $student->id,
                    'student_id' => $student->student_id,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'full_name' => $student->first_name.' '.
                                  ($student->middle_name ? $student->middle_name.' ' : '').
                                  $student->last_name,
                    'birthdate' => $student->birthdate,
                    'gender' => $student->gender,
                    'grade_level' => $student->grade_level,
                    'latest_enrollment' => $latestEnrollment ? [
                        'school_year_name' => $latestEnrollment->schoolYear->name,
                        'status' => $latestEnrollment->status->value,
                        'grade_level' => $latestEnrollment->grade_level,
                    ] : null,
                ];
            });

        return Inertia::render('guardian/students/index', [
            'students' => $students,
        ]);
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student)
    {
        // Get Guardian model for authenticated user
        $guardian = Guardian::where('user_id', Auth::id())->firstOrFail();

        // Verify this guardian has access to this student
        $hasAccess = GuardianStudent::where('guardian_id', $guardian->id)
            ->where('student_id', $student->id)
            ->exists();

        if (! $hasAccess) {
            abort(403, 'You do not have access to view this student.');
        }

        $student->load('enrollments.schoolYear', 'documents');

        // Check for active enrollment period
        $activePeriod = EnrollmentPeriod::active()->first();
        $canEnroll = $activePeriod && $activePeriod->isOpen();

        return Inertia::render('guardian/students/show', [
            'canEnroll' => $canEnroll,
            'enrollmentMessage' => ! $canEnroll
                ? ($activePeriod ? 'Enrollment period is currently closed.' : 'No active enrollment period available.')
                : null,
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'first_name' => $student->first_name,
                'middle_name' => $student->middle_name,
                'last_name' => $student->last_name,
                'birthdate' => $student->birthdate,
                'gender' => $student->gender,
                'address' => $student->address,
                'contact_number' => $student->contact_number,
                'email' => $student->email,
                'grade_level' => $student->grade_level,
                'section' => $student->section,
                'birth_place' => $student->birth_place,
                'nationality' => $student->nationality,
                'religion' => $student->religion,

                'enrollments' => $student->enrollments->map(function (\App\Models\Enrollment $enrollment) {
                    return [
                        'id' => $enrollment->id,
                        'school_year_id' => $enrollment->school_year_id,
                        'grade_level' => $enrollment->grade_level,
                        'quarter' => $enrollment->quarter,
                        'status' => $enrollment->status->value,
                        'payment_status' => $enrollment->payment_status->value,
                        'created_at' => $enrollment->created_at->format('Y-m-d'),
                    ];
                }),

                'documents' => $student->documents->map(function (\App\Models\Document $document) {
                    return [
                        'id' => $document->id,
                        'document_type' => $document->document_type->value,
                        'document_type_label' => $document->document_type->label(),
                        'original_filename' => $document->original_filename,
                        'file_size' => $document->file_size,
                        'upload_date' => $document->upload_date->format('Y-m-d'),
                        'verification_status' => $document->verification_status->value,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Show the form for creating a new student.
     */
    public function create()
    {
        return Inertia::render('guardian/students/create', [
            'gradeLevels' => \App\Enums\GradeLevel::values(),
        ]);
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(StoreStudentRequest $request)
    {
        $validated = $request->validated();

        // Extract only student fields (exclude document uploads)
        $studentData = $request->only([
            'first_name',
            'middle_name',
            'last_name',
            'birthdate',
            'gender',
            'address',
            'contact_number',
            'email',
            'grade_level',
            'birth_place',
            'nationality',
            'religion',
        ]);

        // Generate student ID
        $studentData['student_id'] = 'CBHLC'.date('Y').str_pad((string) (Student::count() + 1), 4, '0', STR_PAD_LEFT);

        $student = Student::create($studentData);

        // Dispatch event to notify registrars
        event(new \App\Events\StudentCreated($student));

        // Get Guardian model for authenticated user
        $guardian = Guardian::where('user_id', Auth::id())->firstOrFail();

        // Link student to guardian
        GuardianStudent::create([
            'guardian_id' => $guardian->id,
            'student_id' => $student->id,
            'relationship_type' => 'mother', // Default relationship type
            'is_primary_contact' => true,
        ]);

        // Handle document uploads
        $documentMappings = [
            'birth_certificate' => DocumentType::BIRTH_CERTIFICATE,
            'report_card' => DocumentType::REPORT_CARD,
            'form_138' => DocumentType::FORM_138,
            'good_moral' => DocumentType::GOOD_MORAL,
        ];

        foreach ($documentMappings as $field => $documentType) {
            if ($request->hasFile($field)) {
                try {
                    $file = $request->file($field);
                    $originalName = $file->getClientOriginalName();
                    $storedName = Str::random(40).'.'.$file->extension();

                    // Store file in private storage
                    $path = $file->storeAs(
                        "documents/{$student->id}",
                        $storedName,
                        'private'
                    );

                    // Create document record
                    Document::create([
                        'student_id' => $student->id,
                        'document_type' => $documentType,
                        'original_filename' => $originalName,
                        'stored_filename' => $storedName,
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'upload_date' => now(),
                        'verification_status' => VerificationStatus::PENDING,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Document upload failed for student creation', [
                        'student_id' => $student->id,
                        'document_type' => $documentType->value,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Optionally, you might want to return an error to the user here
                    // or add a warning to the session.
                }
            }
        }

        return redirect()->route('guardian.students.show', $student->id)
            ->with('success', 'Student and documents added successfully.');
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $student)
    {
        // Get Guardian model for authenticated user
        $guardian = Guardian::where('user_id', Auth::id())->firstOrFail();

        // Verify this guardian has access to this student
        $hasAccess = GuardianStudent::where('guardian_id', $guardian->id)
            ->where('student_id', $student->id)
            ->exists();

        if (! $hasAccess) {
            abort(403, 'You do not have access to edit this student.');
        }

        return Inertia::render('guardian/students/edit', [
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'first_name' => $student->first_name,
                'middle_name' => $student->middle_name,
                'last_name' => $student->last_name,
                'birthdate' => $student->birthdate,
                'gender' => $student->gender,
                'grade_level' => $student->grade_level,
                'contact_number' => $student->contact_number,
                'email' => $student->email,
                'address' => $student->address,
                'birth_place' => $student->birth_place,
                'nationality' => $student->nationality,
                'religion' => $student->religion,
            ],
        ]);
    }

    /**
     * Update the specified student in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student)
    {
        $validated = $request->validated();

        $student->update($validated);

        return redirect()->route('guardian.students.show', $student->id)
            ->with('success', 'Student information updated successfully.');
    }

    /**
     * Remove the student from guardian's account.
     */
    public function destroy(Student $student)
    {
        // Get Guardian model for authenticated user
        $guardian = Guardian::where('user_id', Auth::id())->firstOrFail();

        // Verify this guardian has access to this student
        $guardianStudent = GuardianStudent::where('guardian_id', $guardian->id)
            ->where('student_id', $student->id)
            ->first();

        if (! $guardianStudent) {
            abort(403, 'You do not have access to remove this student.');
        }

        // Check if student has any active enrollments
        $hasActiveEnrollments = $student->enrollments()
            ->whereIn('status', [\App\Enums\EnrollmentStatus::PENDING, \App\Enums\EnrollmentStatus::ENROLLED])
            ->exists();

        if ($hasActiveEnrollments) {
            return redirect()->route('guardian.students.show', $student->id)
                ->with('error', 'Cannot remove student with active or pending enrollments.');
        }

        // Remove the guardian-student relationship
        $guardianStudent->delete();

        return redirect()->route('guardian.students.index')
            ->with('success', 'Student removed from your account successfully.');
    }
}

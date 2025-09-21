<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\GradeLevel;
use App\Enums\RelationshipType;
use App\Http\Controllers\Controller;
use App\Models\GuardianStudent;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    /**
     * Display the guardian's children
     */
    public function index(): Response
    {
        $user = auth()->user();
        $guardian = \App\Models\Guardian::where('user_id', $user->id)->first();

        $children = $guardian ? $guardian->children()
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'student_id' => $student->student_id,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'full_name' => trim($student->first_name.' '.$student->middle_name.' '.$student->last_name),
                    'birthdate' => $student->birthdate->format('M d, Y'),
                    'grade_level' => $student->grade_level,
                    'relationship_type' => $student->pivot->relationship_type,
                    'is_primary_contact' => $student->pivot->is_primary_contact,
                    'user' => $student->user ? [
                        'id' => $student->user->id,
                        'email' => $student->user->email,
                    ] : null,
                ];
            }) : collect();

        return Inertia::render('guardian/students/index', [
            'children' => $children,
            'gradeLevels' => collect(GradeLevel::cases())->map(fn ($level) => [
                'value' => $level->value,
                'label' => $level->value,
            ]),
        ]);
    }

    /**
     * Show the form for creating a new student
     */
    public function create(): Response
    {
        return Inertia::render('guardian/students/create', [
            'gradeLevels' => collect(GradeLevel::cases())->map(fn ($level) => [
                'value' => $level->value,
                'label' => $level->value,
            ]),
            'relationshipTypes' => RelationshipType::options(),
        ]);
    }

    /**
     * Store a newly created student
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $guardian = \App\Models\Guardian::where('user_id', $user->id)->first();
        if (!$guardian) { abort(404); }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'birthdate' => 'required|date|before:today',
            'grade_level' => 'required|in:'.implode(',', array_column(GradeLevel::cases(), 'value')),
            'gender' => 'required|in:male,female',
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'relationship_type' => 'required|in:'.implode(',', RelationshipType::values()),
            'is_primary_contact' => 'boolean',
            // All students now require login accounts
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::transaction(function () use ($validated, $guardian) {
            // Create student record
            $student = Student::create([
                'student_id' => Student::generateStudentId(),
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'birthdate' => $validated['birthdate'],
                'grade_level' => GradeLevel::from($validated['grade_level']),
                'gender' => $validated['gender'],
                'address' => $validated['address'],
                'phone' => $validated['phone'],
            ]);

            // All students now get user accounts
            $user = User::create([
                'name' => trim($validated['first_name'].' '.$validated['last_name']),
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->assignRole('student');

            // Link student to user account
            $student->update(['user_id' => $user->id]);

            // Create guardian-student relationship
            GuardianStudent::create([
                'guardian_id' => $guardian->user_id,
                'student_id' => $student->id,
                'relationship_type' => $validated['relationship_type'],
                'is_primary_contact' => $validated['is_primary_contact'] ?? false,
            ]);
        });

        return redirect()->route('guardian.students.index')
            ->with('success', 'Student added successfully.');
    }

    /**
     * Show the form for editing a student
     */
    public function edit(Student $student): Response
    {
        $user = auth()->user();
        $guardian = \App\Models\Guardian::where('user_id', $user->id)->first();
        if (!$guardian) { abort(404); }

        // Verify guardian has access to this student
        if (! $guardian->children()->where('students.id', $student->id)->exists()) {
            abort(403);
        }

        $studentData = [
            'id' => $student->id,
            'student_id' => $student->student_id,
            'first_name' => $student->first_name,
            'middle_name' => $student->middle_name,
            'last_name' => $student->last_name,
            'birthdate' => $student->birthdate->format('Y-m-d'),
            'grade_level' => $student->grade_level,
            'gender' => $student->gender,
            'address' => $student->address,
            'phone' => $student->phone,
            'user' => $student->user ? [
                'id' => $student->user->id,
                'email' => $student->user->email,
            ] : null,
        ];

        $relationship = $guardian->children()
            ->where('students.id', $student->id)
            ->first()
            ->pivot;

        return Inertia::render('guardian/students/edit', [
            'student' => $studentData,
            'relationship' => [
                'type' => $relationship->relationship_type,
                'is_primary_contact' => $relationship->is_primary_contact,
            ],
            'gradeLevels' => collect(GradeLevel::cases())->map(fn ($level) => [
                'value' => $level->value,
                'label' => $level->value,
            ]),
            'relationshipTypes' => RelationshipType::options(),
        ]);
    }

    /**
     * Update the specified student
     */
    public function update(Request $request, Student $student): RedirectResponse
    {
        $user = auth()->user();
        $guardian = \App\Models\Guardian::where('user_id', $user->id)->first();
        if (!$guardian) { abort(404); }

        // Verify guardian has access to this student
        if (! $guardian->children()->where('students.id', $student->id)->exists()) {
            abort(403);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'birthdate' => 'required|date|before:today',
            'grade_level' => 'required|in:'.implode(',', array_column(GradeLevel::cases(), 'value')),
            'gender' => 'required|in:male,female',
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'relationship_type' => 'required|in:'.implode(',', RelationshipType::values()),
            'is_primary_contact' => 'boolean',
        ]);

        DB::transaction(function () use ($validated, $guardian, $student, $user) {
            // Update student record
            $student->update([
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'birthdate' => $validated['birthdate'],
                'grade_level' => GradeLevel::from($validated['grade_level']),
                'gender' => $validated['gender'],
                'address' => $validated['address'],
                'phone' => $validated['phone'],
            ]);

            // Update relationship
            GuardianStudent::where('guardian_id', $guardian->user_id)
                ->where('student_id', $student->id)
                ->update([
                    'relationship_type' => $validated['relationship_type'],
                    'is_primary_contact' => $validated['is_primary_contact'] ?? false,
                ]);
        });

        return redirect()->route('guardian.students.index')
            ->with('success', 'Student updated successfully.');
    }
}

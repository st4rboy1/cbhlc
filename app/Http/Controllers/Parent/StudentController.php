<?php

namespace App\Http\Controllers\Parent;

use App\Enums\GradeLevel;
use App\Http\Controllers\Controller;
use App\Models\ParentStudent;
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
     * Display the parent's children
     */
    public function index(): Response
    {
        $parent = auth()->user();

        $children = $parent->children()
            ->with('user')
            ->get()
            ->map(function (Student $student) {
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
                    'has_login' => $student->user_id !== null,
                    'user' => $student->user ? [
                        'id' => $student->user->id,
                        'email' => $student->user->email,
                    ] : null,
                ];
            });

        return Inertia::render('parent/students/index', [
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
        return Inertia::render('parent/students/create', [
            'gradeLevels' => collect(GradeLevel::cases())->map(fn ($level) => [
                'value' => $level->value,
                'label' => $level->value,
            ]),
            'relationshipTypes' => [
                ['value' => 'father', 'label' => 'Father'],
                ['value' => 'mother', 'label' => 'Mother'],
                ['value' => 'guardian', 'label' => 'Guardian'],
                ['value' => 'grandparent', 'label' => 'Grandparent'],
                ['value' => 'other', 'label' => 'Other'],
            ],
        ]);
    }

    /**
     * Store a newly created student
     */
    public function store(Request $request): RedirectResponse
    {
        $parent = auth()->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'birthdate' => 'required|date|before:today',
            'grade_level' => 'required|in:'.implode(',', array_column(GradeLevel::cases(), 'value')),
            'gender' => 'required|in:male,female',
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'relationship_type' => 'required|in:father,mother,guardian,grandparent,other',
            'is_primary_contact' => 'boolean',
            'create_login' => 'boolean',
            'email' => 'required_if:create_login,true|nullable|email|unique:users,email',
            'password' => 'required_if:create_login,true|nullable|confirmed|'.Rules\Password::defaults(),
        ]);

        DB::transaction(function () use ($validated, $parent) {
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

            // Create user account if requested
            if ($validated['create_login'] && $validated['email']) {
                $user = User::create([
                    'name' => trim($validated['first_name'].' '.$validated['last_name']),
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                ]);

                $user->assignRole('student');

                // Link student to user account
                $student->update(['user_id' => $user->id]);
            }

            // Create parent-student relationship
            ParentStudent::create([
                'parent_id' => $parent->id,
                'student_id' => $student->id,
                'relationship_type' => $validated['relationship_type'],
                'is_primary_contact' => $validated['is_primary_contact'] ?? false,
            ]);
        });

        return redirect()->route('parent.students.index')
            ->with('success', 'Student added successfully.');
    }

    /**
     * Show the form for editing a student
     */
    public function edit(Student $student): Response
    {
        $parent = auth()->user();

        // Verify parent has access to this student
        if (! $parent->children()->where('students.id', $student->id)->exists()) {
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
            'has_login' => $student->user_id !== null,
            'user' => $student->user ? [
                'id' => $student->user->id,
                'email' => $student->user->email,
            ] : null,
        ];

        $relationship = $parent->children()
            ->where('students.id', $student->id)
            ->first()
            ->pivot;

        return Inertia::render('parent/students/edit', [
            'student' => $studentData,
            'relationship' => [
                'type' => $relationship->relationship_type,
                'is_primary_contact' => $relationship->is_primary_contact,
            ],
            'gradeLevels' => collect(GradeLevel::cases())->map(fn ($level) => [
                'value' => $level->value,
                'label' => $level->value,
            ]),
            'relationshipTypes' => [
                ['value' => 'father', 'label' => 'Father'],
                ['value' => 'mother', 'label' => 'Mother'],
                ['value' => 'guardian', 'label' => 'Guardian'],
                ['value' => 'grandparent', 'label' => 'Grandparent'],
                ['value' => 'other', 'label' => 'Other'],
            ],
        ]);
    }

    /**
     * Update the specified student
     */
    public function update(Request $request, Student $student): RedirectResponse
    {
        $parent = auth()->user();

        // Verify parent has access to this student
        if (! $parent->children()->where('students.id', $student->id)->exists()) {
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
            'relationship_type' => 'required|in:father,mother,guardian,grandparent,other',
            'is_primary_contact' => 'boolean',
        ]);

        DB::transaction(function () use ($validated, $parent, $student) {
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
            $parent->children()
                ->where('students.id', $student->id)
                ->updateExistingPivot($student->id, [
                    'relationship_type' => $validated['relationship_type'],
                    'is_primary_contact' => $validated['is_primary_contact'] ?? false,
                ]);
        });

        return redirect()->route('parent.students.index')
            ->with('success', 'Student updated successfully.');
    }

    /**
     * Create login account for student
     */
    public function createLogin(Request $request, Student $student): RedirectResponse
    {
        $parent = auth()->user();

        // Verify parent has access to this student
        if (! $parent->children()->where('students.id', $student->id)->exists()) {
            abort(403);
        }

        // Check if student already has login
        if ($student->user_id) {
            return redirect()->back()->with('error', 'Student already has a login account.');
        }

        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => trim($student->first_name.' '.$student->last_name),
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole('student');

        $student->update(['user_id' => $user->id]);

        return redirect()->back()
            ->with('success', 'Login account created successfully for '.$student->first_name.'.');
    }

}

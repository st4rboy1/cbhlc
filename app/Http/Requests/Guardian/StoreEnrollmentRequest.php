<?php

namespace App\Http\Requests\Guardian;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\Quarter;
use App\Models\Enrollment;
use App\Models\EnrollmentPeriod;
use App\Models\Guardian;
use App\Models\GuardianStudent;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreEnrollmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->hasRole('guardian');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'enrollment_period' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    // Validate active enrollment period exists
                    $activePeriod = EnrollmentPeriod::active()->first();

                    if (! $activePeriod) {
                        $fail('Enrollment is currently closed. No active enrollment period available.');

                        return;
                    }

                    if (! $activePeriod->isOpen()) {
                        $fail('Enrollment period is not currently open. The deadline has passed.');

                        return;
                    }

                    // Validate student eligibility if student_id is present
                    $studentId = $this->input('student_id');
                    if ($studentId) {
                        $student = Student::find($studentId);
                        if ($student) {
                            $eligibilityErrors = Enrollment::canEnrollForPeriod($activePeriod, $student);
                            if (! empty($eligibilityErrors)) {
                                $fail($eligibilityErrors[0]);
                            }
                        }
                    }
                },
            ],
            'student_id' => [
                'required',
                'exists:students,id',
                function ($attribute, $value, $fail) {
                    // Get Guardian model for authenticated user
                    $guardian = Guardian::where('user_id', Auth::id())->first();

                    // Verify guardian exists and has access to this student
                    if (! $guardian || ! GuardianStudent::where('guardian_id', $guardian->id)
                        ->where('student_id', $value)
                        ->exists()) {
                        $fail('You are not authorized to enroll this student.');

                        return;
                    }

                    // Validate enrollment period eligibility
                    $activePeriod = EnrollmentPeriod::active()->first();
                    if ($activePeriod) {
                        $student = Student::find($value);
                        if ($student) {
                            $eligibilityErrors = Enrollment::canEnrollForPeriod($activePeriod, $student);
                            if (! empty($eligibilityErrors)) {
                                $fail($eligibilityErrors[0]);

                                return;
                            }
                        }
                    }

                    // Check for pending enrollments
                    if (Enrollment::where('student_id', $value)
                        ->where('status', EnrollmentStatus::PENDING)
                        ->exists()) {
                        $fail('This student already has a pending enrollment. Please wait for it to be processed before submitting another one.');

                        return;
                    }

                    // Check for active enrollment
                    if (Enrollment::where('student_id', $value)
                        ->where('status', EnrollmentStatus::ENROLLED)
                        ->exists()) {
                        $fail('This student has an active enrollment. Please wait for the current enrollment to be completed before applying for another year.');

                        return;
                    }
                },
            ],
            'quarter' => ['required', Rule::in(Quarter::values())],
            'grade_level' => [
                'required',
                Rule::in(GradeLevel::values()),
                function ($attribute, $value, $fail) {
                    $studentId = $this->input('student_id');
                    if (! $studentId) {
                        return;
                    }

                    // Check if student has previous enrollments
                    $previousEnrollment = Enrollment::where('student_id', $studentId)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($previousEnrollment) {
                        try {
                            /** @var GradeLevel|string $previousGradeLevel */
                            $previousGradeLevel = $previousEnrollment->grade_level;
                            $previousGradeEnum = is_string($previousGradeLevel)
                                ? GradeLevel::from($previousGradeLevel)
                                : $previousGradeLevel;

                            $newGradeEnum = GradeLevel::from($value);

                            // Students cannot enroll in a lower grade than their previous enrollment
                            if ($newGradeEnum->order() < $previousGradeEnum->order()) {
                                $fail('Students cannot enroll in a grade level lower than their previous enrollment.');
                            }
                        } catch (\ValueError $e) {
                            // If grade level enum conversion fails, skip validation
                        }
                    }
                },
            ],
            'payment_plan' => ['required', 'in:annual,semestral,quarterly,monthly'],
        ];
    }

    /**
     * Get custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Please select a student.',
            'student_id.exists' => 'Selected student does not exist.',
            'quarter.required' => 'Quarter is required.',
            'quarter.in' => 'Invalid quarter selected.',
            'grade_level.required' => 'Grade level is required.',
            'grade_level.in' => 'Invalid grade level selected.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure student_id is integer if provided
        if ($this->has('student_id')) {
            $this->merge([
                'student_id' => (int) $this->student_id,
            ]);
        }
    }
}

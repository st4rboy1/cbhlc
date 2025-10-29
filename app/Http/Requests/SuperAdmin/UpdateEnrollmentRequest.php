<?php

namespace App\Http\Requests\SuperAdmin;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEnrollmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('super_admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'guardian_id' => ['required', 'exists:guardians,id'],
            'grade_level' => ['required', Rule::in(GradeLevel::values())],
            'school_year_id' => ['required', 'exists:school_years,id'],
            'quarter' => ['required', 'string'],
            'type' => ['required', 'in:new,continuing,returnee,transferee'],
            'previous_school' => ['nullable', 'string', 'max:255'],
            'payment_plan' => ['required', 'in:annual,semestral,quarterly,monthly'],
            'status' => ['required', 'string', 'in:'.implode(',', array_column(EnrollmentStatus::cases(), 'value'))],
        ];
    }
}

<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentRequest extends FormRequest
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
            'grade_level' => ['required', 'string'],
            'school_year' => ['required', 'string', 'regex:/^\d{4}-\d{4}$/'],
            'quarter' => ['required', 'string'],
            'type' => ['required', 'in:new,continuing,returnee,transferee'],
            'previous_school' => ['nullable', 'string', 'max:255'],
            'payment_plan' => ['required', 'in:annual,semestral,quarterly,monthly'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'school_year.regex' => 'School year must be in the format YYYY-YYYY (e.g., 2024-2025).',
        ];
    }
}
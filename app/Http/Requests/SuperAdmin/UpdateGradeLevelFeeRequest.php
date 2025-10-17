<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGradeLevelFeeRequest extends FormRequest
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
        $gradeLevelFeeId = $this->route('grade_level_fee');

        return [
            'grade_level' => [
                'required',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('grade_level_fees')
                    ->ignore($gradeLevelFeeId)
                    ->where(function ($query) {
                        return $query->where('school_year', $this->school_year);
                    }),
            ],
            'school_year' => ['required', 'string', 'regex:/^\d{4}-\d{4}$/'],
            'tuition_fee' => ['required', 'numeric', 'min:0'],
            'miscellaneous_fee' => ['required', 'numeric', 'min:0'],
            'other_fees' => ['nullable', 'numeric', 'min:0'],
            'payment_terms' => ['required', 'string', 'in:ANNUAL,SEMESTRAL,QUARTERLY,MONTHLY'],
            'is_active' => ['boolean'],
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
            'grade_level.unique' => 'A fee structure for this grade level and school year already exists.',
        ];
    }
}

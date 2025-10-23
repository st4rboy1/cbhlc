<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class StoreGradeLevelFeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('grade_level_fees.manage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'grade_level' => [
                'required',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('grade_level_fees')->where(function ($query) {
                    return $query->where('school_year_id', $this->school_year_id)
                        ->where('payment_terms', $this->payment_terms);
                }),
            ],
            'school_year_id' => ['required', 'exists:school_years,id'],
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
            'grade_level.unique' => 'A fee structure for this grade level, school year, and payment term already exists.',
        ];
    }
}

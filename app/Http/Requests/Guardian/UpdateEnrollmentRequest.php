<?php

namespace App\Http\Requests\Guardian;

use App\Enums\GradeLevel;
use App\Enums\Quarter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEnrollmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quarter' => ['required', Rule::in(Quarter::values())],
            'grade_level' => ['required', Rule::in(GradeLevel::values())],
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
            'quarter.required' => 'The quarter field is required.',
            'quarter.in' => 'The selected quarter is invalid.',
            'grade_level.required' => 'The grade level field is required.',
            'grade_level.in' => 'The selected grade level is invalid.',
        ];
    }
}

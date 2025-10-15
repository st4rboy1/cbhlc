<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentPeriodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled via middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_year' => 'required|string|regex:/^\d{4}-\d{4}$/|unique:enrollment_periods,school_year',
            'start_date' => 'required|date|after:yesterday',
            'end_date' => 'required|date|after:start_date',
            'early_registration_deadline' => 'nullable|date|after_or_equal:start_date|before:end_date',
            'regular_registration_deadline' => 'required|date|after_or_equal:start_date|before_or_equal:end_date',
            'late_registration_deadline' => 'nullable|date|after:regular_registration_deadline|before_or_equal:end_date',
            'description' => 'nullable|string|max:1000',
            'allow_new_students' => 'boolean',
            'allow_returning_students' => 'boolean',
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
            'school_year.required' => 'The school year is required.',
            'school_year.regex' => 'The school year must be in the format YYYY-YYYY (e.g., 2025-2026).',
            'school_year.unique' => 'An enrollment period for this school year already exists.',
            'start_date.required' => 'The start date is required.',
            'start_date.after' => 'The start date must be today or a future date.',
            'end_date.required' => 'The end date is required.',
            'end_date.after' => 'The end date must be after the start date.',
            'early_registration_deadline.after_or_equal' => 'The early registration deadline must be on or after the start date.',
            'early_registration_deadline.before' => 'The early registration deadline must be before the end date.',
            'regular_registration_deadline.required' => 'The regular registration deadline is required.',
            'regular_registration_deadline.after_or_equal' => 'The regular registration deadline must be on or after the start date.',
            'regular_registration_deadline.before_or_equal' => 'The regular registration deadline must be on or before the end date.',
            'late_registration_deadline.after' => 'The late registration deadline must be after the regular registration deadline.',
            'late_registration_deadline.before_or_equal' => 'The late registration deadline must be on or before the end date.',
            'description.max' => 'The description must not exceed 1000 characters.',
        ];
    }
}

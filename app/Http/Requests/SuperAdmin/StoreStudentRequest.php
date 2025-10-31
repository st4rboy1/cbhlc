<?php

namespace App\Http\Requests\SuperAdmin;

use App\Enums\Gender;
use App\Enums\GradeLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'birthdate' => ['required', 'date', 'before:today'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'gender' => ['required', Rule::in(Gender::values())],
            'nationality' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'unique:students'],
            'grade_level' => ['required', Rule::in(GradeLevel::values())],
            'guardian_ids' => ['required', 'array', 'min:1'],
            'guardian_ids.*' => ['exists:guardians,id'],
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
            'guardian_ids.required' => 'At least one guardian must be selected.',
            'guardian_ids.min' => 'At least one guardian must be selected.',
        ];
    }
}

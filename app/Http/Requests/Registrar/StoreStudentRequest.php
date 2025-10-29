<?php

namespace App\Http\Requests\Registrar;

use App\Enums\GradeLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->hasRole('registrar');
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
            'birthdate' => ['required', 'date', 'before:today', 'after:'.now()->subYears(18)->format('Y-m-d')],
            'gender' => ['required', 'in:Male,Female'],
            'address' => ['required', 'string', 'max:500'],
            'contact_number' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\(\)\s]+$/'],
            'email' => ['nullable', 'email', 'max:255', 'unique:students,email'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', 'string', 'max:100'],
            'grade_level' => ['nullable', Rule::in(GradeLevel::values())],
            'section' => ['nullable', 'string', 'max:50'],
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
            'first_name.required' => 'First name is required.',
            'first_name.max' => 'First name must not exceed 100 characters.',
            'last_name.required' => 'Last name is required.',
            'last_name.max' => 'Last name must not exceed 100 characters.',
            'birthdate.required' => 'Birthdate is required.',
            'birthdate.before' => 'Birthdate must be in the past.',
            'birthdate.after' => 'Student must be less than 18 years old.',
            'gender.required' => 'Gender is required.',
            'gender.in' => 'Gender must be either Male or Female.',
            'address.required' => 'Address is required.',
            'address.max' => 'Address must not exceed 500 characters.',
            'contact_number.regex' => 'Contact number format is invalid.',
            'email.email' => 'Please provide a valid email address.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim whitespace from string inputs
        $this->merge([
            'first_name' => trim($this->first_name ?? ''),
            'middle_name' => $this->middle_name ? trim($this->middle_name) : null,
            'last_name' => trim($this->last_name ?? ''),
            'address' => trim($this->address ?? ''),
            'contact_number' => $this->contact_number ? trim($this->contact_number) : null,
            'email' => $this->email ? trim(strtolower($this->email)) : null,
            'birth_place' => $this->birth_place ? trim($this->birth_place) : null,
            'nationality' => $this->nationality ? trim($this->nationality) : null,
            'religion' => $this->religion ? trim($this->religion) : null,
            'grade_level' => $this->grade_level ? trim($this->grade_level) : null,
            'section' => $this->section ? trim($this->section) : null,
        ]);
    }
}

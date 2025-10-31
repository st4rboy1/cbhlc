<?php

namespace App\Http\Requests\Guardian;

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
        // Guardian can create students
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
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'birthdate' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:Male,Female'],
            'address' => ['required', 'string', 'max:500'],
            'contact_number' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\(\)\s]+$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'grade_level' => ['required', Rule::in(GradeLevel::values())],
            'birth_place' => ['required', 'string', 'max:255'],
            'nationality' => ['required', 'string', 'max:100'],
            'religion' => ['required', 'string', 'max:100'],

            // Document uploads - Birth certificate always required
            'birth_certificate' => ['required', 'file', 'mimes:jpeg,jpg,png', 'max:51200'], // 50MB in kilobytes

            // Conditional documents: required for Grade 1 and above, optional for Kinder
            'report_card' => [
                Rule::when(fn () => $this->input('grade_level') !== 'Kinder', 'required', 'nullable'),
                'file',
                'mimes:jpeg,jpg,png',
                'max:51200',
            ],
            'form_138' => [
                Rule::when(fn () => $this->input('grade_level') !== 'Kinder', 'required', 'nullable'),
                'file',
                'mimes:jpeg,jpg,png',
                'max:51200',
            ],
            'good_moral' => [
                Rule::when(fn () => $this->input('grade_level') !== 'Kinder', 'required', 'nullable'),
                'file',
                'mimes:jpeg,jpg,png',
                'max:51200',
            ],
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
            'gender.required' => 'Gender is required.',
            'gender.in' => 'Gender must be either Male or Female.',
            'address.required' => 'Address is required.',
            'contact_number.regex' => 'Contact number format is invalid.',
            'email.email' => 'Please provide a valid email address.',
            'birth_place.required' => 'Birth place is required.',
            'nationality.required' => 'Nationality is required.',
            'religion.required' => 'Religion is required.',

            // Document validation messages
            'birth_certificate.required' => 'Birth certificate is required.',
            'report_card.required' => 'Report card is required for Grade 1 and above.',
            'form_138.required' => 'Form 138 is required for Grade 1 and above.',
            'good_moral.required' => 'Good moral certificate is required for Grade 1 and above.',
            'birth_certificate.mimes' => 'Birth certificate must be a JPEG or PNG image.',
            'birth_certificate.max' => 'Birth certificate file size must not exceed 50MB.',
            'report_card.mimes' => 'Report card must be a JPEG or PNG image.',
            'report_card.max' => 'Report card file size must not exceed 50MB.',
            'form_138.mimes' => 'Form 138 must be a JPEG or PNG image.',
            'form_138.max' => 'Form 138 file size must not exceed 50MB.',
            'good_moral.mimes' => 'Good moral certificate must be a JPEG or PNG image.',
            'good_moral.max' => 'Good moral certificate file size must not exceed 50MB.',
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
        ]);
    }
}

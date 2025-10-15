<?php

namespace App\Http\Requests\Guardian;

use App\Models\Guardian;
use App\Models\GuardianStudent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if guardian has access to this student
        $student = $this->route('student');

        // Get Guardian model for authenticated user
        $guardian = Guardian::where('user_id', Auth::id())->first();

        return Auth::check()
            && Auth::user()->hasRole('guardian')
            && $guardian
            && GuardianStudent::where('guardian_id', $guardian->id)
                ->where('student_id', $student->id)
                ->exists();
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
            'email' => ['nullable', 'email', 'max:255'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', 'string', 'max:100'],
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
            'contact_number.regex' => 'Contact number format is invalid.',
            'email.email' => 'Please provide a valid email address.',
        ];
    }

    /**
     * Get the error messages for the authorization failure.
     */
    public function failedAuthorization()
    {
        throw new \Illuminate\Auth\Access\AuthorizationException('You do not have access to update this student.');
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

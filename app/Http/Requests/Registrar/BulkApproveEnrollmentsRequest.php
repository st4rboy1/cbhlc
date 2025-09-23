<?php

namespace App\Http\Requests\Registrar;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BulkApproveEnrollmentsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && (
            Auth::user()->hasRole('registrar') ||
            Auth::user()->hasRole('administrator') ||
            Auth::user()->hasRole('super_admin')
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'enrollment_ids' => ['required', 'array', 'min:1'],
            'enrollment_ids.*' => [
                'required',
                'integer',
                'exists:enrollments,id',
                function ($attribute, $value, $fail) {
                    $enrollment = Enrollment::find($value);
                    if ($enrollment && $enrollment->status !== EnrollmentStatus::PENDING) {
                        $fail("Enrollment ID {$value} is not in pending status and cannot be approved.");
                    }
                },
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
            'enrollment_ids.required' => 'Please select at least one enrollment.',
            'enrollment_ids.array' => 'Invalid enrollment selection.',
            'enrollment_ids.min' => 'Please select at least one enrollment.',
            'enrollment_ids.*.exists' => 'One or more selected enrollments do not exist.',
            'enrollment_ids.*.integer' => 'Invalid enrollment ID format.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure enrollment_ids are integers
        if ($this->has('enrollment_ids') && is_array($this->enrollment_ids)) {
            $this->merge([
                'enrollment_ids' => array_map('intval', $this->enrollment_ids),
            ]);
        }
    }
}

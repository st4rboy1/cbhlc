<?php

namespace App\Http\Requests\SuperAdmin;

use App\Enums\EnrollmentType;
use App\Enums\GradeLevel;
use App\Enums\PaymentPlan;
use App\Enums\Quarter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'grade_level' => ['required', Rule::in(GradeLevel::values())],
            'school_year_id' => ['required', 'exists:school_years,id'],
            'quarter' => ['required', Rule::in(Quarter::values())],
            'type' => ['required', Rule::in(EnrollmentType::values())],
            'previous_school' => ['nullable', 'required_if:type,transferee', 'string', 'max:255'],
            'payment_plan' => ['required', Rule::in(PaymentPlan::values())],
        ];
    }
}

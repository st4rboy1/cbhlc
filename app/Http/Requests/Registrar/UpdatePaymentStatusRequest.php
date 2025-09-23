<?php

namespace App\Http\Requests\Registrar;

use App\Enums\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdatePaymentStatusRequest extends FormRequest
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
            'amount_paid' => ['required', 'integer', 'min:0', 'max:999999999'],
            'payment_status' => ['required', 'string', Rule::in(PaymentStatus::values())],
            'remarks' => ['nullable', 'string', 'max:500'],
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
            'amount_paid.required' => 'Amount paid is required.',
            'amount_paid.integer' => 'Amount paid must be a valid number.',
            'amount_paid.min' => 'Amount paid cannot be negative.',
            'amount_paid.max' => 'Amount paid exceeds maximum allowed value.',
            'payment_status.required' => 'Payment status is required.',
            'payment_status.in' => 'Invalid payment status selected.',
            'remarks.max' => 'Remarks must not exceed 500 characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure amount_paid is integer
        if ($this->has('amount_paid')) {
            $this->merge([
                'amount_paid' => (int) $this->amount_paid,
            ]);
        }

        // Trim remarks if provided
        if ($this->has('remarks')) {
            $this->merge([
                'remarks' => $this->remarks ? trim($this->remarks) : null,
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $enrollment = $this->route('enrollment');

            // Validate amount paid based on enrollment total
            if ($enrollment && $this->amount_paid > $enrollment->net_amount_cents) {
                $validator->errors()->add('amount_paid', 'Amount paid cannot exceed the total enrollment amount.');
            }

            // Validate payment status logic (only if enrollment exists)
            if ($enrollment) {
                if ($this->payment_status === PaymentStatus::PAID->value) {
                    if ($this->amount_paid < $enrollment->net_amount_cents) {
                        $validator->errors()->add('payment_status', 'Cannot mark as PAID when amount is not fully paid.');
                    }
                }

                if ($this->payment_status === PaymentStatus::PARTIAL->value) {
                    if ($this->amount_paid == 0) {
                        $validator->errors()->add('payment_status', 'Cannot mark as PARTIAL when no payment has been made.');
                    }
                    if ($this->amount_paid >= $enrollment->net_amount_cents) {
                        $validator->errors()->add('payment_status', 'Cannot mark as PARTIAL when amount is fully paid.');
                    }
                }

                if ($this->payment_status === PaymentStatus::PENDING->value && $this->amount_paid > 0) {
                    $validator->errors()->add('payment_status', 'Cannot mark as PENDING when payment has been made.');
                }
            }
        });
    }
}

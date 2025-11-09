<?php

namespace App\Http\Requests\SuperAdmin;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReceiptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['super_admin', 'administrator', 'registrar']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_id' => ['required_without_all:invoice_id', 'exists:payments,id'],
            'invoice_id' => ['required_without_all:payment_id', 'exists:invoices,id'],
            'receipt_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', Rule::in(PaymentMethod::values())],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'payment_id.exists' => 'The selected payment does not exist.',
            'payment_id.required_without_all' => 'Either a payment or an invoice must be selected.',
            'invoice_id.exists' => 'The selected invoice does not exist.',
            'invoice_id.required_without_all' => 'Either a payment or an invoice must be selected.',
            'receipt_date.required' => 'Receipt date is required.',
            'amount.required' => 'Amount is required.',
            'amount.min' => 'Amount must be greater than zero.',
            'payment_method.required' => 'Payment method is required.',
        ];
    }
}

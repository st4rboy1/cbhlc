<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
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
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after:invoice_date'],
            'status' => ['required', 'in:draft,sent,paid,overdue,cancelled'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'exists:invoice_items,id'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
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
            'items.required' => 'At least one invoice item is required.',
            'items.min' => 'At least one invoice item is required.',
            'due_date.after' => 'Due date must be after invoice date.',
        ];
    }
}

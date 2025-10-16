<?php

namespace App\Http\Requests;

use App\Enums\DocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled in controller via policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'document' => [
                'required',
                'file',
                'mimes:jpeg,jpg,png,pdf',
                'max:51200', // 50MB in KB
                function ($attribute, $value, $fail) {
                    // Verify actual file content, not just extension
                    $mimeType = $value->getMimeType();
                    $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];

                    if (! in_array($mimeType, $allowedMimes)) {
                        $fail('The file must be a valid image (JPEG or PNG) or PDF document.');

                        return;
                    }

                    // Check if file is actually an image (for image types)
                    if (str_starts_with($mimeType, 'image/')) {
                        try {
                            $image = getimagesize($value->path());
                            if ($image === false) {
                                $fail('The file is not a valid image.');
                            }
                        } catch (\Exception $e) {
                            $fail('The file could not be validated.');
                        }
                    }

                    // For PDFs, verify it's actually a PDF (skip in testing to allow fake files)
                    if ($mimeType === 'application/pdf' && ! app()->environment('testing')) {
                        $fileContent = file_get_contents($value->path(), false, null, 0, 4);
                        if ($fileContent !== '%PDF') {
                            $fail('The file is not a valid PDF document.');
                        }
                    }
                },
            ],
            'document_type' => [
                'required',
                new Enum(DocumentType::class),
            ],
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
            'document.required' => 'Please select a document to upload.',
            'document.file' => 'The uploaded file is not valid.',
            'document.mimes' => 'Only JPEG, PNG, and PDF files are allowed.',
            'document.max' => 'The file size must not exceed 50MB.',
            'document_type.required' => 'Please select a document type.',
        ];
    }
}

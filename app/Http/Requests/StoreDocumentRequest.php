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
                'mimes:jpeg,jpg,png',
                'max:51200', // 50MB in KB
                function ($attribute, $value, $fail) {
                    // Verify actual file content, not just extension
                    $mimeType = $value->getMimeType();
                    $allowedMimes = ['image/jpeg', 'image/png'];

                    if (! in_array($mimeType, $allowedMimes)) {
                        $fail('The file must be a valid JPEG or PNG image.');

                        return;
                    }

                    // Check if file is actually an image
                    try {
                        $image = getimagesize($value->path());
                        if ($image === false) {
                            $fail('The file is not a valid image.');
                        }
                    } catch (\Exception $e) {
                        $fail('The file could not be validated.');
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
            'document.mimes' => 'Only JPEG and PNG image files are allowed.',
            'document.max' => 'The file size must not exceed 50MB.',
            'document_type.required' => 'Please select a document type.',
        ];
    }
}

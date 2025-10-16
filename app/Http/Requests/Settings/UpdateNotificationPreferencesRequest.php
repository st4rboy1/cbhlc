<?php

namespace App\Http\Requests\Settings;

use App\Models\NotificationPreference;
use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationPreferencesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'preferences' => 'required|array',
            'preferences.*' => 'array',
            'preferences.*.email_enabled' => 'boolean',
            'preferences.*.database_enabled' => 'boolean',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Only validate types if preferences is an array
            if (! is_array($this->preferences)) {
                return;
            }

            $availableTypes = array_keys(NotificationPreference::availableTypes());

            foreach (array_keys($this->preferences) as $type) {
                if (! in_array($type, $availableTypes)) {
                    $validator->errors()->add('preferences', "Invalid notification type: {$type}");
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'preferences.required' => 'Notification preferences are required.',
            'preferences.array' => 'Notification preferences must be an array.',
            'preferences.*.email_enabled.boolean' => 'Email preference must be true or false.',
            'preferences.*.database_enabled.boolean' => 'Database preference must be true or false.',
        ];
    }
}

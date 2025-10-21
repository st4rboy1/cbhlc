<x-mail::message>
# Additional Information Required

Hello,

We are reviewing the enrollment application for **{{ $student->first_name }} {{ $student->last_name }}** (Enrollment ID: **{{ $enrollment->enrollment_id }}**).

To continue processing the application, we need additional information from you.

## Message from School Administrator:

{{ $message }}

## Next Steps

Please log in to your account and provide the requested information as soon as possible.

<x-mail::button :url="$url">
View Enrollment Application
</x-mail::button>

If you have any questions, please contact our admissions office at {{ config('mail.from.address') }}.

Thank you for your cooperation.

Best regards,<br>
{{ config('app.name') }}<br>
Admissions Office
</x-mail::message>

<x-mail::message>
# Enrollment Application Update

Dear Parent/Guardian,

We have completed our review of the enrollment application for **{{ $studentName }}**.

<x-mail::panel>
**Application Details:**
- **Application ID:** {{ $enrollmentId }}
- **Grade Level Applied:** {{ $gradeLevel }}
- **School Year:** {{ $schoolYear }}
- **Review Date:** {{ $rejectedAt }}
</x-mail::panel>

Unfortunately, we are unable to approve the application at this time for the following reason:

<x-mail::panel>
{{ $reason }}
</x-mail::panel>

## What You Can Do

@foreach ($nextSteps as $step)
- {{ $step }}
@endforeach

## We're Here to Help

Our admissions team is available to discuss your application and provide guidance on next steps:

@foreach ($contactInfo as $key => $value)
- **{{ ucwords(str_replace('_', ' ', $key)) }}:** {{ $value }}
@endforeach

<x-mail::button :url="config('app.url') . '/guardian/enrollments'">
View Application
</x-mail::button>

We understand this may be disappointing news. Please know that we are committed to helping every student find the right educational path, and we encourage you to reach out to discuss your options.

Sincerely,<br>
CBHLC Admissions Team
</x-mail::message>
<x-mail::message>
# Enrollment Application Received

Dear Parent/Guardian,

We have successfully received the enrollment application for **{{ $studentName }}**.

<x-mail::panel>
**Application Details:**
- **Application ID:** {{ $enrollmentId }}
- **Grade Level:** {{ $gradeLevel }}
- **School Year:** {{ $schoolYear }}
- **Submitted On:** {{ $submittedAt }}
</x-mail::panel>

## What Happens Next?

@foreach ($nextSteps as $step)
- {{ $step }}
@endforeach

Your application is now in our review queue. We will carefully evaluate all submitted information and documents to ensure a smooth enrollment process.

<x-mail::button :url="config('app.url') . '/guardian/enrollments'">
View Application Status
</x-mail::button>

If you have any questions or need to submit additional documents, please don't hesitate to contact our admissions office.

Thank you for choosing Christian Bible Heritage Learning Center!

Best regards,<br>
CBHLC Admissions Team
</x-mail::message>
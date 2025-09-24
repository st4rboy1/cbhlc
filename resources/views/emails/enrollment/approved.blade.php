<x-mail::message>
# 🎉 Enrollment Approved!

Dear Parent/Guardian,

We are pleased to inform you that the enrollment application for **{{ $studentName }}** has been **APPROVED**!

<x-mail::panel>
**Enrollment Details:**
- **Application ID:** {{ $enrollmentId }}
- **Grade Level:** {{ $gradeLevel }}
- **School Year:** {{ $schoolYear }}
- **Approved On:** {{ $approvedAt }}
</x-mail::panel>

## Fee Information

<x-mail::table>
| Description | Amount |
|:----------- | ------:|
| Tuition Fee | ₱{{ $tuitionFee }} |
| **Total Amount** | **₱{{ $totalAmount }}** |
@if($paymentDueDate)
| Payment Due Date | {{ $paymentDueDate }} |
@endif
</x-mail::table>

## Next Steps

To complete the enrollment process, please:

@foreach ($nextSteps as $step)
- {{ $step }}
@endforeach

<x-mail::button :url="config('app.url') . '/guardian/billing'">
View Billing Information
</x-mail::button>

## Contact Information

If you have any questions, please contact us:

@foreach ($contactInfo as $key => $value)
- **{{ ucwords(str_replace('_', ' ', $key)) }}:** {{ $value }}
@endforeach

Welcome to the CBHLC family! We look forward to partnering with you in your child's educational journey.

Warm regards,<br>
Christian Bible Heritage Learning Center
</x-mail::message>
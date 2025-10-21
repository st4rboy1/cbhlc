<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4F46E5;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 30px 20px;
            background-color: #f9f9f9;
        }
        .highlight {
            background-color: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 15px;
            margin: 20px 0;
        }
        .details {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .details table {
            width: 100%;
        }
        .details td {
            padding: 8px 0;
        }
        .details td:first-child {
            font-weight: bold;
            width: 40%;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4F46E5;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Payment Reminder</h1>
        </div>

        <div class="content">
            <p>Dear {{ $enrollment->guardian->first_name ?? 'Guardian' }},</p>

            <div class="highlight">
                <strong>Payment Due in {{ $daysUntilDue }} {{ Str::plural('day', $daysUntilDue) }}</strong>
            </div>

            <p>This is a friendly reminder that a payment for your child's enrollment is due soon.</p>

            <div class="details">
                <h3>Enrollment Details</h3>
                <table>
                    <tr>
                        <td>Student Name:</td>
                        <td>{{ $enrollment->student->first_name }} {{ $enrollment->student->last_name }}</td>
                    </tr>
                    <tr>
                        <td>Grade Level:</td>
                        <td>{{ $enrollment->grade_level }}</td>
                    </tr>
                    <tr>
                        <td>School Year:</td>
                        <td>{{ $enrollment->school_year }}</td>
                    </tr>
                    <tr>
                        <td>Payment Due Date:</td>
                        <td>{{ $enrollment->payment_due_date->format('F d, Y') }}</td>
                    </tr>
                    <tr>
                        <td>Amount Due:</td>
                        <td><strong>₱{{ number_format($enrollment->balance, 2) }}</strong></td>
                    </tr>
                </table>
            </div>

            <p>Please ensure your payment is made on or before the due date to avoid any inconvenience.</p>

            <center>
                <a href="{{ url('/guardian/enrollments/' . $enrollment->id) }}" class="button">View Enrollment Details</a>
            </center>

            <p>If you have any questions or concerns, please don't hesitate to contact our office.</p>

            <p>Thank you for your cooperation.</p>

            <p>
                Best regards,<br>
                <strong>Christian Bible Heritage Learning Center</strong>
            </p>
        </div>

        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>Christian Bible Heritage Learning Center</p>
        </div>
    </div>
</body>
</html>

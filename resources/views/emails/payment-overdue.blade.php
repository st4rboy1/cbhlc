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
            background-color: #DC2626;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 30px 20px;
            background-color: #f9f9f9;
        }
        .alert {
            background-color: #FEE2E2;
            border-left: 4px solid #DC2626;
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
            background-color: #DC2626;
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
            <h1>Payment Overdue Notice</h1>
        </div>

        <div class="content">
            <p>Dear {{ $enrollment->guardian->first_name ?? 'Guardian' }},</p>

            <div class="alert">
                <strong>URGENT: Payment {{ $daysOverdue }} {{ Str::plural('day', $daysOverdue) }} Overdue</strong>
            </div>

            <p>This is an important notice regarding an overdue payment for your child's enrollment at Christian Bible Heritage Learning Center.</p>

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
                        <td>Original Due Date:</td>
                        <td>{{ $enrollment->payment_due_date->format('F d, Y') }}</td>
                    </tr>
                    <tr>
                        <td>Days Overdue:</td>
                        <td><strong style="color: #DC2626;">{{ $daysOverdue }} {{ Str::plural('day', $daysOverdue) }}</strong></td>
                    </tr>
                    <tr>
                        <td>Outstanding Amount:</td>
                        <td><strong style="color: #DC2626;">₱{{ number_format($enrollment->balance, 2) }}</strong></td>
                    </tr>
                </table>
            </div>

            <p><strong>Please settle this payment as soon as possible to avoid any disruption to your child's enrollment.</strong></p>

            <center>
                <a href="{{ url('/guardian/enrollments/' . $enrollment->id) }}" class="button">Pay Now</a>
            </center>

            <p>If you have already made this payment, please disregard this notice. If you need to discuss payment arrangements, please contact our office immediately.</p>

            <p><strong>Contact Information:</strong></p>
            <ul>
                <li>Phone: {{ config('app.school_phone', 'Contact school office') }}</li>
                <li>Email: {{ config('app.school_email', 'Contact school office') }}</li>
                <li>Office Hours: Monday - Friday, 8:00 AM - 5:00 PM</li>
            </ul>

            <p>Thank you for your immediate attention to this matter.</p>

            <p>
                Sincerely,<br>
                <strong>Christian Bible Heritage Learning Center</strong><br>
                Cashier's Office
            </p>
        </div>

        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>Christian Bible Heritage Learning Center</p>
        </div>
    </div>
</body>
</html>

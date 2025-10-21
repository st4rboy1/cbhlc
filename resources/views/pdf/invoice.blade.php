<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $enrollment->enrollment_id }}</title>
    <style>
        @page { margin: 0.5in; }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .invoice-container {
            max-width: 700px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .school-name {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        .invoice-title {
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
            text-align: center;
        }
        .invoice-meta {
            margin: 20px 0;
        }
        .info-section {
            margin: 20px 0;
        }
        .info-row {
            margin: 8px 0;
        }
        .info-row strong {
            display: inline-block;
            width: 150px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .amount-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .balance-row {
            font-weight: bold;
            background-color: #e8e8e8;
        }
        .payment-history {
            margin-top: 30px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            font-style: italic;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        {{-- Header with school info --}}
        <div class="header">
            <div class="school-name">Christian Bible Heritage Learning Center</div>
            <div>{{ $settings['school_address'] ?? config('app.school_address', 'Lantapan, Bukidnon') }}</div>
            <div>{{ $settings['school_phone'] ?? config('app.school_phone', '') }} | {{ $settings['school_email'] ?? config('app.school_email', 'cbhlc@example.com') }}</div>
        </div>

        {{-- Invoice title and number --}}
        <div class="invoice-title">INVOICE</div>

        <div class="invoice-meta">
            <div class="info-row">
                <strong>Invoice Number:</strong>
                <span>{{ $enrollment->enrollment_id }}</span>
            </div>
            <div class="info-row">
                <strong>Date:</strong>
                <span>{{ $invoiceDate }}</span>
            </div>
        </div>

        {{-- Student and Guardian Info --}}
        <div class="info-section">
            <h3 style="margin-bottom: 10px; font-size: 14px;">Student Information</h3>
            <div class="info-row">
                <strong>Student Name:</strong>
                <span>{{ $enrollment->student->first_name }} {{ $enrollment->student->middle_name }} {{ $enrollment->student->last_name }}</span>
            </div>
            <div class="info-row">
                <strong>Student ID:</strong>
                <span>{{ $enrollment->student->student_id }}</span>
            </div>
            <div class="info-row">
                <strong>Grade Level:</strong>
                <span>{{ $enrollment->grade_level }}</span>
            </div>
            <div class="info-row">
                <strong>School Year:</strong>
                <span>{{ $enrollment->school_year }}</span>
            </div>
        </div>

        <div class="info-section">
            <h3 style="margin-bottom: 10px; font-size: 14px;">Guardian Information</h3>
            <div class="info-row">
                <strong>Guardian Name:</strong>
                <span>{{ $enrollment->guardian->first_name ?? '' }} {{ $enrollment->guardian->last_name ?? '' }}</span>
            </div>
        </div>

        {{-- Fee Breakdown Table --}}
        <h3 style="margin-top: 30px; margin-bottom: 10px; font-size: 14px;">Fee Breakdown</h3>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="amount-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Tuition Fee</td>
                    <td class="amount-right">₱{{ number_format($enrollment->tuition_fee, 2) }}</td>
                </tr>
                <tr>
                    <td>Miscellaneous Fees</td>
                    <td class="amount-right">₱{{ number_format($enrollment->miscellaneous_fee, 2) }}</td>
                </tr>
                @if($enrollment->laboratory_fee > 0)
                <tr>
                    <td>Laboratory Fee</td>
                    <td class="amount-right">₱{{ number_format($enrollment->laboratory_fee, 2) }}</td>
                </tr>
                @endif
                @if($enrollment->library_fee > 0)
                <tr>
                    <td>Library Fee</td>
                    <td class="amount-right">₱{{ number_format($enrollment->library_fee, 2) }}</td>
                </tr>
                @endif
                @if($enrollment->sports_fee > 0)
                <tr>
                    <td>Sports Fee</td>
                    <td class="amount-right">₱{{ number_format($enrollment->sports_fee, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>Total Amount</td>
                    <td class="amount-right">₱{{ number_format($enrollment->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Amount Paid</td>
                    <td class="amount-right">₱{{ number_format($enrollment->amount_paid, 2) }}</td>
                </tr>
                <tr class="balance-row">
                    <td>Balance Due</td>
                    <td class="amount-right">₱{{ number_format($enrollment->balance, 2) }}</td>
                </tr>
                <tr>
                    <td>Payment Status</td>
                    <td style="text-transform: uppercase; font-weight: bold;">{{ $enrollment->payment_status }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Payment History (if exists) --}}
        @if($payments->count() > 0)
        <div class="payment-history">
            <h3 style="margin-bottom: 10px; font-size: 14px;">Payment History</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                    <tr>
                        <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                        <td>₱{{ number_format($payment->amount, 2) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                        <td>{{ $payment->reference_number ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <p>This is a computer-generated invoice.</p>
            <p>For inquiries, please contact {{ $settings['school_email'] ?? config('app.school_email', 'cbhlc@example.com') }}</p>
            <p>Invoice No: {{ $enrollment->enrollment_id }} | Generated: {{ now()->format('F d, Y h:i A') }}</p>
        </div>
    </div>
</body>
</html>

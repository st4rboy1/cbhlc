<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $enrollment->enrollment_id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .school-name { font-size: 24px; font-weight: bold; }
        .document-title { font-size: 20px; margin-top: 15px; font-weight: bold; }
        .invoice-meta { margin: 20px 0; padding: 10px; background-color: #f9f9f9; }
        .info-row { margin: 5px 0; }
        .info-row strong { display: inline-block; width: 150px; }
        .student-guardian-section { margin: 20px 0; }
        .section-title { font-size: 14px; font-weight: bold; margin: 20px 0 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .fee-table td { padding: 8px; }
        .total-row { font-weight: bold; background-color: #e9e9e9; }
        .balance-row { font-weight: bold; background-color: #f5f5f5; font-size: 14px; }
        .payment-status { display: inline-block; padding: 5px 10px; border-radius: 3px; text-transform: uppercase; font-weight: bold; }
        .status-paid { background-color: #d4edda; color: #155724; }
        .status-partial { background-color: #fff3cd; color: #856404; }
        .status-unpaid { background-color: #f8d7da; color: #721c24; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #666; font-style: italic; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <div class="school-name">Christian Bible Heritage Learning Center</div>
        <div>{{ $settings['school_address'] ?? config('app.school_address', 'Lantapan, Bukidnon') }}</div>
        <div>{{ $settings['school_phone'] ?? config('app.school_phone', '') }} | {{ $settings['school_email'] ?? config('app.school_email', 'cbhlc@example.com') }}</div>
        <div class="document-title">INVOICE</div>
    </div>

    {{-- Invoice Metadata --}}
    <div class="invoice-meta">
        <div class="info-row">
            <strong>Invoice Number:</strong>
            <span>{{ $enrollment->enrollment_id }}</span>
        </div>
        <div class="info-row">
            <strong>Invoice Date:</strong>
            <span>{{ $invoiceDate }}</span>
        </div>
        <div class="info-row">
            <strong>School Year:</strong>
            <span>{{ $enrollment->schoolYear->name }}</span>
        </div>
    </div>

    {{-- Student and Guardian Information --}}
    <div class="student-guardian-section">
        <div class="section-title">BILL TO:</div>
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
            <span>{{ $enrollment->grade_level->label() }}</span>
        </div>
        <div class="info-row">
            <strong>Guardian:</strong>
            <span>{{ $enrollment->guardian->first_name }} {{ $enrollment->guardian->middle_name }} {{ $enrollment->guardian->last_name }}</span>
        </div>
        <div class="info-row">
            <strong>Contact Number:</strong>
            <span>{{ $enrollment->guardian->phone }}</span>
        </div>
        @if($enrollment->guardian->email)
        <div class="info-row">
            <strong>Email:</strong>
            <span>{{ $enrollment->guardian->email }}</span>
        </div>
        @endif
    </div>

    {{-- Fee Breakdown --}}
    <div class="section-title">FEE BREAKDOWN</div>
    <table class="fee-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right" style="width: 150px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Tuition Fee</td>
                <td class="text-right">₱{{ number_format($enrollment->tuition_fee, 2) }}</td>
            </tr>
            <tr>
                <td>Miscellaneous Fee</td>
                <td class="text-right">₱{{ number_format($enrollment->miscellaneous_fee, 2) }}</td>
            </tr>
            @if($enrollment->laboratory_fee > 0)
            <tr>
                <td>Laboratory Fee</td>
                <td class="text-right">₱{{ number_format($enrollment->laboratory_fee, 2) }}</td>
            </tr>
            @endif
            @if($enrollment->library_fee > 0)
            <tr>
                <td>Library Fee</td>
                <td class="text-right">₱{{ number_format($enrollment->library_fee, 2) }}</td>
            </tr>
            @endif
            @if($enrollment->sports_fee > 0)
            <tr>
                <td>Sports Fee</td>
                <td class="text-right">₱{{ number_format($enrollment->sports_fee, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>TOTAL AMOUNT DUE</td>
                <td class="text-right">₱{{ number_format($enrollment->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Amount Paid</td>
                <td class="text-right">₱{{ number_format($enrollment->amount_paid, 2) }}</td>
            </tr>
            <tr class="balance-row">
                <td>OUTSTANDING BALANCE</td>
                <td class="text-right">₱{{ number_format($enrollment->balance, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Payment Status --}}
    <div style="margin-top: 20px;">
        <strong>Payment Status:</strong>
        @php
            $statusValue = $enrollment->payment_status->value ?? $enrollment->payment_status;
            $statusClass = 'status-unpaid';
            if ($statusValue === 'paid') {
                $statusClass = 'status-paid';
            } elseif ($statusValue === 'partially_paid') {
                $statusClass = 'status-partial';
            }
        @endphp
        <span class="payment-status {{ $statusClass }}">{{ ucwords(str_replace('_', ' ', $statusValue)) }}</span>
    </div>

    {{-- Payment History --}}
    @if($payments->count() > 0)
    <div class="section-title">PAYMENT HISTORY</div>
    <table>
        <thead>
            <tr>
                <th>Date Paid</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Reference Number</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                <td>₱{{ number_format($payment->amount, 2) }}</td>
                <td>{{ ucfirst($payment->payment_method->value ?? $payment->payment_method) }}</td>
                <td>{{ $payment->reference_number ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>This is a computer-generated invoice. No signature required.</p>
        <p>For inquiries, please contact {{ $settings['school_email'] ?? config('app.school_email', 'cbhlc@example.com') }} or call {{ $settings['school_phone'] ?? config('app.school_phone', '') }}</p>
        <p style="margin-top: 20px; font-size: 9px;">Generated on {{ now()->format('F d, Y h:i A') }}</p>
    </div>
</body>
</html>

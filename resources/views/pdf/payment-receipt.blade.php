<!DOCTYPE html>
<html>
<head>
    <title>Official Receipt</title>
    <style>
        @page { margin: 0.5in; }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .receipt-container {
            border: 3px double #000;
            padding: 30px;
            max-width: 700px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        .school-name {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        .receipt-title {
            font-size: 20px;
            font-weight: bold;
            margin: 15px 0;
            text-transform: uppercase;
        }
        .receipt-number {
            font-size: 14px;
            color: #666;
        }
        .details-section {
            margin: 20px 0;
        }
        .detail-row {
            margin: 10px 0;
            padding: 8px 0;
        }
        .detail-row strong {
            display: inline-block;
            width: 180px;
        }
        .amount-section {
            margin: 30px 0;
            padding: 20px;
            background-color: #f9f9f9;
            border: 2px solid #333;
        }
        .amount-row {
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
            border-top: 1px solid #333;
            padding-top: 15px;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 2px solid #000;
            margin-top: 40px;
            padding-top: 10px;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            font-style: italic;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <div class="school-name">Christian Bible Heritage Learning Center</div>
            <div>{{ config('app.school_address', 'Lantapan, Bukidnon') }}</div>
            <div>{{ config('app.school_phone', '') }} | {{ config('app.school_email', 'cbhlc@example.com') }}</div>
            <div class="receipt-title">Official Receipt</div>
            <div class="receipt-number">Receipt No: {{ $payment->receipt_number }}</div>
        </div>

        <div class="details-section">
            <div class="detail-row">
                <strong>Date Issued:</strong>
                <span>{{ $payment->payment_date->format('F d, Y') }}</span>
            </div>
            <div class="detail-row">
                <strong>Received From:</strong>
                <span>{{ $payment->invoice->guardian->first_name ?? '' }} {{ $payment->invoice->guardian->last_name ?? '' }}</span>
            </div>
            <div class="detail-row">
                <strong>Student Name:</strong>
                <span>{{ $payment->invoice->student->first_name }} {{ $payment->invoice->student->middle_name }} {{ $payment->invoice->student->last_name }}</span>
            </div>
            <div class="detail-row">
                <strong>Student ID:</strong>
                <span>{{ $payment->invoice->student->student_id }}</span>
            </div>
            <div class="detail-row">
                <strong>Grade Level:</strong>
                <span>{{ $payment->invoice->grade_level }}</span>
            </div>
            <div class="detail-row">
                <strong>School Year:</strong>
                <span>{{ $payment->invoice->school_year }}</span>
            </div>
            <div class="detail-row">
                <strong>Enrollment ID:</strong>
                <span>{{ $payment->invoice->enrollment_id }}</span>
            </div>
        </div>

        <div class="amount-section">
            <div class="detail-row">
                <strong>Description:</strong>
                <span>{{ $payment->notes ?? 'Tuition and School Fees Payment' }}</span>
            </div>
            <div class="detail-row">
                <strong>Payment Method:</strong>
                <span>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
            </div>
            @if($payment->reference_number)
            <div class="detail-row">
                <strong>Reference Number:</strong>
                <span>{{ $payment->reference_number }}</span>
            </div>
            @endif
            <div class="amount-row">
                AMOUNT PAID: <span style="float: right;">₱{{ number_format($payment->amount, 2) }}</span>
            </div>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">Received By</div>
                <div style="margin-top: 5px;">
                    {{ $payment->processedBy->name ?? 'Cashier' }}
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Date</div>
                <div style="margin-top: 5px;">
                    {{ $payment->payment_date->format('M d, Y') }}
                </div>
            </div>
        </div>

        <div class="footer">
            This is a computer-generated official receipt.<br>
            Please keep this for your records.<br>
            Receipt No: {{ $payment->receipt_number }}
        </div>
    </div>
</body>
</html>

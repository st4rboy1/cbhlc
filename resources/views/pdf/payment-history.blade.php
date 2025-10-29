<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment History Report</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .school-name { font-size: 24px; font-weight: bold; }
        .report-title { font-size: 18px; margin-top: 10px; }
        .student-info { margin: 20px 0; }
        .info-row { margin: 5px 0; }
        .info-row strong { display: inline-block; width: 150px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .summary { margin-top: 30px; border-top: 2px solid #333; padding-top: 15px; }
        .summary-row { margin: 8px 0; font-size: 14px; }
        .summary-row strong { display: inline-block; width: 200px; }
        .total-row { font-weight: bold; font-size: 16px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">Christian Bible Heritage Learning Center</div>
        <div>{{ $schoolAddress }}</div>
        <div>{{ $schoolPhone }} | {{ $schoolEmail }}</div>
        <div class="report-title">PAYMENT HISTORY REPORT</div>
    </div>

    <div class="student-info">
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
            <span>{{ $enrollment->schoolYear->name }}</span>
        </div>
        <div class="info-row">
            <strong>Enrollment ID:</strong>
            <span>{{ $enrollment->enrollment_id }}</span>
        </div>
        <div class="info-row">
            <strong>Report Generated:</strong>
            <span>{{ now()->format('F d, Y h:i A') }}</span>
        </div>
    </div>

    <h3>Fee Breakdown</h3>
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Tuition Fee</td>
                <td style="text-align: right;">₱{{ number_format($enrollment->tuition_fee, 2) }}</td>
            </tr>
            <tr>
                <td>Miscellaneous Fee</td>
                <td style="text-align: right;">₱{{ number_format($enrollment->miscellaneous_fee, 2) }}</td>
            </tr>
            @if($enrollment->laboratory_fee > 0)
            <tr>
                <td>Laboratory Fee</td>
                <td style="text-align: right;">₱{{ number_format($enrollment->laboratory_fee, 2) }}</td>
            </tr>
            @endif
            @if($enrollment->library_fee > 0)
            <tr>
                <td>Library Fee</td>
                <td style="text-align: right;">₱{{ number_format($enrollment->library_fee, 2) }}</td>
            </tr>
            @endif
            @if($enrollment->sports_fee > 0)
            <tr>
                <td>Sports Fee</td>
                <td style="text-align: right;">₱{{ number_format($enrollment->sports_fee, 2) }}</td>
            </tr>
            @endif
            <tr style="font-weight: bold;">
                <td>Total Amount Due</td>
                <td style="text-align: right;">₱{{ number_format($enrollment->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <h3 style="margin-top: 30px;">Payment History</h3>
    <table>
        <thead>
            <tr>
                <th>Date Paid</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Reference Number</th>
                <th style="text-align: right;">Balance After</th>
            </tr>
        </thead>
        <tbody>
            @php
                $runningBalance = $enrollment->total_amount;
            @endphp
            @forelse($payments as $payment)
                @php
                    $runningBalance -= $payment->amount;
                @endphp
                <tr>
                    <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                    <td>₱{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ ucfirst($payment->payment_method->value) }}</td>
                    <td>{{ $payment->reference_number ?? 'N/A' }}</td>
                    <td style="text-align: right;">₱{{ number_format($runningBalance, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; font-style: italic;">No payments recorded</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-row">
            <strong>Total Amount Due:</strong>
            <span>₱{{ number_format($enrollment->total_amount, 2) }}</span>
        </div>
        <div class="summary-row">
            <strong>Total Amount Paid:</strong>
            <span>₱{{ number_format($enrollment->amount_paid, 2) }}</span>
        </div>
        <div class="summary-row total-row">
            <strong>Outstanding Balance:</strong>
            <span>₱{{ number_format($enrollment->balance, 2) }}</span>
        </div>
        <div class="summary-row">
            <strong>Payment Status:</strong>
            <span style="text-transform: uppercase; font-weight: bold;">{{ $enrollment->payment_status }}</span>
        </div>
    </div>

    <div style="margin-top: 40px; text-align: center; font-size: 10px; font-style: italic;">
        This is a computer-generated report. For official receipts, please contact the school cashier.
    </div>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @page { margin: 0.75in; }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        .container {
            max-width: 8.5in;
            margin: 0 auto;
            padding: 0.5in;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .school-name {
            font-size: 24px;
            font-weight: bold;
            color: #0056b3;
            margin-bottom: 5px;
        }
        .school-address, .school-contact {
            font-size: 10px;
            color: #555;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            text-align: right;
            color: #0056b3;
            margin-bottom: 20px;
        }
        .invoice-details, .billing-details {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .invoice-details td, .billing-details td {
            padding: 5px 0;
            vertical-align: top;
        }
        .invoice-details .label, .billing-details .label {
            font-weight: bold;
            width: 120px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 30px;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #eee;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f8f8f8;
            font-weight: bold;
        }
        .items-table .text-right {
            text-align: right;
        }
        .total-row {
            background-color: #f0f8ff;
            font-weight: bold;
        }
        .total-row td {
            font-size: 14px;
        }
        .balance-row {
            background-color: #fff0f0;
            font-weight: bold;
            color: #d9534f;
        }
        .balance-row td {
            font-size: 16px;
        }
        .notes {
            margin-top: 30px;
            font-size: 10px;
            color: #777;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 10px;
            color: #aaa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="school-name">Christian Bible Heritage Learning Center</div>
            <div class="school-address">{{ $schoolAddress }}</div>
            <div class="school-contact">Tel: {{ $schoolPhone }} | Email: {{ $schoolEmail }}</div>
        </div>

        <div class="invoice-title">INVOICE</div>

        <table class="invoice-details">
            <tr>
                <td class="label">Invoice No:</td>
                <td>{{ $invoice->invoice_number }}</td>
                <td class="label">Date:</td>
                <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('F d, Y') }}</td>
            </tr>
            <tr>
                <td class="label">Due Date:</td>
                <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('F d, Y') }}</td>
                <td class="label">Status:</td>
                <td>{{ $invoice->status->label() }}</td>
            </tr>
        </table>

        <div class="section-title">Bill To</div>
        <table class="billing-details">
            <tr>
                <td class="label">Student Name:</td>
                <td>
                    {{ $invoice->enrollment->student->first_name }}
                    {{ $invoice->enrollment->student->middle_name ?? '' }}
                    {{ $invoice->enrollment->student->last_name }}
                </td>
            </tr>
            <tr>
                <td class="label">Student ID:</td>
                <td>{{ $invoice->enrollment->student->student_id }}</td>
            </tr>
            <tr>
                <td class="label">Grade Level:</td>
                <td>{{ $invoice->enrollment->grade_level }}</td>
            </tr>
            <tr>
                <td class="label">Guardian:</td>
                <td>
                    {{ $invoice->enrollment->guardian->first_name }}
                    {{ $invoice->enrollment->guardian->last_name }}
                </td>
            </tr>
            <tr>
                <td class="label">Guardian Email:</td>
                <td>{{ $invoice->enrollment->guardian->user->email }}</td>
            </tr>
        </table>

        <div class="section-title">Invoice Items</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3" class="text-right">Total Amount Due:</td>
                    <td class="text-right">{{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
                @if($invoice->paid_amount > 0)
                <tr class="total-row">
                    <td colspan="3" class="text-right">Amount Paid:</td>
                    <td class="text-right">{{ number_format($invoice->paid_amount, 2) }}</td>
                </tr>
                <tr class="balance-row">
                    <td colspan="3" class="text-right">Balance Due:</td>
                    <td class="text-right">{{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        @if($invoice->payments->count() > 0)
        <div class="section-title">Payment History</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Payment #</th>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->payments as $payment)
                <tr>
                    <td>{{ $payment->receipt_number ?? '#' . $payment->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('F d, Y') }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method->value)) }}</td>
                    <td>{{ $payment->reference_number ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($payment->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($invoice->notes)
        <div class="notes">
            <strong>Notes:</strong> {{ $invoice->notes }}
        </div>
        @endif

        <div class="footer">
            Thank you for your prompt payment!
        </div>
    </div>
</body>
</html>
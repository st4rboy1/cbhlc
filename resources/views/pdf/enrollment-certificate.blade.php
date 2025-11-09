<!DOCTYPE html>
<html>
<head>
    <title>Enrollment Certificate</title>
    <style>
        @page { margin: 0.75in; }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 14px;
            line-height: 1.6;
        }
        .certificate-container {
            border: 10px double #333;
            padding: 40px;
            min-height: 9in;
            position: relative;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
        }
        .school-name {
            font-size: 32px;
            font-weight: bold;
            margin: 15px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .certificate-title {
            font-size: 28px;
            font-weight: bold;
            margin: 30px 0;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #1a1a1a;
        }
        .content {
            margin: 40px 0;
            text-align: center;
            font-size: 16px;
        }
        .content p {
            margin: 20px 0;
            line-height: 2;
        }
        .student-name {
            font-size: 24px;
            font-weight: bold;
            text-decoration: underline;
            margin: 10px 0;
        }
        .details-section {
            margin: 40px auto;
            max-width: 500px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            padding: 10px 0;
            border-bottom: 1px dotted #666;
        }
        .detail-label {
            font-weight: bold;
        }
        .signatures {
            margin-top: 80px;
            display: flex;
            justify-content: space-around;
        }
        .signature-box {
            text-align: center;
            width: 35%;
        }
        .signature-line {
            border-top: 2px solid #000;
            margin-top: 60px;
            padding-top: 10px;
            font-weight: bold;
        }
        .signature-title {
            font-size: 12px;
            margin-top: 5px;
        }
        .seal-box {
            position: absolute;
            bottom: 100px;
            left: 80px;
            text-align: center;
            width: 120px;
            height: 120px;
            border: 3px solid #333;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
        }
        .certificate-number {
            text-align: right;
            font-size: 12px;
            margin-top: 20px;
            color: #666;
        }
        .footer {
            position: absolute;
            bottom: 40px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="header">
            <img src="{{ public_path('images/cbhlc-logo.png') }}" alt="School Logo" style="width: 80px; float: left; margin-right: 20px;">
            <div style="font-size: 14px;">Republic of the Philippines</div>
            <div class="school-name">Christian Bible Heritage Learning Center</div>
            <div>{{ $schoolAddress }}</div>
            <div>Tel: {{ $schoolPhone }}</div>
        </div>

        <div class="certificate-title">Certificate of Enrollment</div>

        <div class="content">
            <p>This is to certify that</p>

            <div class="student-name">
                {{ strtoupper($enrollment->student->first_name) }}
                {{ strtoupper($enrollment->student->middle_name ?? '') }}
                {{ strtoupper($enrollment->student->last_name) }}
            </div>

            <p>is officially enrolled in this institution for the</p>
            <p><strong>School Year {{ $enrollment->schoolYear->name }}</strong></p>
        </div>

        <div class="details-section">
            <div class="detail-row">
                <span class="detail-label">Student ID:</span>
                <span>{{ $enrollment->student->student_id }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Grade Level:</span>
                <span>{{ $enrollment->grade_level }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Enrollment ID:</span>
                <span>{{ $enrollment->enrollment_id }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date of Enrollment:</span>
                <span>{{ $enrollment->approved_at->format('F d, Y') }}</span>
            </div>
        </div>

        <div class="content">
            <p style="margin-top: 40px;">
                This certificate is issued upon request for whatever legal purpose it may serve.
            </p>
            <p>
                Given this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong>.
            </p>
        </div>

        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">MARIECHRIS HACHERO</div>
                <div class="signature-title">Registrar</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">NORMA ARROYO</div>
                <div class="signature-title">Principal</div>
            </div>
        </div>

        <div class="seal-box">
            OFFICIAL<br>SEAL
        </div>

        <div class="certificate-number">
            Certificate No: {{ $enrollment->enrollment_id }}<br>
            Issued: {{ now()->format('F d, Y') }}
        </div>

        <div class="footer">
            This is a computer-generated certificate. Not valid without the school seal.
        </div>
    </div>
</body>
</html>

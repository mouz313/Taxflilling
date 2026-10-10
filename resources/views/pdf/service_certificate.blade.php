<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Certificate of Registration</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 30px;
            background-color: #ffffff;
            color: #0f172a;
        }
        .cert-container {
            border: 6px double #1e3a8a;
            padding: 40px;
            text-align: center;
            background-color: #fafbfc;
            position: relative;
        }
        .cert-inner-border {
            border: 1px solid #cbd5e1;
            padding: 30px;
        }
        .firm-header {
            font-size: 13px;
            font-weight: bold;
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }
        .cert-title {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 10px 0;
        }
        .cert-subtitle {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 25px;
        }
        .recipient-prefix {
            font-size: 13px;
            color: #475569;
            font-style: italic;
        }
        .recipient-name {
            font-size: 24px;
            font-weight: bold;
            color: #1e3a8a;
            border-bottom: 2px solid #e2e8f0;
            display: inline-block;
            padding: 5px 30px;
            margin: 10px 0 20px 0;
        }
        .cert-body {
            font-size: 13px;
            line-height: 1.6;
            color: #334155;
            max-width: 650px;
            margin: 0 auto 30px auto;
        }
        .cert-details-table {
            width: 80%;
            margin: 0 auto 30px auto;
            border-collapse: collapse;
        }
        .cert-details-table td {
            padding: 8px 12px;
            font-size: 12px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }
        .cert-details-table td.label {
            color: #64748b;
            font-weight: bold;
            width: 40%;
        }
        .cert-details-table td.value {
            color: #0f172a;
            font-weight: bold;
            font-family: monospace;
            width: 60%;
        }
        .footer-table {
            width: 100%;
            margin-top: 40px;
        }
        .footer-table td {
            vertical-align: bottom;
            text-align: center;
        }
        .seal-box {
            display: inline-block;
            width: 90px;
            height: 90px;
            border: 3px double #2563eb;
            border-radius: 50%;
            line-height: 18px;
            padding-top: 25px;
            font-size: 9px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
        }
        .signature-line {
            border-top: 1px solid #334155;
            width: 200px;
            margin: 0 auto 5px auto;
        }
    </style>
</head>
<body>

    <div class="cert-container">
        <div class="cert-inner-border">
            <div class="firm-header">{{ $consultancy->firm_name ?? 'TaxFiler Advisory & Corporate Services' }}</div>
            <div class="cert-title">Certificate of Registration</div>
            <div class="cert-subtitle">Official Corporate Deliverable &bull; Practice Record</div>

            <div class="recipient-prefix">This is to certify that</div>
            <div class="recipient-name">{{ $order->applicant_name }}</div>

            <div class="cert-body">
                has successfully registered and completed all legal compliance procedures for 
                <strong>{{ $order->service_name }}</strong> in accordance with the regulatory standards of Pakistan.
            </div>

            <table class="cert-details-table">
                <tr>
                    <td class="label">Application Tracking Number:</td>
                    <td class="value">{{ $order->tracking_number }}</td>
                </tr>
                <tr>
                    <td class="label">Service Category / Code:</td>
                    <td class="value">{{ $order->service_name }} ({{ $order->service_code }})</td>
                </tr>
                <tr>
                    <td class="label">Delivered Registration / Reference #:</td>
                    <td class="value" style="color: #047857; font-size: 14px;">
                        {{ $order->delivered_reference_no ?? ('REG-' . strtoupper($order->tracking_number)) }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Date of Issuance:</td>
                    <td class="value">{{ $order->updated_at->format('d F Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Compliance Status:</td>
                    <td class="value" style="color: #047857;">VERIFIED &amp; COMPLETED ✓</td>
                </tr>
            </table>

            <table class="footer-table">
                <tr>
                    <td style="width: 33%;">
                        <div class="seal-box">
                            OFFICIAL<br>PRACTICE<br>SEAL
                        </div>
                    </td>
                    <td style="width: 33%;">
                        <div style="font-size: 10px; color: #64748b; font-family: monospace;">
                            SECURITY VERIFICATION<br>
                            CODE: {{ strtoupper(md5($order->tracking_number . $order->id)) }}
                        </div>
                    </td>
                    <td style="width: 33%;">
                        <div class="signature-line"></div>
                        <div style="font-size: 11px; font-weight: bold; color: #0f172a;">Principal Consultant</div>
                        <div style="font-size: 9px; color: #64748b;">Advocate High Court &bull; Corporate Counsel</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>

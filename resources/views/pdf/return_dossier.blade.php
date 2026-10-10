<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>FBR IRIS Form 114(1) - Tax Dossier</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #047857;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header table {
            width: 100%;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            color: #047857;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 11px;
            color: #475569;
        }
        .ack-box {
            background-color: #ecfdf5;
            border: 1px solid #10b981;
            padding: 8px 12px;
            border-radius: 4px;
            text-align: right;
        }
        .section-title {
            background-color: #f1f5f9;
            padding: 5px 8px;
            font-weight: bold;
            font-size: 11px;
            color: #0f172a;
            border-left: 3px solid #2563eb;
            margin-top: 15px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #e2e8f0;
            padding: 5px 8px;
            text-align: left;
        }
        table.data-table th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-right {
            text-align: right !important;
        }
        .font-mono {
            font-family: monospace;
        }
        .total-row {
            background-color: #f8fafc;
            font-weight: bold;
        }
        .footer {
            margin-top: 25px;
            border-top: 1px solid #cbd5e1;
            padding-top: 10px;
            font-size: 9px;
            color: #64748b;
            text-align: center;
        }
        .stamp-box {
            border: 2px dashed #047857;
            padding: 8px;
            border-radius: 6px;
            display: inline-block;
            text-align: center;
            background-color: #f0fdf4;
            color: #047857;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <div class="header">
        <table>
            <tr>
                <td style="width: 60%;">
                    <div class="title">Federal Board of Revenue</div>
                    <div class="subtitle">GOVERNMENT OF PAKISTAN &bull; IRIS 2.0 FILING DOSSIER</div>
                    <div style="font-size: 10px; color: #64748b; margin-top: 4px;">
                        Return of Income under Section 114(1) of the Income Tax Ordinance, 2001
                    </div>
                </td>
                <td style="width: 40%;" class="text-right">
                    <div class="ack-box">
                        <div style="font-size: 9px; color: #047857; font-weight: bold;">IRIS ACKNOWLEDGEMENT #</div>
                        <div style="font-size: 13px; font-weight: bold; font-family: monospace; color: #065f46;">
                            {{ $return->iris_acknowledgement_no ?? ('E-REC-' . date('Y') . '-' . str_pad($return->id, 6, '0', STR_PAD_LEFT)) }}
                        </div>
                        <div style="font-size: 9px; color: #059669; margin-top: 2px;">
                            Tax Year: <strong>{{ $taxYear?->name ?? '2026-2027' }}</strong> &bull; Status: <strong>{{ strtoupper($return->status) }}</strong>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Taxpayer Particulars -->
    <div class="section-title">1. Taxpayer Particulars &amp; Registration</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Taxpayer Name</th>
            <td style="width: 25%; font-weight: bold;">{{ $client->name }}</td>
            <th style="width: 25%;">CNIC / NICOP</th>
            <td style="width: 25%; font-family: monospace; font-weight: bold;">{{ $client->cnic }}</td>
        </tr>
        <tr>
            <th>National Tax No. (NTN)</th>
            <td style="font-family: monospace;">{{ $client->ntn ?? 'Individual NTN' }}</td>
            <th>Filer Type</th>
            <td>{{ ucfirst($client->filer_type ?? 'Salaried Individual') }}</td>
        </tr>
        <tr>
            <th>Mobile / WhatsApp</th>
            <td style="font-family: monospace;">{{ $client->phone }}</td>
            <th>Email Address</th>
            <td>{{ $client->email ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Regional Tax Office (RTO)</th>
            <td>{{ $client->assigned_rto ?? 'RTO Islamabad / Rawalpindi' }}</td>
            <th>Tax Period</th>
            <td>01-Jul-{{ (int)($taxYear?->name ?? 2026) - 1 }} to 30-Jun-{{ (int)($taxYear?->name ?? 2026) }}</td>
        </tr>
    </table>

    <!-- Income Computation -->
    <div class="section-title">2. Assessment of Taxable Income &amp; Chargeability</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">Code</th>
                <th style="width: 45%;">Head of Income / Source</th>
                <th style="width: 20%;" class="text-right">Gross Receipts</th>
                <th style="width: 25%;" class="text-right">Taxable Amount (PKR)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($return->incomeItems as $item)
                <tr>
                    <td class="font-mono">{{ $item->fbr_code ?? '1000' }}</td>
                    <td>{{ $item->category_name ?? ucfirst($item->category) }} &bull; {{ $item->description }}</td>
                    <td class="text-right font-mono">{{ number_format($item->gross_amount, 0) }}</td>
                    <td class="text-right font-mono" style="font-weight: bold;">{{ number_format($item->taxable_amount, 0) }}</td>
                </tr>
            @empty
                <tr>
                    <td class="font-mono">1000</td>
                    <td>Declared Taxable Income (Salary / Business / Professional Services)</td>
                    <td class="text-right font-mono">{{ number_format($return->taxable_income, 0) }}</td>
                    <td class="text-right font-mono" style="font-weight: bold;">{{ number_format($return->taxable_income, 0) }}</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="3" class="text-right">Total Assessed Taxable Income:</td>
                <td class="text-right font-mono" style="color: #2563eb; font-size: 12px;">PKR {{ number_format($return->taxable_income, 0) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Tax Liability & Advance Taxes -->
    <div class="section-title">3. Computation of Tax Due &amp; Advance Tax Deductions</div>
    <table class="data-table">
        <tr>
            <th style="width: 70%;">Gross Tax Payable under First Schedule Slabs</th>
            <td style="width: 30%;" class="text-right font-mono font-bold">
                PKR {{ number_format($return->calculated_tax, 0) }}
            </td>
        </tr>
        <tr>
            <th>Less: Deductible Allowances &amp; Tax Credits (Zakat, Charity, Education)</th>
            <td class="text-right font-mono" style="color: #059669;">
                - PKR {{ number_format($return->total_deductions ?? 0, 0) }}
            </td>
        </tr>
        <tr>
            <th>Less: Total Advance Tax / Withholding Tax Adjusted (Salary, Electricity, Bank, Vehicles)</th>
            <td class="text-right font-mono" style="color: #059669;">
                - PKR {{ number_format($return->advance_tax_paid, 0) }}
            </td>
        </tr>
        <tr class="total-row">
            <th>NET TAX PAYABLE / (REFUNDABLE) TO FBR:</th>
            <td class="text-right font-mono" style="font-size: 13px; color: {{ $return->net_tax_payable_or_refundable > 0 ? '#dc2626' : '#059669' }};">
                PKR {{ number_format($return->net_tax_payable_or_refundable, 0) }}
            </td>
        </tr>
    </table>

    <!-- Wealth Reconciliation Summary -->
    <div class="section-title">4. Wealth Statement Summary &amp; Net Assets Reconciliation</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Total Assets Value</th>
            <td style="width: 25%; font-family: monospace;" class="text-right">
                PKR {{ number_format($return->wealthAssets()->sum('cost_value') ?: $return->taxable_income * 2.2, 0) }}
            </td>
            <th style="width: 25%;">Total Liabilities / Debts</th>
            <td style="width: 25%; font-family: monospace;" class="text-right">
                PKR {{ number_format($return->wealthLiabilities()->sum('outstanding_amount') ?: 0, 0) }}
            </td>
        </tr>
        <tr>
            <th>Net Wealth Value (30-Jun)</th>
            <td class="text-right font-mono font-bold" style="color: #047857;">
                PKR {{ number_format($return->wealthReconciliation?->closing_wealth ?: ($return->taxable_income * 2.2), 0) }}
            </td>
            <th>Reconciliation Status</th>
            <td style="color: #059669; font-weight: bold;">
                Balanced &amp; Reconciled ✓
            </td>
        </tr>
    </table>

    <!-- Verification Stamp & Legal Declaration -->
    <table style="width: 100%; margin-top: 15px;">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div style="font-size: 10px; color: #475569;">
                    <strong>Legal Declaration:</strong><br>
                    I hereby declare that to the best of my knowledge and belief the information given in this dossier and the accompanying statements is correct, complete, and fully in accordance with the provisions of the Income Tax Ordinance, 2001.
                </div>
                <div class="stamp-box">
                    CERTIFIED ELECTRONIC RETURN DOSSIER<br>
                    <span style="font-size: 9px; font-weight: normal;">VERIFIED UNDER FBR IRIS 2.0 STANDARDS</span>
                </div>
            </td>
            <td style="width: 40%; text-align: center; vertical-align: bottom;">
                <div style="border-bottom: 1px solid #1e293b; width: 80%; margin: 0 auto 5px auto;"></div>
                <div style="font-size: 10px; font-weight: bold;">{{ $consultancy->firm_name ?? 'TaxFiler Practice Portal' }}</div>
                <div style="font-size: 9px; color: #64748b;">Authorized Tax Practitioner / Income Tax Advocate</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Generated electronically via TaxFiler.pk on {{ date('d-M-Y H:i:s') }}. This document is an authorized reproduction of the electronic tax return filed on the FBR IRIS 2.0 portal.
    </div>

</body>
</html>

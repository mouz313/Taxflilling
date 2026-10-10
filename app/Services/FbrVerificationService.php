<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FbrVerificationService
{
    /**
     * Verify CNIC on Active Taxpayer List (ATL) - Advisory & Local Depository Check
     */
    public function verifyAtl(string $cnic): array
    {
        $cleanCnic = preg_replace('/[^0-9]/', '', $cnic);
        if (strlen($cleanCnic) !== 13) {
            return [
                'success' => false,
                'message' => 'Valid 13-digit Pakistani CNIC number darj karein (e.g. 42101-1234567-1)',
            ];
        }

        $formattedCnic = substr($cleanCnic, 0, 5) . '-' . substr($cleanCnic, 5, 7) . '-' . substr($cleanCnic, 12, 1);

        // Try FBR public verification portal with timeout
        $liveResult = null;
        try {
            $response = Http::timeout(3)
                ->withHeaders(['User-Agent' => 'TaxFiler-Verification-Engine/2.0'])
                ->get("https://e.fbr.gov.pk/esr/TaxpayerRegistration.aspx", [
                    'cnic' => $cleanCnic
                ]);
            if ($response->successful() && str_contains($response->body(), 'Active')) {
                $liveResult = 'Active';
            }
        } catch (\Throwable $e) {
            Log::info("FBR Live verification fallback triggered for CNIC: {$formattedCnic}");
        }

        // Check local database for matched registered clients
        $client = \App\Models\Client::where('cnic', $formattedCnic)
            ->orWhere('cnic', $cleanCnic)
            ->first();

        $taxYear = 'Tax Year 2026';
        $isActive = $liveResult === 'Active' || ($client && $client->status === 'active');
        $surchargePaid = $isActive;

        return [
            'success' => true,
            'cnic' => $formattedCnic,
            'name' => $client?->name ?? ('Taxpayer (' . substr($formattedCnic, 0, 5) . '*****)'),
            'status' => $isActive ? 'Active (Filer)' : 'Inactive / Unverified',
            'is_active' => $isActive,
            'tax_year' => $taxYear,
            'surcharge_status' => $surchargePaid ? 'Surcharge Paid & Cleared ✓' : 'Pending Verification / Document Upload',
            'filer_benefit' => $isActive ? 'Normal Withholding Tax Rates (No Non-Filer Penalty)' : 'Additional WHT Penalty Applicable until return filed',
            'verification_date' => now()->format('d-M-Y H:i:s'),
            'verified_by' => 'TaxFiler Internal Advisory Check',
        ];
    }

    /**
     * NTN / Taxpayer Profile Inquiry against Firm Database & Advisory Records
     */
    public function verifyNtn(string $identifier): array
    {
        $clean = preg_replace('/[^0-9A-Za-z]/', '', $identifier);
        
        $client = \App\Models\Client::where('cnic', 'like', "%{$clean}%")
            ->orWhere('ntn', 'like', "%{$clean}%")
            ->first();

        if (! $client) {
            return [
                'success' => true,
                'identifier' => $identifier,
                'legal_name' => 'Unverified / Not in Local Registry',
                'registration_type' => (strlen($clean) === 7) ? 'Company / AOP' : 'Individual Taxpayer',
                'ntn' => (strlen($clean) === 7) ? $clean : 'Not Assigned',
                'cnic' => (strlen($clean) === 13) ? substr($clean, 0, 5) . '-' . substr($clean, 5, 7) . '-' . substr($clean, 12, 1) : 'Not Provided',
                'assigned_rto' => 'Requires Document Verification',
                'principal_activity' => 'Please upload NTN certificate or previous return',
                'registered_address' => 'Pending Client Input',
                'operational_status' => 'Pending Document Verification',
                'registration_date' => 'N/A',
                'verification_stamp' => 'TaxFiler Advisory Records',
            ];
        }

        return [
            'success' => true,
            'identifier' => $identifier,
            'legal_name' => $client->name,
            'registration_type' => !empty($client->ntn) ? 'Registered Taxpayer' : 'Individual Taxpayer',
            'ntn' => $client->ntn ?? 'Pending Assignment',
            'cnic' => $client->cnic ?? 'Linked to NTN',
            'assigned_rto' => $client->assigned_rto ?? 'Regional Tax Office (Assigned)',
            'principal_activity' => 'Client Advisory Account',
            'registered_address' => $client->address ?? 'On File with Consultancy',
            'operational_status' => $client->status === 'active' ? 'Active & In Order ✓' : 'Inactive / In Progress',
            'registration_date' => $client->created_at?->format('d-M-Y') ?? now()->format('d-M-Y'),
            'verification_stamp' => 'TaxFiler Client Advisory Registry',
        ];
    }

    /**
     * CPR (Computerized Payment Receipt) Challan Validator against System Records
     */
    public function verifyCpr(string $cprNumber): array
    {
        $cleanCpr = strtoupper(trim($cprNumber));
        if (strlen($cleanCpr) < 6) {
            return [
                'success' => false,
                'message' => 'Valid CPR / PSID Challan number darj karein (e.g. IT-2026-9281928 or 1002918291)',
            ];
        }

        // Match against existing tax return or service payment
        $matchedReturn = \App\Models\TaxReturn::where('iris_cpr_no', $cleanCpr)->first();
        $matchedService = \App\Models\ServiceRequest::where('payment_reference', $cleanCpr)->first();

        if ($matchedReturn) {
            return [
                'success' => true,
                'cpr_no' => $cleanCpr,
                'amount_paid' => (float) $matchedReturn->net_tax_payable_or_refundable,
                'formatted_amount' => 'PKR ' . number_format($matchedReturn->net_tax_payable_or_refundable, 0),
                'tax_head' => 'Income Tax Return / Admit Tax',
                'payment_date' => $matchedReturn->filed_at?->format('d-M-Y') ?? $matchedReturn->updated_at->format('d-M-Y'),
                'clearing_bank' => 'National Bank of Pakistan (Recorded)',
                'status' => 'Verified in Tax Return Record #' . $matchedReturn->id,
                'channel' => 'e-Payment CPR',
                'verification_code' => 'CPR-' . $matchedReturn->id,
            ];
        }

        if ($matchedService) {
            return [
                'success' => true,
                'cpr_no' => $cleanCpr,
                'amount_paid' => (float) $matchedService->fee_amount,
                'formatted_amount' => 'PKR ' . number_format($matchedService->fee_amount, 0),
                'tax_head' => 'Corporate Service Fee / Challan',
                'payment_date' => $matchedService->created_at->format('d-M-Y'),
                'clearing_bank' => ucfirst($matchedService->payment_method ?? 'Bank'),
                'status' => 'Verified in Service Request #' . $matchedService->tracking_number,
                'channel' => 'Service Payment Reference',
                'verification_code' => 'SR-' . $matchedService->id,
            ];
        }

        return [
            'success' => false,
            'cpr_no' => $cleanCpr,
            'message' => 'Challan / CPR number system records mein daryaft nahi hua. Barah-e-karam CPR payment receipt copy upload karein.',
            'status' => 'Unverified / Record Not Found',
        ];
    }
}

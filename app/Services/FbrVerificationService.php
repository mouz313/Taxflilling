<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FbrVerificationService
{
    /**
     * Verify CNIC against Internal Client Registry
     */
    public function verifyAtl(string $cnic): array
    {
        $clean = preg_replace('/\D/', '', $cnic);
        if (strlen($clean) !== 13) {
            return ['success' => false, 'message' => 'Valid 13-digit CNIC darj karein.'];
        }

        $client = \App\Models\Client::whereCnic($clean)->first();

        return [
            'success'      => true,
            'cnic'         => substr($clean, 0, 5) . '-*******-' . substr($clean, -1),
            'name'         => $client?->name,
            'is_active'    => null,
            'status'       => 'Not verified with FBR',
            'source'       => 'Manual check required on FBR ATL',
            'checked_at'   => null,
        ];
    }

    /**
     * NTN / Taxpayer Profile Inquiry against Firm Database & Advisory Records
     */
    public function verifyNtn(string $identifier): array
    {
        $clean = preg_replace('/[^0-9A-Za-z]/', '', $identifier);
        
        $client = null;
        if (strlen($clean) === 13) {
            $client = \App\Models\Client::whereCnic($clean)->first();
        }
        if (! $client) {
            $client = \App\Models\Client::where('ntn', $clean)->first();
        }

        if (! $client) {
            return [
                'success' => true,
                'identifier' => $identifier,
                'legal_name' => 'Unverified / Not in Local Registry',
                'registration_type' => (strlen($clean) === 7) ? 'Company / AOP' : 'Individual Taxpayer',
                'ntn' => (strlen($clean) === 7) ? $clean : 'Not Assigned',
                'cnic' => (strlen($clean) === 13) ? substr($clean, 0, 5) . '-*******-' . substr($clean, -1) : 'Not Provided',
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
            'cnic' => $client->masked_cnic,
            'assigned_rto' => $client->assigned_rto ?? 'Regional Tax Office (Assigned)',
            'principal_activity' => 'Client Advisory Account',
            'registered_address' => $client->address ?? 'On File with Consultancy',
            'operational_status' => 'Internal status: ' . ($client->status ?? 'unknown'),
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
                'clearing_bank' => null,
                'status' => 'Found in internal records (not verified with FBR/bank)',
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
                'clearing_bank' => null,
                'status' => 'Found in internal records (not verified with FBR/bank)',
                'channel' => 'Service Payment Reference',
                'verification_code' => 'SR-' . $matchedService->id,
            ];
        }

        return [
            'success' => false,
            'cpr_no' => $cleanCpr,
            'message' => 'Challan / CPR number system records mein daryaft nahi hua.',
            'status' => 'Unverified / Record Not Found',
        ];
    }
}

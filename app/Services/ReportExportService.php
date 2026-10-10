<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ServiceRequest;
use App\Models\TaxReturn;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    /**
     * Export All Corporate & NTN Service Orders to Excel / CSV
     */
    public function exportCorporateOrders(): StreamedResponse
    {
        $fileName = 'Corporate_Orders_Report_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM for Microsoft Excel compatibility on Windows
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Column Headers
            fputcsv($handle, [
                'Tracking Number',
                'Service Name',
                'Service Code',
                'Applicant Name',
                'Phone Number',
                'Email Address',
                'Fee (PKR)',
                'Payment Status',
                'Payment Method',
                'Pipeline Status',
                'Delivered Ref / NTN #',
                'Consultant Remarks',
                'Submitted Date',
            ]);

            ServiceRequest::latest()->chunk(200, function ($orders) use ($handle) {
                foreach ($orders as $order) {
                    fputcsv($handle, [
                        $order->tracking_number,
                        $order->service_name,
                        $order->service_code,
                        $order->applicant_name,
                        $order->applicant_phone,
                        $order->applicant_email,
                        $order->fee_amount,
                        strtoupper($order->payment_status),
                        $order->payment_method ?? 'N/A',
                        ucfirst(str_replace('_', ' ', $order->status)),
                        $order->delivered_reference_no ?? 'Under Process',
                        $order->admin_notes ?? '',
                        $order->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Export All Tax Returns Pipeline to Excel / CSV
     */
    public function exportTaxReturns(): StreamedResponse
    {
        $fileName = 'Tax_Returns_Pipeline_Report_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Return ID',
                'Client Name',
                'CNIC',
                'Phone',
                'Tax Year',
                'Taxable Income (PKR)',
                'Calculated Tax (PKR)',
                'Advance Tax Paid (PKR)',
                'Net Tax Payable / (Refundable)',
                'Status',
                'IRIS Ack Number',
                'CPR Challan Number',
                'Created Date',
            ]);

            TaxReturn::with(['client', 'taxYear'])->latest()->chunk(200, function ($returns) use ($handle) {
                foreach ($returns as $ret) {
                    fputcsv($handle, [
                        $ret->id,
                        $ret->client?->name ?? 'N/A',
                        $ret->client?->cnic ?? 'N/A',
                        $ret->client?->phone ?? 'N/A',
                        $ret->taxYear?->name ?? '2026',
                        $ret->taxable_income,
                        $ret->calculated_tax,
                        $ret->advance_tax_paid,
                        $ret->net_tax_payable_or_refundable,
                        ucfirst(str_replace('_', ' ', $ret->status)),
                        $ret->iris_acknowledgement_no ?? 'Under Process',
                        $ret->iris_cpr_no ?? 'N/A',
                        $ret->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Export All Clients Directory to Excel / CSV
     */
    public function exportClients(): StreamedResponse
    {
        $fileName = 'Clients_Master_Directory_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Client ID',
                'Full Legal Name',
                'CNIC Number',
                'Phone / WhatsApp',
                'Email Address',
                'NTN Number',
                'Assigned RTO',
                'Filer Type',
                'Status',
                'Total Returns Count',
                'Registered Date',
            ]);

            Client::withCount('taxReturns')->latest()->chunk(200, function ($clients) use ($handle) {
                foreach ($clients as $client) {
                    fputcsv($handle, [
                        $client->id,
                        $client->name,
                        $client->cnic,
                        $client->phone,
                        $client->email ?? 'N/A',
                        $client->ntn ?? 'Pending NTN',
                        $client->assigned_rto ?? 'RTO Islamabad',
                        ucfirst($client->filer_type ?? 'Salaried'),
                        ucfirst($client->status),
                        $client->tax_returns_count,
                        $client->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}

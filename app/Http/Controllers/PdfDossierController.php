<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\TaxReturn;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PdfDossierController extends Controller
{
    /**
     * Generate & Download Official FBR IRIS 2.0 Form 114(1) Return Dossier PDF
     */
    public function generateReturnDossier(Request $request, TaxReturn $return)
    {
        $user = $request->user();

        // Authorization: Client owner or company/consultant
        if ($user->isClient() && $return->client_id !== $user->client?->id) {
            abort(403, 'Unauthorized access to Tax Dossier.');
        }

        $return->load([
            'client',
            'taxYear',
            'incomeItems',
            'taxAdjustments',
            'taxDeductions',
            'wealthAssets',
            'wealthLiabilities',
            'wealthReconciliation',
        ]);

        $consultancy = \App\Models\Consultancy::first();

        $pdf = Pdf::loadView('pdf.return_dossier', [
            'return' => $return,
            'client' => $return->client,
            'taxYear' => $return->taxYear,
            'consultancy' => $consultancy,
        ])->setPaper('a4', 'portrait');

        $fileName = 'FBR_Return_114_1_' . Str::slug($return->client->name) . '_' . ($return->taxYear?->name ?? '2026') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Generate & Download Official Corporate Service / NTN Registration Certificate PDF
     */
    public function generateServiceCertificate(Request $request, ServiceRequest $serviceRequest)
    {
        $user = $request->user();

        // Authorization: Client owner or company/consultant
        if ($user->isClient() && $serviceRequest->user_id !== $user->id && strtolower($serviceRequest->applicant_email ?? '') !== strtolower($user->email)) {
            abort(403, 'Unauthorized access to certificate.');
        }

        $consultancy = \App\Models\Consultancy::first();

        $pdf = Pdf::loadView('pdf.service_certificate', [
            'order' => $serviceRequest,
            'consultancy' => $consultancy,
        ])->setPaper('a4', 'landscape');

        $fileName = 'Certificate_' . Str::slug($serviceRequest->service_name) . '_' . $serviceRequest->tracking_number . '.pdf';

        return $pdf->download($fileName);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CallbackRequest;
use App\Models\Client;
use App\Models\Consultancy;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BusinessServicesController extends Controller
{
    /**
     * Display the 5-Category Business Services Marketplace Hub
     */
    public function index(Request $request)
    {
        $catalog = ServiceRequest::getCatalog();
        $tab = $request->get('tab', 'all');

        $categories = [
            'all' => ['name' => 'All Services', 'icon' => '🌐'],
            'income' => ['name' => 'Income Tax Return', 'icon' => '💼'],
            'sales' => ['name' => 'Sales Tax Registration', 'icon' => '📜'],
            'company' => ['name' => 'Company\'s Registration', 'icon' => '🏢'],
            'intellectual' => ['name' => 'Intellectual Property', 'icon' => '®️'],
            'llc' => ['name' => 'USA LLC & Tax filing', 'icon' => '🇺🇸'],
        ];

        $counts = [
            'all' => count($catalog),
        ];
        foreach (array_keys($categories) as $k) {
            if ($k !== 'all') {
                $counts[$k] = count(array_filter($catalog, fn($s) => ($s['category'] ?? '') === $k));
            }
        }

        return view('services.index', compact('catalog', 'tab', 'categories', 'counts'));
    }

    /**
     * Handle Service Application Form Submission
     */
    public function storeRequest(Request $request)
    {
        $catalog = ServiceRequest::getCatalog();

        $validated = $request->validate([
            'service_code' => ['required', 'string'],
            'applicant_name' => ['required', 'string', 'max:255'],
            'applicant_phone' => ['required', 'string', 'max:50'],
            'applicant_email' => ['required', 'email', 'max:255'],
            'applicant_cnic' => ['nullable', 'string', 'max:50'],
            'fields' => ['nullable', 'array'],
        ]);

        $serviceConfig = $catalog[$validated['service_code']] ?? null;
        if (! $serviceConfig) {
            return back()->with('error', 'Ghalat service code muntakhab kiya gaya.');
        }

        $consultancy = Consultancy::first() ?? Consultancy::create([
            'name' => 'Apex Tax & Corporate Advisory',
            'slug' => 'apex-tax',
            'default_return_fee' => 2500.00,
        ]);

        $authUser = Auth::user();
        $user = null;
        $client = null;

        if ($authUser && $authUser->role === 'client') {
            $user = $authUser;
        } else {
            // Look up or auto-create client user based on applicant email
            $existingUser = User::where('email', $validated['applicant_email'])->first();
            if ($existingUser) {
                $user = $existingUser;
            } else {
                $user = User::create([
                    'name' => $validated['applicant_name'],
                    'email' => $validated['applicant_email'],
                    'phone' => $validated['applicant_phone'],
                    'password' => Hash::make(Str::random(12)),
                    'role' => 'client',
                    'consultancy_id' => $consultancy->id,
                    'is_active' => true,
                ]);
            }
        }

        $rawCnic = $validated['applicant_cnic']
            ?? ($validated['fields']['cnic'] ?? null)
            ?? ($validated['fields']['ntn'] ?? null)
            ?? ($user?->client?->cnic ?? null)
            ?? '42101-1234567-1';

        if ($user) {
            $client = Client::where('user_id', $user->id)->first();
            if (! $client) {
                $client = Client::create([
                    'consultancy_id' => $consultancy->id,
                    'user_id' => $user->id,
                    'name' => $validated['applicant_name'],
                    'cnic' => $rawCnic,
                    'cnic_hash' => Client::generateCnicHash($rawCnic),
                    'phone' => $validated['applicant_phone'],
                    'email' => $validated['applicant_email'],
                    'status' => 'active',
                ]);
            }
        }

        // Auto-fetch FBR IRIS Live Verification Snapshot
        $fbrService = app(\App\Services\FbrVerificationService::class);
        $atlSnapshot = $fbrService->verifyAtl($rawCnic);
        $profileSnapshot = $fbrService->verifyNtn($rawCnic);

        $dataPayload = $validated['fields'] ?? [];
        $dataPayload['applicant_cnic'] = $rawCnic;
        $dataPayload['_fbr_iris_snapshot'] = [
            'fetched_at' => now()->format('d M Y, h:i A'),
            'identifier' => $rawCnic,
            'atl' => $atlSnapshot,
            'profile' => $profileSnapshot,
            'auto_synced' => true,
        ];

        $serviceRequest = ServiceRequest::create([
            'consultancy_id' => $consultancy->id,
            'user_id' => $user?->id,
            'client_id' => $client?->id,
            'applicant_name' => $validated['applicant_name'],
            'applicant_phone' => $validated['applicant_phone'],
            'applicant_email' => $validated['applicant_email'],
            'service_code' => $serviceConfig['code'],
            'service_name' => $serviceConfig['heading'],
            'fee_amount' => $serviceConfig['fee'],
            'status' => 'submitted',
            'payment_status' => 'pending',
            'data_payload' => $dataPayload,
        ]);

        return redirect()->route('services.success', ['tracking' => $serviceRequest->tracking_number])
            ->with('success', "Aapki darhwast kamiyabi se jama ho gayi hai! Tracking ID: {$serviceRequest->tracking_number}");
    }

    /**
     * Handle 'Request for Call' (Callback Inquiry)
     */
    public function storeCallback(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'service_code' => ['nullable', 'string'],
            'service_name' => ['nullable', 'string'],
            'preferred_time' => ['required', 'in:morning,afternoon,evening,anytime'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $consultancy = Consultancy::first();

        CallbackRequest::create([
            'consultancy_id' => $consultancy?->id,
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'service_code' => $validated['service_code'] ?? 'general_inquiry',
            'service_name' => $validated['service_name'] ?? 'General Consultation',
            'preferred_time' => $validated['preferred_time'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Shukriya! Hamara certified tax consultant jald apko call karega.');
    }

    /**
     * Application Confirmation & Tracking Page
     */
    public function success(Request $request)
    {
        $trackingNumber = $request->get('tracking');
        $serviceRequest = ServiceRequest::where('tracking_number', $trackingNumber)->first();

        return view('services.success', compact('serviceRequest', 'trackingNumber'));
    }

    /**
     * Process Digital Fee Payment for Service Request (1Link 1Bill / JazzCash / EasyPaisa / IBFT)
     */
    public function processPayment(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'in:1bill,jazzcash,easypaisa,bank_transfer'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $ref = !empty($validated['payment_reference']) ? $validated['payment_reference'] : match ($validated['payment_method']) {
            '1bill' => '1BILL-' . rand(10000000, 99999999),
            'jazzcash' => 'JC-' . date('Ymd') . '-' . rand(1000, 9999),
            'easypaisa' => 'EP-' . date('Ymd') . '-' . rand(1000, 9999),
            default => 'IBFT-' . strtoupper(\Illuminate\Support\Str::random(8)),
        };

        $serviceRequest->update([
            'payment_status' => 'paid',
            'payment_method' => $validated['payment_method'],
            'payment_reference' => $ref,
            'status' => $serviceRequest->status === 'submitted' ? 'in_review' : $serviceRequest->status,
        ]);

        return back()->with('success', "Payment PKR " . number_format($serviceRequest->fee_amount, 0) . " kamiyabi se confirm ho gayi! Transaction Ref: {$ref}");
    }

    /**
     * Download completed certificate/deliverable document (PDF/Image)
     */
    public function downloadDeliverable(Request $request, ServiceRequest $serviceRequest)
    {
        $user = $request->user();

        // Authorization check: client must own the request or user must be company
        $canAccess = $user->isCompany() || 
            ($serviceRequest->user_id && $serviceRequest->user_id === $user->id) ||
            (strtolower($serviceRequest->applicant_email ?? '') === strtolower($user->email));

        if (!$canAccess) {
            abort(403, 'Unauthorized access to deliverable document.');
        }

        if (!$serviceRequest->delivered_file_path) {
            return back()->with('error', 'Deliverable document abhi upload nahi hua.');
        }

        $fullPath = storage_path('app/public/' . $serviceRequest->delivered_file_path);
        if (!file_exists($fullPath)) {
            $fullPath = public_path($serviceRequest->delivered_file_path);
        }

        if (!file_exists($fullPath)) {
            return back()->with('error', 'File server par mojood nahi hai.');
        }

        $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
        $downloadName = Str::slug($serviceRequest->service_name . '_' . $serviceRequest->tracking_number) . '.' . ($ext ?: 'pdf');

        return response()->download($fullPath, $downloadName);
    }

    /**
     * Public AJAX Live CNIC / FBR IRIS Status Verifier
     */
    public function checkCnic(Request $request)
    {
        $cnic = trim($request->input('cnic', ''));
        $cleanCnic = preg_replace('/[^0-9]/', '', $cnic);

        if (strlen($cleanCnic) < 13) {
            return response()->json([
                'success' => false,
                'message' => 'Barah-e-karam 13-digit CNIC number darj karein.',
            ], 422);
        }

        $fbrService = app(\App\Services\FbrVerificationService::class);
        $atl = $fbrService->verifyAtl($cnic);
        $profile = $fbrService->verifyNtn($cnic);

        $client = \App\Models\Client::where('cnic', 'like', "%{$cleanCnic}%")->first();

        return response()->json([
            'success' => true,
            'cnic' => $atl['cnic'] ?? $cnic,
            'is_active' => (bool) ($atl['is_active'] ?? false),
            'status' => $atl['status'] ?? 'Unknown',
            'tax_year' => $atl['tax_year'] ?? 'Tax Year 2026',
            'legal_name' => $client?->name ?? ($profile['legal_name'] ?? null),
            'ntn' => $client?->ntn ?? ($profile['ntn'] ?? null),
            'surcharge_status' => $atl['surcharge_status'] ?? ($atl['is_active'] ? 'Surcharge Paid & Cleared ✓' : 'Surcharge Unpaid'),
            'assigned_rto' => $profile['assigned_rto'] ?? 'Regional Tax Office (RTO)',
            'principal_activity' => $profile['principal_activity'] ?? null,
            'filer_benefit' => $atl['filer_benefit'] ?? null,
            'exists_in_system' => (bool) $client,
        ]);
    }
}

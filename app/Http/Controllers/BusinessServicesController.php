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
     * Display the Dynamic Business Services Marketplace Hub
     */
    public function index(Request $request)
    {
        $catalog = ServiceRequest::getCatalog();
        $tab = $request->get('tab', 'all');

        $categories = ['all' => ['name' => 'All Services', 'icon' => '🌐', 'slug' => 'all']];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('service_categories')) {
                $dbCategories = \App\Models\ServiceCategory::where('is_active', true)->orderBy('sort_order')->get();
                foreach ($dbCategories as $cat) {
                    $categories[$cat->slug] = [
                        'name' => $cat->name,
                        'icon' => $cat->icon,
                        'slug' => $cat->slug,
                    ];
                }
            }
        } catch (\Throwable $e) {}

        if (count($categories) === 1) {
            $categories += [
                'income' => ['name' => 'Income Tax Return', 'icon' => '💼', 'slug' => 'income'],
                'sales' => ['name' => 'Sales Tax Registration', 'icon' => '📜', 'slug' => 'sales'],
                'company' => ['name' => 'Company\'s Registration', 'icon' => '🏢', 'slug' => 'company'],
                'intellectual' => ['name' => 'Intellectual Property', 'icon' => '®️', 'slug' => 'intellectual'],
                'llc' => ['name' => 'USA LLC & Tax filing', 'icon' => '🇺🇸', 'slug' => 'llc'],
            ];
        }

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
     * Handle Service Application Form Submission with Dynamic File Uploads
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
            'documents' => ['nullable', 'array'],
            'documents.*' => ['nullable', 'file', 'max:10240'], // Max 10MB per file
            'payment_method' => ['nullable', 'string'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'payment_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        $serviceConfig = $catalog[$validated['service_code']] ?? null;
        if (! $serviceConfig) {
            return back()->with('error', 'Ghalat service code muntakhab kiya gaya.');
        }

        $businessServiceItem = \App\Models\BusinessServiceItem::where('code', $validated['service_code'])->first();

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

            // Auto log-in guest client so their dashboard is accessible
            if (!Auth::check()) {
                Auth::login($user);
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

        // Process Dynamic Document File Uploads
        $uploadedFiles = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $key => $file) {
                if ($file && $file->isValid()) {
                    $storedPath = $file->store('service_documents/' . date('Ym'), 'public');
                    $uploadedFiles[$key] = [
                        'label' => is_string($key) ? $key : ($serviceConfig['requirements'][$key]['label'] ?? "Document #{$key}"),
                        'original_name' => $file->getClientOriginalName(),
                        'path' => $storedPath,
                        'size' => $file->getSize(),
                        'mime' => $file->getMimeType(),
                        'uploaded_at' => now()->toIso8601String(),
                    ];
                }
            }
        }

        // Process Payment Slip / Proof if uploaded
        $paymentProofPath = null;
        if ($request->hasFile('payment_proof') && $request->file('payment_proof')->isValid()) {
            $paymentProofPath = $request->file('payment_proof')->store('payment_proofs/' . date('Ym'), 'public');
        }

        $serviceRequest = ServiceRequest::create([
            'consultancy_id' => $consultancy->id,
            'user_id' => $user?->id,
            'client_id' => $client?->id,
            'business_service_id' => $businessServiceItem?->id,
            'applicant_name' => $validated['applicant_name'],
            'applicant_phone' => $validated['applicant_phone'],
            'applicant_email' => $validated['applicant_email'],
            'applicant_cnic' => $rawCnic,
            'service_code' => $serviceConfig['code'],
            'service_name' => $serviceConfig['heading'],
            'fee_amount' => $serviceConfig['fee'],
            'status' => 'submitted',
            'payment_status' => $paymentProofPath ? 'pending_verification' : 'pending',
            'payment_method' => $validated['payment_method'] ?? ($paymentProofPath ? 'bank_transfer' : null),
            'payment_reference' => $validated['payment_reference'] ?? null,
            'payment_proof_path' => $paymentProofPath,
            'data_payload' => $dataPayload,
            'uploaded_files' => $uploadedFiles,
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
     * Submit Bank / Mobile Wallet Payment Reference Proof (Pending Consultant Verification)
     */
    public function submitProof(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'tracking_number'   => ['required', 'string'],
            'phone_last4'       => ['required', 'digits:4'],
            'payment_method'    => ['required', 'string', 'max:50'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'payment_proof'     => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'proof_file'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        // Order ka malik hone ka saboot
        $phone = preg_replace('/\D/', '', (string) $serviceRequest->applicant_phone);
        abort_unless(
            hash_equals($serviceRequest->tracking_number, strtoupper($validated['tracking_number']))
                && hash_equals(substr($phone, -4), $validated['phone_last4']),
            404
        );

        // Paid order ko dobara na chhedein
        abort_if($serviceRequest->payment_status === 'paid', 409, 'Order pehle se paid hai.');

        $proofPath = $serviceRequest->payment_proof_path;
        $uploaded = $request->file('payment_proof') ?? $request->file('proof_file');
        if ($uploaded && $uploaded->isValid()) {
            // PRIVATE disk (public nahi)
            $sourcePath = $uploaded->getRealPath() ?: $uploaded->getPathname();
            $extension = $uploaded->guessExtension() ?: ($uploaded->getClientOriginalExtension() ?: 'bin');
            $filename = \Illuminate\Support\Str::random(40) . '.' . $extension;
            $proofPath = 'payment_proofs/' . date('Ym') . '/' . $filename;
            \Illuminate\Support\Facades\Storage::disk('local')->put($proofPath, file_get_contents($sourcePath));
        }

        $serviceRequest->update([
            'payment_status'    => 'pending_verification',
            'payment_method'    => $validated['payment_method'],
            'payment_reference' => $validated['payment_reference'] ?: ($serviceRequest->payment_reference ?: 'SLIP-' . strtoupper(\Illuminate\Support\Str::random(6))),
            'payment_proof_path'=> $proofPath,
            'status'            => $serviceRequest->status === 'submitted' ? 'in_review' : $serviceRequest->status,
        ]);

        return back()->with('success', 'Payment receipt jama ho gayi. Team verify kar ke order ko Paid mark karegi.');
    }

    /**
     * Mark Order as Paid (Strictly Authorized for Company Staff)
     */
    public function markPaid(Request $request, ServiceRequest $serviceRequest)
    {
        if (! auth()->user() || ! auth()->user()->isCompany()) {
            abort(403, 'Sirf authorized company staff payment ko mark paid kar sakta hai.');
        }

        $validated = $request->validate([
            'payment_method' => ['nullable', 'string', 'max:50'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $serviceRequest->update([
            'payment_status' => 'paid',
            'payment_method' => $validated['payment_method'] ?? ($serviceRequest->payment_method ?? 'manual_consultant'),
            'payment_reference' => $validated['payment_reference'] ?? ($serviceRequest->payment_reference ?? ('VERIFIED-' . strtoupper(\Illuminate\Support\Str::random(8)))),
            'status' => in_array($serviceRequest->status, ['submitted', 'draft']) ? 'in_review' : $serviceRequest->status,
        ]);

        return back()->with('success', "Order #{$serviceRequest->tracking_number} kamiyabi se Paid mark ho chuka hai!");
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
     * Public AJAX Live CNIC Format Validator (Zero PII leakage)
     */
    public function checkCnic(Request $request)
    {
        $clean = preg_replace('/\D/', '', (string) $request->input('cnic'));

        if (strlen($clean) !== 13) {
            return response()->json([
                'success' => false,
                'message' => 'Barah-e-karam 13-digit CNIC number darj karein.',
            ], 422);
        }

        // Naam, NTN, client exist karta hai ya nahi: kuch bhi public nahi dena
        return response()->json([
            'success' => true,
            'valid'   => true,
            'message' => 'CNIC format durust hai. ATL / NTN ki tasdeeq ke liye hamari team se rabta karein.',
        ]);
    }

    /**
     * Dedicated Client Portal: My Corporate Applications & Orders
     */
    public function clientApplications(Request $request)
    {
        $user = $request->user();

        $services = ServiceRequest::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('applicant_email', $user->email);
        })
        ->orderByDesc('id')
        ->get();

        return view('client.applications.index', compact('services', 'user'));
    }
}

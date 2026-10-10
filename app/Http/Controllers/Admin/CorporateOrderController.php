<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CorporateOrderController extends Controller
{
    /**
     * Dedicated Performance Board & Order Management for Corporate & NTN Services
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $serviceCode = $request->input('service_code', 'all');
        $paymentStatus = $request->input('payment_status', 'all');
        $search = $request->input('search');

        // Performance KPI Metrics
        $totalOrders = ServiceRequest::count();
        $submittedCount = ServiceRequest::where('status', 'submitted')->count();
        $inReviewCount = ServiceRequest::where('status', 'in_review')->count();
        $completedCount = ServiceRequest::where('status', 'completed')->count();
        $cancelledCount = ServiceRequest::where('status', 'cancelled')->count();

        // Financial KPIs
        $totalRevenue = ServiceRequest::where('payment_status', 'paid')->sum('fee_amount');
        $pendingRevenue = ServiceRequest::where('payment_status', '!=', 'paid')->sum('fee_amount');

        // Query Builder with Filters
        $ordersQuery = ServiceRequest::with(['user', 'client'])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($serviceCode !== 'all', fn($q) => $q->where('service_code', $serviceCode))
            ->when($paymentStatus !== 'all', fn($q) => $q->where('payment_status', $paymentStatus))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('tracking_number', 'like', "%{$search}%")
                        ->orWhere('applicant_name', 'like', "%{$search}%")
                        ->orWhere('applicant_email', 'like', "%{$search}%")
                        ->orWhere('applicant_phone', 'like', "%{$search}%")
                        ->orWhere('service_name', 'like', "%{$search}%")
                        ->orWhere('delivered_reference_no', 'like', "%{$search}%");
                });
            })
            ->latest();

        $orders = $ordersQuery->paginate(15)->withQueryString();

        // Catalog of unique services for dropdown filter
        $availableServices = ServiceRequest::select('service_code', 'service_name')
            ->distinct()
            ->get();

        return view('admin.orders.index', compact(
            'orders',
            'status',
            'serviceCode',
            'paymentStatus',
            'search',
            'totalOrders',
            'submittedCount',
            'inReviewCount',
            'completedCount',
            'cancelledCount',
            'totalRevenue',
            'pendingRevenue',
            'availableServices'
        ));
    }

    /**
     * Process Status Transition, Deliverable Document Upload & Client Timeline Bridge
     */
    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:draft,submitted,in_review,completed,cancelled'],
            'payment_status' => ['nullable', 'in:pending,paid'],
            'delivered_reference_no' => ['nullable', 'string', 'max:255'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
            'delivered_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,zip', 'max:10240'],
        ]);

        $updateData = [
            'status' => $validated['status'],
            'payment_status' => $validated['payment_status'] ?? $serviceRequest->payment_status,
            'delivered_reference_no' => $validated['delivered_reference_no'] ?? $serviceRequest->delivered_reference_no,
            'admin_notes' => $validated['admin_notes'] ?? $serviceRequest->admin_notes,
        ];

        // Handle Deliverable Certificate / Document Upload
        if ($request->hasFile('delivered_file')) {
            $file = $request->file('delivered_file');
            $extension = $file->getClientOriginalExtension();
            $filename = 'deliverable_' . $serviceRequest->tracking_number . '_' . time() . '.' . $extension;

            // Store in storage/app/public/deliverables
            $path = $file->storeAs('deliverables', $filename, 'public');
            $updateData['delivered_file_path'] = $path;
        }

        $oldStatus = $serviceRequest->status;
        $serviceRequest->update($updateData);

        // Automated WhatsApp & SMS Notification Dispatch
        $notifier = app(\App\Services\NotificationService::class);
        if ($request->hasFile('delivered_file')) {
            $notifier->notifyDeliverableUploaded($serviceRequest);
        } elseif ($oldStatus !== $validated['status']) {
            $notifier->notifyServiceStatusChange($serviceRequest, $validated['status'], $validated['admin_notes'] ?? null);
        }

        return back()->with('success', "Order #{$serviceRequest->tracking_number} status kamiyabi se update ho gaya! Client ko automated WhatsApp/SMS alert bhej diya gaya hai.");
    }

    /**
     * AJAX Live Re-sync FBR IRIS verification snapshot for an application
     */
    public function syncFbr(Request $request, ServiceRequest $serviceRequest)
    {
        $payload = is_array($serviceRequest->data_payload) ? $serviceRequest->data_payload : [];
        $identifier = $request->input('identifier')
            ?? ($payload['applicant_cnic'] ?? null)
            ?? ($payload['cnic'] ?? null)
            ?? ($payload['ntn'] ?? null)
            ?? ($serviceRequest->client?->cnic ?? '42101-1234567-1');

        $fbrService = app(\App\Services\FbrVerificationService::class);
        $atl = $fbrService->verifyAtl($identifier);
        $profile = $fbrService->verifyNtn($identifier);

        $snapshot = [
            'fetched_at' => now()->format('d M Y, h:i A'),
            'identifier' => $identifier,
            'atl' => $atl,
            'profile' => $profile,
            'auto_synced' => true,
        ];

        $payload['_fbr_iris_snapshot'] = $snapshot;
        $serviceRequest->data_payload = $payload;
        $serviceRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'FBR IRIS live record re-synced successfully!',
            'snapshot' => $snapshot,
        ]);
    }

    /**
     * Delete Corporate / NTN Order
     */
    public function destroy(ServiceRequest $serviceRequest)
    {
        $tracking = $serviceRequest->tracking_number;
        if ($serviceRequest->delivered_file_path && Storage::disk('public')->exists($serviceRequest->delivered_file_path)) {
            Storage::disk('public')->delete($serviceRequest->delivered_file_path);
        }

        $serviceRequest->delete();

        return redirect()->route('admin.orders.index')
            ->with('success', "Order #{$tracking} kamiyabi se delete ho gaya!");
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CallbackRequest;
use App\Models\Client;
use App\Models\ServiceRequest;
use App\Models\TaxReturn;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isCompany()) {
            $totalClients = Client::count();
            $totalReturns = TaxReturn::count();
            $inReviewReturns = TaxReturn::where('status', 'in_review')->count();
            $readyToFile = TaxReturn::where('status', 'ready_to_file')->count();
            $filedReturns = TaxReturn::where('status', 'filed')->count();
            $draftReturns = TaxReturn::where('status', 'draft')->count();

            // Financial Metrics
            $totalTaxableVolume = TaxReturn::sum('taxable_income');
            $returnsRevenue = TaxReturn::whereIn('status', ['filed', 'ready_to_file'])->sum('fee_amount');
            $servicesRevenue = ServiceRequest::where('payment_status', 'paid')->sum('fee_amount');
            $totalRevenue = $returnsRevenue + $servicesRevenue;

            $recentReturns = TaxReturn::with(['client', 'taxYear'])
                ->latest()
                ->take(25)
                ->get();

            $recentClients = Client::withCount('taxReturns')
                ->latest()
                ->take(20)
                ->get();

            $serviceRequests = ServiceRequest::latest()
                ->take(30)
                ->get();
            $pendingRequestsCount = ServiceRequest::where('status', 'submitted')->count();

            $callbacks = CallbackRequest::latest()
                ->take(30)
                ->get();
            $pendingCallbacksCount = CallbackRequest::where('status', 'pending')->count();
            $consultancy = $user->consultancy ?? \App\Models\Consultancy::first();

            return view('dashboard.company', compact(
                'totalClients',
                'totalReturns',
                'inReviewReturns',
                'readyToFile',
                'filedReturns',
                'draftReturns',
                'totalTaxableVolume',
                'totalRevenue',
                'recentReturns',
                'recentClients',
                'serviceRequests',
                'pendingRequestsCount',
                'callbacks',
                'pendingCallbacksCount',
                'consultancy'
            ));
        }

        // Client dashboard
        $client = $user->client;
        $myReturns = $client ? $client->taxReturns()->with('taxYear')->latest()->get() : collect();
        $myServiceRequests = ServiceRequest::where('user_id', $user->id)
            ->orWhere('applicant_email', $user->email)
            ->latest()
            ->get();

        return view('dashboard.client', compact('client', 'myReturns', 'myServiceRequests'));
    }

    /**
     * Consultant status updater for Callback Requests
     */
    public function updateCallbackStatus(Request $request, CallbackRequest $callback)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,contacted,converted,closed'],
            'admin_response_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $callback->update([
            'status' => $validated['status'],
            'handled_by' => auth()->id(),
            'admin_response_notes' => $validated['admin_response_notes'] ?? $callback->admin_response_notes,
        ]);

        return back()->with('success', 'Callback status kamiyabi se update ho gaya.');
    }

    /**
     * Consultant status updater for Corporate Service Requests
     */
    public function updateServiceRequestStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:draft,submitted,in_review,completed,cancelled'],
            'payment_status' => ['nullable', 'in:pending,paid'],
            'delivered_reference_no' => ['nullable', 'string', 'max:255'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $serviceRequest->update($validated);

        return back()->with('success', "Service application #{$serviceRequest->tracking_number} status updated.");
    }
}

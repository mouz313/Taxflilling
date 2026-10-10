<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CallbackRequest;
use Illuminate\Http\Request;

class CallRequestController extends Controller
{
    /**
     * Dedicated Performance Board & Lead Inquiries Desk
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $search = $request->input('search');

        // Performance KPI Metrics
        $totalCallbacks = CallbackRequest::count();
        $pendingCount = CallbackRequest::where('status', 'pending')->count();
        $contactedCount = CallbackRequest::where('status', 'contacted')->count();
        $convertedCount = CallbackRequest::where('status', 'converted')->count();
        $closedCount = CallbackRequest::where('status', 'closed')->count();

        // Conversion Rate %
        $conversionRate = $totalCallbacks > 0 
            ? round(($convertedCount / $totalCallbacks) * 100, 1) 
            : 0;

        // Query Builder with Filters
        $callbacksQuery = CallbackRequest::with('consultancy')
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('service_name', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->latest();

        $callbacks = $callbacksQuery->paginate(15)->withQueryString();

        return view('admin.callbacks.index', compact(
            'callbacks',
            'status',
            'search',
            'totalCallbacks',
            'pendingCount',
            'contactedCount',
            'convertedCount',
            'closedCount',
            'conversionRate'
        ));
    }

    /**
     * Update Callback Status with Consultant Notes
     */
    public function updateStatus(Request $request, CallbackRequest $callback)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,contacted,converted,closed'],
            'admin_response_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $callback->update([
            'status' => $validated['status'],
            'handled_by' => auth()->id(),
            'admin_response_notes' => $validated['admin_response_notes'] ?? $callback->admin_response_notes,
        ]);

        return back()->with('success', "Call request ({$callback->name}) ka status kamiyabi se update ho gaya.");
    }
}

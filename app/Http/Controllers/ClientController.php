<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    /**
     * Display a listing of clients with uniform search and pagination.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $totalClients = Client::count();
        $activeClients = Client::where('status', 'active')->count();
        $pendingNtnClients = Client::where('status', 'pending_ntn')->orWhereNull('ntn')->count();
        $withReturnsCount = Client::has('taxReturns')->count();

        $clients = Client::search($search)
            ->when($status, fn($q) => $q->where('status', $status))
            ->withCount('taxReturns')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('clients.index', compact(
            'clients',
            'search',
            'status',
            'totalClients',
            'activeClients',
            'pendingNtnClients',
            'withReturnsCount'
        ));
    }

    /**
     * Show form to register a new client.
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Store a newly created client.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cnic' => ['required', 'string', 'regex:/^[0-9]{5}-?[0-9]{7}-?[0-9]{1}$/'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'ntn' => ['nullable', 'string', 'max:30'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'create_login_account' => ['nullable', 'boolean'],
            'account_password' => ['nullable', 'string', 'min:6'],
        ]);

        // Clean CNIC to standardized format 42101-1234567-1
        $cleanedCnic = preg_replace('/[^0-9]/', '', $validated['cnic']);
        if (strlen($cleanedCnic) === 13) {
            $formattedCnic = substr($cleanedCnic, 0, 5) . '-' . substr($cleanedCnic, 5, 7) . '-' . substr($cleanedCnic, 12, 1);
        } else {
            $formattedCnic = $validated['cnic'];
        }

        // Blind Index check to prevent duplicate CNIC per consultancy
        $cnicHash = Client::generateCnicHash($formattedCnic);
        $consultancyId = auth()->user()->consultancy_id;

        $exists = Client::where('consultancy_id', $consultancyId)
            ->where('cnic_hash', $cnicHash)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'cnic' => 'Yeh CNIC pehle se is consultancy mein register hai.',
            ]);
        }

        // Optional User account creation
        $userId = null;
        if ($request->boolean('create_login_account') && ! empty($validated['email'])) {
            $existingUser = User::where('email', $validated['email'])->first();
            if ($existingUser) {
                return back()->withInput()->withErrors([
                    'email' => 'Yeh email address pehle se use mein hai.',
                ]);
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($request->input('account_password', 'client123')),
                'consultancy_id' => $consultancyId,
                'role' => 'client',
                'phone' => $validated['phone'],
                'is_active' => true,
            ]);
            $userId = $user->id;
        }

        $client = Client::create([
            'consultancy_id' => $consultancyId,
            'user_id' => $userId,
            'name' => $validated['name'],
            'cnic' => $formattedCnic,
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'ntn' => $validated['ntn'] ?? null,
            'father_name' => $validated['father_name'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => empty($validated['ntn']) ? 'pending_ntn' : 'active',
            'internal_notes' => $validated['internal_notes'] ?? null,
        ]);

        return redirect()->route('clients.show', $client)
            ->with('success', "Client '{$client->name}' kamiyabi se add ho gaya!");
    }

    /**
     * Display client profile and return history.
     */
    public function show(Client $client)
    {
        $client->load(['taxReturns.taxYear', 'documents']);
        return view('clients.show', compact('client'));
    }

    /**
     * Show edit form.
     */
    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    /**
     * Update client details.
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'ntn' => ['nullable', 'string', 'max:30'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'string'],
            'internal_notes' => ['nullable', 'string'],
        ]);

        $client->update($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client profile kamiyabi se update ho gayi!');
    }

    /**
     * Delete a client and their associated returns
     */
    public function destroy(Client $client)
    {
        $name = $client->name;

        foreach ($client->taxReturns as $return) {
            $return->incomeItems()->delete();
            $return->taxAdjustments()->delete();
            $return->taxDeductions()->delete();
            $return->wealthAssets()->delete();
            $return->wealthLiabilities()->delete();
            $return->wealthReconciliation()->delete();
            $return->statusLogs()->delete();
            $return->queries()->delete();
            $return->documents()->delete();
            $return->delete();
        }

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', "Client '{$name}' aur unka record kamiyabi se delete ho gaya!");
    }
}

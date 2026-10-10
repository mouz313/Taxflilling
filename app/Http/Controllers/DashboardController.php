<?php

namespace App\Http\Controllers;

use App\Models\CallbackRequest;
use App\Models\Consultancy;
use App\Models\ServiceRequest;
use App\Models\TaxReturn;
use App\Models\TaxYear;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return $user->isCompany()
            ? $this->company($request, $user)
            : $this->client($user);
    }

    private function company(Request $request, User $user)
    {
        $consultancy = $user->consultancy ?? Consultancy::first();

        // Tax year filter: ?year=ID, warna active year
        $years = TaxYear::orderByDesc('id')->get();
        $year = $years->firstWhere('id', $request->integer('year'))
            ?? $years->firstWhere('is_active', true)
            ?? $years->first();

        // Har call par fresh builder (clone ke masail se bachne ke liye)
        $returns = fn () => TaxReturn::query()
            ->when($year, fn ($q) => $q->where('tax_year_id', $year->id));

        // Ek hi query se sab statuses ke counts
        $byStatus = $returns()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $count = fn (string ...$keys) => (int) collect($keys)->sum(fn ($k) => $byStatus[$k] ?? 0);

        $stats = [
            'in_review'      => $count('in_review'),
            'ready_to_file'  => $count('ready_to_file'),
            'waiting_client' => $count('docs_pending', 'query'),
            'filed'          => $count('filed', 'closed'),
            'total'          => (int) $byStatus->sum(),
        ];

        // Paise: sirf asli paid/outstanding
        $money = [
            'collected_returns'  => (float) $returns()->where('fee_status', 'paid')->sum('fee_amount'),
            'outstanding'        => (float) $returns()
                ->where('fee_status', '!=', 'paid')
                ->where('status', '!=', 'draft')
                ->sum('fee_amount'),
            'collected_services' => (float) ServiceRequest::where('payment_status', 'paid')->sum('fee_amount'),
        ];

        // Needs attention: company ke action wale returns, unread client messages ke saath
        $clientUserIds = User::where('role', 'client')->select('id');

        $attention = $returns()
            ->with(['client', 'taxYear'])
            ->withCount(['queries as unread_queries_count' => fn ($q) => $q
                ->where('is_read', false)
                ->whereIn('user_id', $clientUserIds),
            ])
            ->whereIn('status', ['in_review', 'ready_to_file', 'query', 'docs_pending'])
            ->orderByRaw("CASE status WHEN 'ready_to_file' THEN 1 WHEN 'in_review' THEN 2 WHEN 'query' THEN 3 ELSE 4 END")
            ->latest('updated_at')
            ->limit(15)
            ->get();

        // Leads aur orders: sirf 5-5 aur counts
        $leads = CallbackRequest::where('status', 'pending')->latest()->limit(5)->get();
        $orders = ServiceRequest::where('status', 'submitted')->latest()->limit(5)->get();
        $pendingLeads = CallbackRequest::where('status', 'pending')->count();
        $pendingOrders = ServiceRequest::where('status', 'submitted')->count();

        // Deadline (tax_years.filing_deadline). FBR extension ho to yahan update karein.
        $deadline = $year?->filing_deadline ? Carbon::parse($year->filing_deadline) : null;
        $daysLeft = $deadline
            ? (int) now()->startOfDay()->diffInDays($deadline->copy()->startOfDay(), false)
            : null;

        return view('dashboard.company', compact(
            'consultancy', 'years', 'year', 'stats', 'money', 'attention',
            'leads', 'orders', 'pendingLeads', 'pendingOrders', 'deadline', 'daysLeft'
        ));
    }

    private function client(User $user)
    {
        $client = $user->client;
        $returns = $client
            ? $client->taxReturns()->with('taxYear')->latest()->get()
            : collect();
        $current = $returns->first();

        // To-do list asli data se
        $todo = [];
        $deadline = null;
        $daysLeft = null;

        if ($current) {
            $deadline = $current->taxYear?->filing_deadline
                ? Carbon::parse($current->taxYear->filing_deadline)
                : null;
            $daysLeft = $deadline
                ? (int) now()->startOfDay()->diffInDays($deadline->copy()->startOfDay(), false)
                : null;

            $unread = $current->queries()
                ->where('is_read', false)
                ->where('user_id', '!=', $user->id)
                ->get();

            if ($unread->isNotEmpty()) {
                $todo[] = [
                    'tone'  => 'orange',
                    'title' => 'Consultant ka naya sawal (' . $unread->count() . ')',
                    'body'  => (string) str($unread->first()->message)->limit(120),
                    'url'   => route('returns.wizard', $current),
                    'cta'   => 'Jawab dein',
                ];
            }

            if ($current->status === 'docs_pending') {
                $docCount = $current->documents()->count();
                $todo[] = [
                    'tone'  => 'amber',
                    'title' => 'Documents chahiye',
                    'body'  => $docCount > 0
                        ? "Aap ne abhi tak {$docCount} document upload kiye hain."
                        : 'Abhi tak koi document upload nahi hua.',
                    'url'   => route('wizard.filing', $current),
                    'cta'   => 'Upload karein',
                ];
            }

            if ($current->status === 'draft') {
                $todo[] = [
                    'tone'  => 'blue',
                    'title' => 'Return mukammal karein',
                    'body'  => 'Return abhi draft mein hai. Details bhar kar submit karein.',
                    'url'   => route('wizard.filing', $current),
                    'cta'   => 'Continue',
                ];
            }

            if ($current->status !== 'draft' && $current->fee_status !== 'paid') {
                $todo[] = [
                    'tone'  => 'slate',
                    'title' => 'Fee baqi hai: PKR ' . number_format($current->fee_amount, 0),
                    'body'  => 'Fee ki tasdeeq hone tak consultant filing shuru nahi karega.',
                    'url'   => route('wizard.filing', ['return' => $current, 'step' => 12]),
                    'cta'   => 'Payment details',
                ];
            }
        }

        $activeYear = TaxYear::where('is_active', true)->first() ?? TaxYear::latest()->first();
        if (! $deadline && $activeYear?->filing_deadline) {
            $deadline = Carbon::parse($activeYear->filing_deadline);
            $daysLeft = (int) now()->startOfDay()->diffInDays($deadline->copy()->startOfDay(), false);
        }
        $consultancy = $user->consultancy ?? Consultancy::first();

        // orWhere ab group ke andar, taake scope/filters na tootein
        $mine = fn () => ServiceRequest::where(fn ($q) => $q
            ->where('user_id', $user->id)
            ->orWhere('applicant_email', $user->email));

        $servicesCount = $mine()->count();
        $services = $mine()->latest()->limit(5)->get();

        return view('dashboard.client', compact(
            'client', 'returns', 'current', 'todo', 'deadline', 'daysLeft',
            'services', 'servicesCount', 'activeYear', 'consultancy'
        ));
    }

    /**
     * Start a new tax return for the logged-in client with 1 click
     */
    public function startReturn(Request $request)
    {
        $user = $request->user();
        $consultancy = $user->consultancy ?? Consultancy::first();
        $client = $user->client;

        if (! $client) {
            $client = Client::create([
                'user_id' => $user->id,
                'consultancy_id' => $consultancy?->id ?? 1,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '03001234567',
                'cnic' => '42101-1234567-1',
            ]);
        }

        $activeYear = TaxYear::where('is_active', true)->first() ?? TaxYear::latest()->first();
        if (! $activeYear) {
            return back()->with('error', 'Tax year available nahi hai.');
        }

        $existing = TaxReturn::where('client_id', $client->id)
            ->where('tax_year_id', $activeYear->id)
            ->first();

        if ($existing) {
            return redirect()->route('wizard.filing', ['return' => $existing, 'step' => 1]);
        }

        $return = TaxReturn::create([
            'consultancy_id' => $consultancy?->id ?? 1,
            'client_id' => $client->id,
            'tax_year_id' => $activeYear->id,
            'filing_type' => 'original',
            'status' => 'draft',
            'fee_amount' => $consultancy?->default_return_fee ?? 3000,
            'fee_status' => 'pending',
            'selected_categories' => ['salary', 'advance_tax', 'expenses', 'assets', 'reconciliation'],
        ]);

        $return->wealthReconciliation()->create([
            'opening_wealth' => 0,
            'net_inflows' => 0,
            'living_expenses' => 0,
            'other_outflows' => 0,
            'closing_wealth' => 0,
            'unreconciled_difference' => 0,
        ]);

        return redirect()->route('wizard.filing', ['return' => $return, 'step' => 1])
            ->with('success', 'Aapka naya tax return shuru ho chuka hai! Step 1 fill karein.');
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

<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\IncomeItem;
use App\Models\TaxAdjustment;
use App\Models\TaxDeduction;
use App\Models\TaxReturn;
use App\Models\TaxYear;
use App\Models\WealthAsset;
use App\Models\WealthLiability;
use Illuminate\Http\Request;

class TaxReturnController extends Controller
{
    /**
     * Display listing of returns in pipeline.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $taxYearId = $request->input('tax_year_id');

        $totalReturns = TaxReturn::count();
        $inReviewReturns = TaxReturn::where('status', 'in_review')->count();
        $readyToFile = TaxReturn::where('status', 'ready_to_file')->count();
        $filedReturns = TaxReturn::where('status', 'filed')->count();
        $draftReturns = TaxReturn::where('status', 'draft')->count();
        $totalTaxVolume = TaxReturn::sum('taxable_income');

        $returns = TaxReturn::with(['client', 'taxYear'])
            ->search($search)
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($taxYearId, fn($q) => $q->where('tax_year_id', $taxYearId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $taxYears = TaxYear::where('is_active', true)->get();

        return view('returns.index', compact(
            'returns',
            'search',
            'status',
            'taxYearId',
            'taxYears',
            'totalReturns',
            'inReviewReturns',
            'readyToFile',
            'filedReturns',
            'draftReturns',
            'totalTaxVolume'
        ));
    }

    /**
     * Show form to initiate new return.
     */
    public function create(Request $request)
    {
        $clients = Client::orderBy('name')->get();
        $taxYears = TaxYear::where('is_active', true)->get();
        $selectedClientId = $request->input('client_id');

        return view('returns.create', compact('clients', 'taxYears', 'selectedClientId'));
    }

    /**
     * Store new tax return and redirect directly into the 12-Category Visual Wizard!
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'tax_year_id' => ['required', 'exists:tax_years,id'],
            'filing_type' => ['required', 'in:original,amendment'],
        ]);

        $consultancyId = auth()->user()->consultancy_id;

        $existing = TaxReturn::where('client_id', $validated['client_id'])
            ->where('tax_year_id', $validated['tax_year_id'])
            ->where('amendment_no', 0)
            ->first();

        if ($existing) {
            return redirect()->route('returns.wizard', $existing)
                ->with('info', 'Is client ka return pehle se maujood hai.');
        }

        // Default selected categories (Salary + Advance Tax + Expenses + Assets)
        $defaultCategories = ['salary', 'advance_tax', 'expenses', 'assets', 'reconciliation'];

        $taxReturn = TaxReturn::create([
            'consultancy_id' => $consultancyId,
            'client_id' => $validated['client_id'],
            'tax_year_id' => $validated['tax_year_id'],
            'filing_type' => $validated['filing_type'],
            'selected_categories' => $defaultCategories,
            'status' => 'draft',
            'fee_amount' => auth()->user()->consultancy->default_return_fee ?? 2500.00,
        ]);

        $taxReturn->wealthReconciliation()->create([
            'opening_wealth' => 0,
            'net_inflows' => 0,
            'living_expenses' => 0,
            'other_outflows' => 0,
            'closing_wealth' => 0,
            'unreconciled_difference' => 0,
        ]);

        return redirect()->route('returns.wizard', $taxReturn)
            ->with('success', 'Tax return ban gaya! Apni aamdani aur asason ki categories select karein.');
    }

    /**
     * The 12-Category Interactive Visual Wizard (Befiler-Style)
     */
    public function wizard(TaxReturn $return)
    {
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

        // Definition of all 12 Categories with Icons and FBR Codes
        $allCategories = [
            'salary' => [
                'icon' => '💼',
                'title' => 'Salary Income',
                'urdu' => 'Tankhwah / Mulazmat',
                'desc' => 'Monthly salary, allowances, provident fund, employer tax',
                'iris_code' => 'Code 7001 / 640101',
            ],
            'business' => [
                'icon' => '🏪',
                'title' => 'Business & Freelance',
                'urdu' => 'Karobar / Services',
                'desc' => 'Sole proprietor, IT exports, shop, consultancy revenue',
                'iris_code' => 'Code 7010 / 7014',
            ],
            'rental' => [
                'icon' => '🏠',
                'title' => 'Property Rent',
                'urdu' => 'Kiraya Aamdani',
                'desc' => 'Commercial or residential rent received (Sec 155)',
                'iris_code' => 'Code 7005',
            ],
            'capital_gains' => [
                'icon' => '📈',
                'title' => 'Capital Gains',
                'urdu' => 'Shares & Plot Sale',
                'desc' => 'Stock exchange shares, mutual funds, real estate profit',
                'iris_code' => 'Code 7007',
            ],
            'agri' => [
                'icon' => '🌾',
                'title' => 'Agriculture Income',
                'urdu' => 'Zaraati Aamdani',
                'desc' => 'Agricultural land harvest, lease or farming income',
                'iris_code' => 'Code 7009',
            ],
            'foreign' => [
                'icon' => '🌍',
                'title' => 'Foreign Remittance',
                'urdu' => 'Beroon-e-Mulk Kamai',
                'desc' => 'Home remittances via banking channels (Sec 111(4))',
                'iris_code' => 'Code 7012',
            ],
            'other_income' => [
                'icon' => '💰',
                'title' => 'Other Sources',
                'urdu' => 'Bank Munafa & Dividend',
                'desc' => 'Bank savings profit (Sec 151), dividends, prize bonds',
                'iris_code' => 'Code 7013 / 7008',
            ],
            'advance_tax' => [
                'icon' => '🧾',
                'title' => 'Advance / Deducted Tax',
                'urdu' => 'Katta Hua Advance Tax',
                'desc' => 'Mobile bills, vehicle token tax, electricity bill, bank tax',
                'iris_code' => 'Code 6400 Series',
            ],
            'deductions' => [
                'icon' => '🎁',
                'title' => 'Tax Rebates & Zakat',
                'urdu' => 'Zakat / Charity / Pension',
                'desc' => 'Zakat deducted, Sec 61 donations, VPS pension fund',
                'iris_code' => 'Code 9000 Series',
            ],
            'expenses' => [
                'icon' => '🛒',
                'title' => 'Household Expenses',
                'urdu' => 'Gharelu & Zati Kharch',
                'desc' => 'Annual family living expenses, utilities, education, travel',
                'iris_code' => 'Code 7087',
            ],
            'assets' => [
                'icon' => '💎',
                'title' => 'Wealth Assets (Possessions)',
                'urdu' => 'Asasay (Ghar, Gari, Bank)',
                'desc' => 'Properties, cars, 30 June bank balances, gold, cash in hand',
                'iris_code' => 'Code 7001-7014 (Sec 116)',
            ],
            'reconciliation' => [
                'icon' => '⚖️',
                'title' => 'Liabilities & Reconciliation',
                'urdu' => 'Karz & Tally Check',
                'desc' => 'Bank loans, borrowings, opening wealth vs closing tally',
                'iris_code' => 'Code 7015 / 7099',
            ],
        ];

        // Fetch Live FBR IRIS Verification Snapshot for Client
        $fbrService = app(\App\Services\FbrVerificationService::class);
        $cnic = $return->client?->cnic ?? '42101-1234567-1';
        $atl = $fbrService->verifyAtl($cnic);
        $ntnProfile = $fbrService->verifyNtn($cnic);

        $fbrSnapshot = [
            'fetched_at' => now()->format('d M Y, h:i A'),
            'identifier' => $cnic,
            'atl' => $atl,
            'profile' => $ntnProfile,
        ];

        return view('returns.wizard', compact('return', 'allCategories', 'fbrSnapshot'));
    }

    /**
     * AJAX Live Re-sync FBR IRIS record for this return's client
     */
    public function syncFbr(Request $request, TaxReturn $return)
    {
        $fbrService = app(\App\Services\FbrVerificationService::class);
        $cnic = $return->client?->cnic ?? '42101-1234567-1';
        $atl = $fbrService->verifyAtl($cnic);
        $ntnProfile = $fbrService->verifyNtn($cnic);

        $fbrSnapshot = [
            'fetched_at' => now()->format('d M Y, h:i A'),
            'identifier' => $cnic,
            'atl' => $atl,
            'profile' => $ntnProfile,
        ];

        return response()->json([
            'success' => true,
            'message' => 'FBR IRIS live record synchronized successfully!',
            'snapshot' => $fbrSnapshot,
        ]);
    }

    /**
     * Show return dossier (FBR IRIS Console).
     */
    public function show(TaxReturn $return)
    {
        $return->load([
            'client',
            'taxYear',
            'incomeItems',
            'taxAdjustments',
            'taxDeductions',
            'wealthAssets',
            'wealthLiabilities',
            'wealthReconciliation',
            'statusLogs.changedBy',
        ]);

        return view('returns.show', compact('return'));
    }

    /**
     * Update active categories from wizard
     */
    public function updateCategories(Request $request, TaxReturn $return)
    {
        $categories = $request->input('categories', []);
        $return->update(['selected_categories' => $categories]);

        return back()->with('success', 'Selected categories update ho gayeen!');
    }

    /**
     * Category 1-7: Add Income Item
     */
    public function addIncomeItem(Request $request, TaxReturn $return)
    {
        if ($return->is_locked) return back()->with('error', 'Return locked hai.');

        $validated = $request->validate([
            'source_type' => ['required', 'string'],
            'iris_box_code' => ['nullable', 'string'],
            'employer_or_institution' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'gross_amount' => ['required', 'numeric', 'min:0'],
            'exempt_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_deducted' => ['nullable', 'numeric', 'min:0'],
        ]);

        $return->incomeItems()->create($validated);
        return back()->with('success', 'Income entry save ho gayi!');
    }

    public function removeIncomeItem(IncomeItem $item)
    {
        if ($item->taxReturn->is_locked) return back()->with('error', 'Return locked hai.');
        $item->delete();
        return back()->with('success', 'Entry delete ho gayi.');
    }

    /**
     * Category 8: Advance Tax Adjustments
     */
    public function addAdjustment(Request $request, TaxReturn $return)
    {
        if ($return->is_locked) return back()->with('error', 'Return locked hai.');

        $validated = $request->validate([
            'adjustment_type' => ['required', 'string'],
            'iris_box_code' => ['required', 'string'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'institution_name' => ['nullable', 'string', 'max:100'],
            'tax_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $return->taxAdjustments()->create($validated);
        return back()->with('success', 'Advance tax entry add ho gayi!');
    }

    public function removeAdjustment(TaxAdjustment $adjustment)
    {
        if ($adjustment->taxReturn->is_locked) return back()->with('error', 'Return locked hai.');
        $adjustment->delete();
        return back()->with('success', 'Adjustment entry delete ho gayi.');
    }

    /**
     * Category 9: Tax Deductions & Credits
     */
    public function addDeduction(Request $request, TaxReturn $return)
    {
        if ($return->is_locked) return back()->with('error', 'Return locked hai.');

        $validated = $request->validate([
            'deduction_type' => ['required', 'string'],
            'iris_box_code' => ['required', 'string'],
            'institution_or_fund' => ['nullable', 'string', 'max:255'],
            'eligible_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $return->taxDeductions()->create($validated);
        return back()->with('success', 'Deduction / Credit add ho gaya!');
    }

    public function removeDeduction(TaxDeduction $deduction)
    {
        if ($deduction->taxReturn->is_locked) return back()->with('error', 'Return locked hai.');
        $deduction->delete();
        return back()->with('success', 'Deduction delete ho gayi.');
    }

    /**
     * Category 10: Household Expenses
     */
    public function updateExpenses(Request $request, TaxReturn $return)
    {
        if ($return->is_locked) return back()->with('error', 'Return locked hai.');

        $validated = $request->validate([
            'household_expenses' => ['required', 'numeric', 'min:0'],
        ]);

        $return->update($validated);

        // Also sync into wealth reconciliation living expenses
        $return->wealthReconciliation()->updateOrCreate(
            ['tax_return_id' => $return->id],
            ['living_expenses' => $validated['household_expenses']]
        );

        return back()->with('success', 'Gharelu kharcha update ho gaya!');
    }

    /**
     * Category 11: Wealth Assets
     */
    public function addAsset(Request $request, TaxReturn $return)
    {
        if ($return->is_locked) return back()->with('error', 'Return locked hai.');

        $validated = $request->validate([
            'asset_type' => ['required', 'string'],
            'iris_box_code' => ['required', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string'],
            'cost_value' => ['required', 'numeric', 'min:0'],
        ]);

        $return->wealthAssets()->create($validated);

        // Update closing wealth in wealth reconciliation
        $totalAssets = $return->wealthAssets()->sum('cost_value');
        $totalLiabilities = $return->wealthLiabilities()->sum('outstanding_amount');
        $netClosingWealth = max(0, $totalAssets - $totalLiabilities);

        $return->wealthReconciliation()->updateOrCreate(
            ['tax_return_id' => $return->id],
            ['closing_wealth' => $netClosingWealth]
        );

        return back()->with('success', 'Asasa (Asset) kamiyabi se add ho gaya!');
    }

    public function removeAsset(WealthAsset $asset)
    {
        $return = $asset->taxReturn;
        if ($return->is_locked) return back()->with('error', 'Return locked hai.');
        $asset->delete();

        $totalAssets = $return->wealthAssets()->sum('cost_value');
        $totalLiabilities = $return->wealthLiabilities()->sum('outstanding_amount');
        $return->wealthReconciliation()->updateOrCreate(
            ['tax_return_id' => $return->id],
            ['closing_wealth' => max(0, $totalAssets - $totalLiabilities)]
        );

        return back()->with('success', 'Asset delete ho gaya.');
    }

    /**
     * Category 12: Liabilities & Reconciliation
     */
    public function addLiability(Request $request, TaxReturn $return)
    {
        if ($return->is_locked) return back()->with('error', 'Return locked hai.');

        $validated = $request->validate([
            'liability_type' => ['required', 'string'],
            'iris_box_code' => ['required', 'string'],
            'creditor_name' => ['required', 'string', 'max:255'],
            'outstanding_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $return->wealthLiabilities()->create($validated);

        $totalAssets = $return->wealthAssets()->sum('cost_value');
        $totalLiabilities = $return->wealthLiabilities()->sum('outstanding_amount');
        $return->wealthReconciliation()->updateOrCreate(
            ['tax_return_id' => $return->id],
            ['closing_wealth' => max(0, $totalAssets - $totalLiabilities)]
        );

        return back()->with('success', 'Karz (Liability) entry add ho gayi!');
    }

    public function removeLiability(WealthLiability $liability)
    {
        $return = $liability->taxReturn;
        if ($return->is_locked) return back()->with('error', 'Return locked hai.');
        $liability->delete();

        $totalAssets = $return->wealthAssets()->sum('cost_value');
        $totalLiabilities = $return->wealthLiabilities()->sum('outstanding_amount');
        $return->wealthReconciliation()->updateOrCreate(
            ['tax_return_id' => $return->id],
            ['closing_wealth' => max(0, $totalAssets - $totalLiabilities)]
        );

        return back()->with('success', 'Liability delete ho gayi.');
    }

    /**
     * Update Wealth Opening & Inflows
     */
    public function updateWealth(Request $request, TaxReturn $return)
    {
        if ($return->is_locked) return back()->with('error', 'Return locked hai.');

        $validated = $request->validate([
            'opening_wealth' => ['required', 'numeric'],
            'net_inflows' => ['required', 'numeric'],
            'other_outflows' => ['required', 'numeric', 'min:0'],
        ]);

        $return->wealthReconciliation()->updateOrCreate(
            ['tax_return_id' => $return->id],
            $validated
        );

        return back()->with('success', 'Wealth Reconciliation values save ho gayeen!');
    }

    /**
     * Transition return lifecycle status.
     */
    public function transitionStatus(Request $request, TaxReturn $return)
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'iris_acknowledgement_no' => ['nullable', 'string'],
            'iris_cpr_no' => ['nullable', 'string'],
        ]);

        $targetStatus = $validated['status'];

        if ($targetStatus === 'filed') {
            $request->validate([
                'iris_acknowledgement_no' => ['required', 'string'],
            ]);

            $return->iris_acknowledgement_no = $validated['iris_acknowledgement_no'];
            $return->iris_cpr_no = $validated['iris_cpr_no'] ?? null;
        }

        if (! $return->transitionTo($targetStatus, auth()->id(), $validated['notes'] ?? null)) {
            return back()->with('error', "Is status par tabdeel karne ki ijazat nahi hai.");
        }

        if ($targetStatus === 'filed') {
            app(\App\Services\NotificationService::class)->notifyReturnFiled($return);
        }

        return back()->with('success', "Status update ho gaya: " . ucfirst(str_replace('_', ' ', $targetStatus)) . ". Client ko automated WhatsApp alert bhej diya gaya hai.");
    }

    /**
     * Delete a tax return and its associated records.
     */
    public function destroy(TaxReturn $return)
    {
        $id = $return->id;
        $clientName = $return->client?->name ?? 'Client';
        $taxYearName = $return->taxYear?->name ?? '';

        // Delete child relations
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

        return redirect()->route('returns.index')
            ->with('success', "Tax Return #{$id} ({$clientName} - {$taxYearName}) kamiyabi se delete ho gaya!");
    }
}

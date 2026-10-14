<?php

namespace App\Http\Controllers;

use App\Models\TaxReturn;
use Illuminate\Http\Request;

class FilingWizardController extends Controller
{
    /**
     * Display the 12-Step Befiler Filing Wizard
     */
    public function show(Request $request, TaxReturn $return)
    {
        \Illuminate\Support\Facades\Gate::authorize('view', $return);

        $step = (int) $request->input('step', 1);
        if ($step < 1) $step = 1;
        if ($step > 12) $step = 12;

        $return->load([
            'client',
            'taxYear',
            'incomeItems',
            'taxAdjustments',
            'taxDeductions',
            'wealthAssets',
            'wealthReconciliation',
        ]);

        return view('wizard.filing', compact('return', 'step'));
    }

    /**
     * Process each step and advance to next
     */
    public function saveStep(Request $request, TaxReturn $return, int $step)
    {
        \Illuminate\Support\Facades\Gate::authorize('update', $return);

        if ($return->is_locked) {
            return back()->with('error', 'Return already filed & locked.');
        }

        switch ($step) {
            // Step 2: Fresh vs Already Filer
            case 2:
                $filerType = $request->input('filer_type', 'fresh_filer');
                $return->update(['filing_type' => ($filerType === 'fresh_filer' ? 'original' : 'amendment')]);
                break;

            // Step 3: Personal Information
            case 3:
                $validated = $request->validate([
                    'name' => ['required', 'string'],
                    'phone' => ['required', 'string'],
                    'address' => ['nullable', 'string'],
                    'ntn' => ['nullable', 'string'],
                ]);
                $return->client->update($validated);
                break;

            // Step 5: Income Details (Salary, Business, Freelancing)
            case 5:
                $salaryFreq = $request->input('salary_frequency', 'annual');
                $inputSalary = (float) $request->input('gross_salary', 0);
                $annualSalary = ($salaryFreq === 'monthly') ? ($inputSalary * 12) : $inputSalary;

                if ($annualSalary > 0) {
                    $return->incomeItems()->updateOrCreate(
                        ['source_type' => 'salary'],
                        [
                            'iris_box_code' => '7001',
                            'employer_or_institution' => $request->input('employer', 'Employer / Organization'),
                            'gross_amount' => $annualSalary,
                            'exempt_amount' => $request->input('exempt_salary', 0),
                            'tax_deducted' => (float) $request->input('salary_tax_deducted', 0),
                        ]
                    );
                }

                $businessRev = (float) $request->input('business_revenue', 0);
                if ($businessRev > 0) {
                    $return->incomeItems()->updateOrCreate(
                        ['source_type' => 'business'],
                        [
                            'iris_box_code' => '7010',
                            'employer_or_institution' => $request->input('business_name', 'Business / Freelance Services'),
                            'gross_amount' => $businessRev,
                            'exempt_amount' => (float) $request->input('business_expenses', 0),
                            'tax_deducted' => (float) $request->input('business_tax_deducted', 0),
                        ]
                    );
                }

                $return->recalculateTotals();
                break;

            // Step 6: Adjustable Taxes (Mobile, Vehicle) & Tax Credits
            case 6:
                if ($request->filled('mobile_tax') && (float) $request->input('mobile_tax') > 0) {
                    $return->taxAdjustments()->updateOrCreate(
                        ['adjustment_type' => 'mobile_sec236'],
                        [
                            'iris_box_code' => '64151',
                            'description' => 'Mobile Phone Tax (Sec 236)',
                            'tax_collected' => (float) $request->input('mobile_tax', 0),
                        ]
                    );
                }

                if ($request->filled('vehicle_tax') && (float) $request->input('vehicle_tax') > 0) {
                    $return->taxAdjustments()->updateOrCreate(
                        ['adjustment_type' => 'vehicle_sec231b'],
                        [
                            'iris_box_code' => '6405',
                            'description' => 'Vehicle Token / Purchase Tax (Sec 231B)',
                            'tax_collected' => (float) $request->input('vehicle_tax', 0),
                        ]
                    );
                }

                if ($request->filled('charity_amount') && (float) $request->input('charity_amount') > 0) {
                    $return->taxDeductions()->updateOrCreate(
                        ['deduction_type' => 'charity_sec61'],
                        [
                            'iris_box_code' => '9002',
                            'institution_or_fund' => $request->input('charity_ngo', 'Approved Charity'),
                            'eligible_amount' => (float) $request->input('charity_amount', 0),
                            'computed_credit_or_deduction' => round((float) $request->input('charity_amount', 0) * 0.15, 2),
                        ]
                    );
                }

                $return->recalculateTotals();
                break;

            // Step 7: Tax Deductions (Zakat Sec 60)
            case 7:
                if ($request->filled('zakat_amount') && $request->input('zakat_amount') > 0) {
                    $return->taxDeductions()->updateOrCreate(
                        ['deduction_type' => 'zakat'],
                        [
                            'iris_box_code' => '9001',
                            'institution_or_fund' => 'Bank / Zakat Fund',
                            'eligible_amount' => $request->input('zakat_amount', 0),
                            'computed_credit_or_deduction' => $request->input('zakat_amount', 0),
                        ]
                    );
                    $return->recalculateTotals();
                }
                break;

            // Step 8: Asset Details (Bank, Vehicle, Property)
            case 8:
                if ($request->filled('bank_balance')) {
                    $return->wealthAssets()->updateOrCreate(
                        ['asset_type' => 'bank_account'],
                        [
                            'iris_box_code' => '7006',
                            'title' => 'Bank Closing Balance (30 June)',
                            'cost_value' => (float) $request->input('bank_balance', 0),
                        ]
                    );
                }
                if ($request->filled('vehicle_value') && (float) $request->input('vehicle_value') > 0) {
                    $return->wealthAssets()->updateOrCreate(
                        ['asset_type' => 'vehicle'],
                        [
                            'iris_box_code' => '7005',
                            'title' => $request->input('vehicle_description', 'Personal Vehicle / Car'),
                            'cost_value' => (float) $request->input('vehicle_value', 0),
                        ]
                    );
                }
                if ($request->filled('property_value') && (float) $request->input('property_value') > 0) {
                    $return->wealthAssets()->updateOrCreate(
                        ['asset_type' => 'immovable_property'],
                        [
                            'iris_box_code' => '7002',
                            'title' => $request->input('property_description', 'House / Apartment'),
                            'cost_value' => (float) $request->input('property_value', 0),
                        ]
                    );
                }
                $return->recalculateTotals();
                break;

            // Step 9: Personal Expenses (Code 7087)
            case 9:
                $expenses = (float) $request->input('household_expenses', 0);
                $return->update(['household_expenses' => $expenses]);
                $return->wealthReconciliation()->updateOrCreate(
                    ['tax_return_id' => $return->id],
                    ['living_expenses' => $expenses]
                );
                $return->recalculateTotals();
                break;

            // Step 10: FBR Filing Authorization (Consent)
            case 10:
                if ($request->boolean('consent_consultant_files')) {
                    $return->statusLogs()->create([
                        'from_status' => $return->status,
                        'to_status' => $return->status,
                        'changed_by_user_id' => auth()->id(),
                        'notes' => 'Client ne consultant ko apni taraf se file karne ki ijazat di.',
                    ]);
                }
                break;

            // Step 12: Pay Now & Submit Return
            case 12:
                $tid = $request->input('transaction_reference', 'N/A');
                $method = $request->input('payment_method', 'bank_transfer');

                // fee_status pending hi rahe jab tak company ya verified gateway confirm na kare
                $return->update([
                    'status' => 'in_review',
                ]);

                $return->statusLogs()->create([
                    'from_status' => 'draft',
                    'to_status' => 'in_review',
                    'changed_by_user_id' => auth()->id(),
                    'notes' => "Client ne return submit kiya. Payment ({$method}) ref: {$tid} (unverified, company confirm karegi).",
                ]);

                return redirect()->route('dashboard')
                    ->with('success', 'Mubarak ho! Aapka tax return submit ho gaya hai. Consultant fee aur documents verify kar ke IRIS par file kar dega.');
        }

        $nextStep = $step + 1;
        if ($nextStep > 12) $nextStep = 12;

        return redirect()->route('wizard.filing', ['return' => $return, 'step' => $nextStep]);
    }
}

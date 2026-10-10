<?php

namespace App\Models;

use App\Traits\BelongsToConsultancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TaxReturn extends Model
{
    use HasFactory, BelongsToConsultancy;

    protected $fillable = [
        'consultancy_id',
        'client_id',
        'tax_year_id',
        'status',
        'filing_type',
        'amendment_no',
        'selected_categories',
        'is_locked',
        'total_income',
        'taxable_income',
        'computed_tax',
        'tax_credits',
        'tax_deducted_advance',
        'household_expenses',
        'net_tax_payable_or_refundable',
        'iris_acknowledgement_no',
        'iris_cpr_no',
        'filed_at',
        'filed_by_user_id',
        'fee_amount',
        'fee_status',
        'internal_notes',
    ];

    protected $casts = [
        'selected_categories' => 'array',
        'is_locked' => 'boolean',
        'total_income' => 'decimal:2',
        'taxable_income' => 'decimal:2',
        'computed_tax' => 'decimal:2',
        'tax_credits' => 'decimal:2',
        'tax_deducted_advance' => 'decimal:2',
        'household_expenses' => 'decimal:2',
        'net_tax_payable_or_refundable' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'filed_at' => 'datetime',
    ];

    /**
     * Allowed State Machine Transitions
     */
    public const ALLOWED_TRANSITIONS = [
        'draft' => ['docs_pending', 'in_review'],
        'docs_pending' => ['in_review', 'draft'],
        'in_review' => ['query', 'ready_to_file', 'docs_pending'],
        'query' => ['in_review'],
        'ready_to_file' => ['filed', 'in_review', 'query'],
        'filed' => ['closed'],
        'closed' => [],
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function taxYear(): BelongsTo
    {
        return $this->belongsTo(TaxYear::class);
    }

    public function filedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filed_by_user_id');
    }

    // 1-7. Income Items (Salary, Business, Rent, Capital Gains, Agri, Foreign, Other)
    public function incomeItems(): HasMany
    {
        return $this->hasMany(IncomeItem::class);
    }

    // 8. Advance / Withholding Taxes (Vehicles, Mobiles, Electricity, Banking)
    public function taxAdjustments(): HasMany
    {
        return $this->hasMany(TaxAdjustment::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(TaxAdjustment::class);
    }

    // 9. Tax Deductions & Credits (Zakat, Charity Sec 61, Pension Sec 63)
    public function taxDeductions(): HasMany
    {
        return $this->hasMany(TaxDeduction::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(TaxDeduction::class);
    }

    // 10. Wealth Statement Reconciliation
    public function wealthReconciliation(): HasOne
    {
        return $this->hasOne(WealthReconciliation::class);
    }

    // 11. Wealth Assets (Properties, Cars, Bank Accounts, Gold)
    public function wealthAssets(): HasMany
    {
        return $this->hasMany(WealthAsset::class);
    }

    // 12. Wealth Liabilities (Loans & Borrowings)
    public function wealthLiabilities(): HasMany
    {
        return $this->hasMany(WealthLiability::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(ReturnStatusLog::class)->latest();
    }

    public function queries(): HasMany
    {
        return $this->hasMany(Query::class)->latest();
    }

    /**
     * Check if a specific category is activated for this return
     */
    public function hasCategory(string $key): bool
    {
        if (empty($this->selected_categories)) {
            return true; // Default show all if not restricted
        }
        return in_array($key, $this->selected_categories, true);
    }

    /**
     * Recalculate all tax figures directly in Model across all 12 categories
     */
    public function recalculateTotals(): void
    {
        if ($this->is_locked) {
            return;
        }

        // 1. Sum Gross Income
        $totalGross = (float) $this->incomeItems()->sum('gross_amount');
        $totalExempt = (float) $this->incomeItems()->sum('exempt_amount');
        $netIncome = max(0, $totalGross - $totalExempt);

        // 2. Direct Deductions (Zakat etc.)
        $totalDeductions = (float) $this->taxDeductions()->where('deduction_type', 'zakat')->sum('eligible_amount');
        $taxableIncome = max(0, $netIncome - $totalDeductions);

        // 3. Gross Tax Liability from active Slabs
        $computedTax = TaxSlab::computeTax($taxableIncome, $this->tax_year_id, 'salaried');

        // 4. Tax Credits (Donation Sec 61, VPS Pension Sec 63)
        $taxCredits = (float) $this->taxDeductions()->where('deduction_type', '!=', 'zakat')->sum('computed_credit_or_deduction');

        // 5. Total Advance / Withholding Tax Paid (Salary slip + Bank + Mobile + Vehicle token)
        $salaryTaxDeducted = (float) $this->incomeItems()->sum('tax_deducted');
        $adjustmentsTaxDeducted = (float) $this->taxAdjustments()->sum('tax_amount');
        $totalAdvanceTax = $salaryTaxDeducted + $adjustmentsTaxDeducted;

        // 6. Net Payable (+) or Refundable (-)
        $netPayable = round($computedTax - $taxCredits - $totalAdvanceTax, 2);

        // Update model silently without recursive triggers
        $this->updateQuietly([
            'total_income' => $totalGross,
            'taxable_income' => $taxableIncome,
            'computed_tax' => $computedTax,
            'tax_credits' => $taxCredits,
            'tax_deducted_advance' => $totalAdvanceTax,
            'net_tax_payable_or_refundable' => $netPayable,
        ]);
    }

    public function canTransitionTo(string $targetStatus): bool
    {
        if ($this->is_locked && $targetStatus !== 'closed') {
            return false;
        }

        $allowed = self::ALLOWED_TRANSITIONS[$this->status] ?? [];
        return in_array($targetStatus, $allowed, true);
    }

    public function transitionTo(string $targetStatus, ?int $userId = null, ?string $notes = null): bool
    {
        if (! $this->canTransitionTo($targetStatus)) {
            return false;
        }

        $oldStatus = $this->status;
        $this->status = $targetStatus;

        if ($targetStatus === 'filed') {
            $this->is_locked = true;
            $this->filed_at = now();
            $this->filed_by_user_id = $userId ?? auth()->id();
        }

        $this->save();

        $this->statusLogs()->create([
            'from_status' => $oldStatus,
            'to_status' => $targetStatus,
            'changed_by_user_id' => $userId ?? auth()->id(),
            'notes' => $notes,
        ]);

        return true;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->whereHas('client', function ($q) use ($term) {
            $q->search($term);
        })->orWhere('iris_acknowledgement_no', 'like', "%{$term}%")
          ->orWhere('iris_cpr_no', 'like', "%{$term}%");
    }

    public function isPayable(): bool
    {
        return $this->net_tax_payable_or_refundable > 0;
    }

    public function isRefundable(): bool
    {
        return $this->net_tax_payable_or_refundable < 0;
    }
}

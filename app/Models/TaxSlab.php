<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxSlab extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_year_id',
        'taxpayer_type', // 'salaried', 'business'
        'min_income',
        'max_income',
        'fixed_tax',
        'percentage_rate',
        'exceeding_threshold',
    ];

    protected $casts = [
        'min_income' => 'decimal:2',
        'max_income' => 'decimal:2',
        'fixed_tax' => 'decimal:2',
        'percentage_rate' => 'decimal:2',
        'exceeding_threshold' => 'decimal:2',
    ];

    public function taxYear(): BelongsTo
    {
        return $this->belongsTo(TaxYear::class);
    }

    /**
     * Compute gross tax for a given taxable income based on active slabs for a tax year.
     */
    public static function computeTax(float $taxableIncome, int $taxYearId, string $taxpayerType = 'salaried'): float
    {
        if ($taxableIncome <= 0) {
            return 0.00;
        }

        // Find matching slab
        $slab = self::where('tax_year_id', $taxYearId)
            ->where('taxpayer_type', $taxpayerType)
            ->where('min_income', '<=', $taxableIncome)
            ->where(function ($q) use ($taxableIncome) {
                $q->whereNull('max_income')
                  ->orWhere('max_income', '>=', $taxableIncome);
            })
            ->first();

        if (! $slab) {
            return 0.00;
        }

        $exceedingAmount = max(0, $taxableIncome - (float) $slab->exceeding_threshold);
        $variableTax = ($exceedingAmount * (float) $slab->percentage_rate) / 100;
        $totalTax = (float) $slab->fixed_tax + $variableTax;

        return round($totalTax, 2);
    }
}

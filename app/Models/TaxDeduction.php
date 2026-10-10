<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_return_id',
        'deduction_type', // zakat, charity_sec61, pension_sec63, education_sec60d
        'iris_box_code',
        'institution_or_fund',
        'eligible_amount',
        'computed_credit_or_deduction',
    ];

    protected $casts = [
        'eligible_amount' => 'decimal:2',
        'computed_credit_or_deduction' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function ($item) {
            // By default Zakat is 100% direct deductible
            if ($item->deduction_type === 'zakat') {
                $item->computed_credit_or_deduction = $item->eligible_amount;
            } else {
                // Sec 61 / VPS tax credit estimation
                $item->computed_credit_or_deduction = round($item->eligible_amount * 0.15, 2);
            }
        });

        static::saved(function ($item) {
            $item->taxReturn?->recalculateTotals();
        });

        static::deleted(function ($item) {
            $item->taxReturn?->recalculateTotals();
        });
    }

    public function taxReturn(): BelongsTo
    {
        return $this->belongsTo(TaxReturn::class);
    }
}

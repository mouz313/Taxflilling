<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_return_id',
        'source_type',
        'iris_box_code',
        'employer_or_institution',
        'description',
        'gross_amount',
        'exempt_amount',
        'taxable_amount',
        'tax_deducted',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'exempt_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'tax_deducted' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Auto compute taxable_amount before saving
        static::saving(function ($item) {
            $gross = (float) ($item->gross_amount ?? 0);
            $exempt = (float) ($item->exempt_amount ?? 0);
            $item->taxable_amount = max(0, $gross - $exempt);
        });

        // Whenever an income item is saved or deleted, recalculate parent TaxReturn totals
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

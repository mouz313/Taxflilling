<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_return_id',
        'adjustment_type', // mobile, vehicle_token, electricity, bank_transaction, other
        'iris_box_code',
        'reference_no',
        'institution_name',
        'tax_amount',
    ];

    protected $casts = [
        'tax_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Whenever advance tax is added or updated, recalculate parent TaxReturn totals
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

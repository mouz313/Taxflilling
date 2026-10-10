<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WealthReconciliation extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_return_id',
        'opening_wealth',
        'net_inflows',
        'living_expenses',
        'other_outflows',
        'closing_wealth',
        'unreconciled_difference',
    ];

    protected $casts = [
        'opening_wealth' => 'decimal:2',
        'net_inflows' => 'decimal:2',
        'living_expenses' => 'decimal:2',
        'other_outflows' => 'decimal:2',
        'closing_wealth' => 'decimal:2',
        'unreconciled_difference' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Auto compute unreconciled difference:
        // Expected Closing Wealth = Opening Wealth + Net Inflows - Living Expenses - Other Outflows
        // Unreconciled Difference = Expected Closing Wealth - Actual Closing Wealth
        static::saving(function ($wealth) {
            $expected = (float) $wealth->opening_wealth + (float) $wealth->net_inflows - (float) $wealth->living_expenses - (float) $wealth->other_outflows;
            $actual = (float) $wealth->closing_wealth;
            $wealth->unreconciled_difference = round($expected - $actual, 2);
        });
    }

    public function taxReturn(): BelongsTo
    {
        return $this->belongsTo(TaxReturn::class);
    }

    public function isReconciled(): bool
    {
        return abs((float) $this->unreconciled_difference) < 1.00;
    }
}

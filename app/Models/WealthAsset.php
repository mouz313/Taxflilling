<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WealthAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_return_id',
        'asset_type', // property, vehicle, bank_account, gold, cash_in_hand, other
        'iris_box_code',
        'title',
        'details',
        'cost_value',
    ];

    protected $casts = [
        'cost_value' => 'decimal:2',
    ];

    public function taxReturn(): BelongsTo
    {
        return $this->belongsTo(TaxReturn::class);
    }
}

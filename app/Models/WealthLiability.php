<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WealthLiability extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_return_id',
        'liability_type', // bank_loan, personal_loan, mortgage, other
        'iris_box_code',
        'creditor_name',
        'outstanding_amount',
    ];

    protected $casts = [
        'outstanding_amount' => 'decimal:2',
    ];

    public function taxReturn(): BelongsTo
    {
        return $this->belongsTo(TaxReturn::class);
    }
}

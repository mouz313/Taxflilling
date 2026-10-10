<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'filing_deadline',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'filing_deadline' => 'date',
        'is_active' => 'boolean',
    ];

    public function taxSlabs(): HasMany
    {
        return $this->hasMany(TaxSlab::class);
    }

    public function taxReturns(): HasMany
    {
        return $this->hasMany(TaxReturn::class);
    }
}

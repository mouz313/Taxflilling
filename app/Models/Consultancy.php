<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consultancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'phone',
        'email',
        'address',
        'ntn_registration_no',
        'logo_path',
        'default_return_fee',
        'default_ntn_fee',
        'default_wealth_statement_fee',
        'is_active',
    ];

    protected $casts = [
        'default_return_fee' => 'decimal:2',
        'default_ntn_fee' => 'decimal:2',
        'default_wealth_statement_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function taxReturns(): HasMany
    {
        return $this->hasMany(TaxReturn::class);
    }
}

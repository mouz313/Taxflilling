<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function services(): HasMany
    {
        return $this->hasMany(BusinessServiceItem::class, 'service_category_id')->orderBy('sort_order');
    }

    public function activeServices(): HasMany
    {
        return $this->hasMany(BusinessServiceItem::class, 'service_category_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessServiceItem extends Model
{
    use HasFactory;

    protected $table = 'business_services';

    protected $fillable = [
        'code',
        'category',
        'category_name',
        'heading',
        'urdu',
        'icon',
        'fee',
        'currency',
        'turnaround',
        'popular',
        'is_active',
        'desc',
        'requirements',
        'form_fields',
        'sort_order',
    ];

    protected $casts = [
        'fee' => 'decimal:2',
        'popular' => 'boolean',
        'is_active' => 'boolean',
        'requirements' => 'array',
        'form_fields' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Category Display Map
     */
    public static function getCategories(): array
    {
        return [
            'income' => [
                'name' => 'Income Tax Return',
                'icon' => '💼',
            ],
            'sales' => [
                'name' => 'Sales Tax Registration',
                'icon' => '📜',
            ],
            'company' => [
                'name' => 'Company\'s Registration',
                'icon' => '🏢',
            ],
            'intellectual' => [
                'name' => 'Intellectual Property',
                'icon' => '®️',
            ],
            'llc' => [
                'name' => 'USA LLC & Tax filing',
                'icon' => '🇺🇸',
            ],
        ];
    }
}

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
        'service_category_id',
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

    public function categoryRelation()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'business_service_id');
    }

    /**
     * Category Display Map (Dynamic fallback)
     */
    public static function getCategories(): array
    {
        $dynamic = ServiceCategory::where('is_active', true)->orderBy('sort_order')->get();
        if ($dynamic->isNotEmpty()) {
            $mapped = [];
            foreach ($dynamic as $cat) {
                $mapped[$cat->slug] = [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'icon' => $cat->icon,
                ];
            }
            return $mapped;
        }

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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $table = 'faqs';

    protected $fillable = [
        'category',
        'question',
        'urdu_question',
        'answer',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public static function getCategories(): array
    {
        return [
            'all' => 'All Topics',
            'filer_benefits' => '⚡ Filer vs Non-Filer',
            'salaried' => '💼 Salaried Individuals',
            'business_ntn' => '🏢 NTN & Company Setup',
            'wealth' => '💰 Wealth Reconciliation',
        ];
    }
}

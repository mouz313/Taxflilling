<?php

namespace App\Models;

use App\Traits\BelongsToConsultancy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallbackRequest extends Model
{
    use HasFactory, BelongsToConsultancy;

    protected $fillable = [
        'consultancy_id',
        'name',
        'phone',
        'email',
        'service_code',
        'service_name',
        'preferred_time',
        'notes',
        'status',
        'handled_by',
        'admin_response_notes',
    ];

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}

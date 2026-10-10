<?php

namespace App\Models;

use App\Traits\BelongsToConsultancy;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory, BelongsToConsultancy;

    protected $fillable = [
        'consultancy_id',
        'user_id',
        'name',
        'cnic',
        'cnic_hash',
        'phone',
        'email',
        'ntn',
        'father_name',
        'address',
        'status',
        'internal_notes',
    ];

    protected static function booted()
    {
        static::saving(function ($client) {
            if (!empty($client->cnic) && empty($client->cnic_hash)) {
                $client->cnic_hash = self::generateCnicHash($client->cnic);
            }
        });
    }

    /**
     * Safe Encryption & Decryption for CNIC.
     * Prevents "Payload is invalid" DecryptException if record was previously stored unencrypted.
     */
    protected function cnic(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (empty($value)) {
                    return $value;
                }
                try {
                    return decrypt($value);
                } catch (DecryptException $e) {
                    // Safe fallback if existing record has plain text CNIC
                    return $value;
                }
            },
            set: function ($value) {
                $this->attributes['cnic_hash'] = self::generateCnicHash($value);
                return !empty($value) ? encrypt($value) : null;
            }
        );
    }

    /**
     * Generate HMAC blind index for CNIC.
     * Uses application key as HMAC secret so rainbow tables cannot reverse it.
     */
    public static function generateCnicHash(?string $cnic): ?string
    {
        if (empty($cnic)) {
            return null;
        }

        // Clean dashes and spaces: 42101-1234567-1 -> 4210112345671
        $normalized = preg_replace('/[^0-9]/', '', $cnic);
        return hash_hmac('sha256', $normalized, config('app.key'));
    }

    /**
     * User relationship (for portal login)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tax Returns
     */
    public function taxReturns(): HasMany
    {
        return $this->hasMany(TaxReturn::class);
    }

    /**
     * Documents
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Search scope across client fields
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        $term = trim($term);
        $cnicSearchHash = self::generateCnicHash($term);

        return $query->where(function ($q) use ($term, $cnicSearchHash) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('ntn', 'like', "%{$term}%");

            if ($cnicSearchHash) {
                $q->orWhere('cnic_hash', $cnicSearchHash);
            }
        });
    }
}

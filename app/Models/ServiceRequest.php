<?php

namespace App\Models;

use App\Traits\BelongsToConsultancy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRequest extends Model
{
    use HasFactory, BelongsToConsultancy;

    protected $fillable = [
        'consultancy_id',
        'tracking_number',
        'user_id',
        'client_id',
        'business_service_id',
        'applicant_name',
        'applicant_phone',
        'applicant_email',
        'applicant_cnic',
        'service_code',
        'service_name',
        'filer_type',
        'fee_amount',
        'payment_status',
        'payment_method',
        'payment_reference',
        'payment_proof_path',
        'status',
        'data_payload',
        'uploaded_files',
        'delivered_file_path',
        'delivered_reference_no',
        'admin_notes',
    ];

    protected $appends = [
        'fbr_snapshot',
    ];

    protected $casts = [
        'fee_amount' => 'decimal:2',
        'data_payload' => 'array',
        'uploaded_files' => 'array',
    ];

    public function businessService(): BelongsTo
    {
        return $this->belongsTo(BusinessServiceItem::class, 'business_service_id');
    }

    /**
     * Accessor for fbr_snapshot attribute
     */
    public function getFbrSnapshotAttribute(): array
    {
        return $this->getFbrIrisSnapshot();
    }

    /**
     * Retrieve or generate the FBR IRIS verification snapshot attached to this service request.
     */
    public function getFbrIrisSnapshot(): array
    {
        $payload = is_array($this->data_payload) ? $this->data_payload : [];
        if (!empty($payload['_fbr_iris_snapshot'])) {
            return $payload['_fbr_iris_snapshot'];
        }

        $rawIdentifier = $payload['applicant_cnic']
            ?? $payload['cnic']
            ?? $payload['ntn']
            ?? ($this->client?->cnic ?? null)
            ?? ($this->client?->ntn ?? null)
            ?? '42101-1234567-1';

        $fbrService = app(\App\Services\FbrVerificationService::class);
        $atl = $fbrService->verifyAtl($rawIdentifier);
        $profile = $fbrService->verifyNtn($rawIdentifier);

        return [
            'fetched_at' => now()->format('d M Y, h:i A'),
            'identifier' => $rawIdentifier,
            'atl' => $atl,
            'profile' => $profile,
            'auto_synced' => true,
        ];
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->tracking_number)) {
                $model->tracking_number = static::generateAlphanumericTrackingNumber();
            }
        });
    }

    /**
     * Generate unique 9-character alphanumeric tracking / application number (e.g. 8K2M9PX7Q)
     */
    public static function generateAlphanumericTrackingNumber(): string
    {
        $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        do {
            $number = '';
            for ($i = 0; $i < 9; $i++) {
                $number .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (static::where('tracking_number', $number)->exists());

        return $number;
    }

    /**
     * Master Catalog of Services (Dynamic with CMS, with fallback for unseeded environments)
     */
    public static function getCatalog(): array
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('business_services')) {
                $items = \App\Models\BusinessServiceItem::where('is_active', true)->orderBy('sort_order')->get();
                if ($items->isNotEmpty()) {
                    $catalog = [];
                    foreach ($items as $item) {
                        $catalog[$item->code] = [
                            'id' => $item->id,
                            'code' => $item->code,
                            'category' => $item->category,
                            'category_name' => $item->category_name,
                            'heading' => $item->heading,
                            'urdu' => $item->urdu,
                            'icon' => $item->icon,
                            'fee' => (float) $item->fee,
                            'currency' => $item->currency,
                            'turnaround' => $item->turnaround,
                            'popular' => (bool) $item->popular,
                            'desc' => $item->desc,
                            'requirements' => $item->requirements ?? [],
                            'form_fields' => $item->form_fields ?? [],
                        ];
                    }
                    return $catalog;
                }
            }
        } catch (\Throwable $e) {
            // fallback to default below
        }

        return [
            'ntn_individual' => [
                'code' => 'ntn_individual',
                'category' => 'income',
                'category_name' => 'Income Tax Return',
                'heading' => 'Individual NTN Registration',
                'urdu' => 'Zati Shanakhti NTN Registration',
                'icon' => '🆔',
                'fee' => 1500.00,
                'currency' => 'Rs.',
                'turnaround' => '1 - 2 Working Days',
                'popular' => true,
                'desc' => 'Salaried individuals aur general citizens ke liye FBR se naya NTN number generate karwana.',
                'requirements' => [
                    'Color copy of CNIC (Front & Back)',
                    'Latest paid electricity bill (premises copy)',
                    'Phone number registered on applicant\'s CNIC',
                    'Active email address for FBR PIN verification',
                ],
                'form_fields' => [
                    ['name' => 'full_name', 'label' => 'Full Name (As per CNIC)', 'type' => 'text', 'required' => true],
                    ['name' => 'cnic', 'label' => 'CNIC Number', 'type' => 'text', 'placeholder' => '42101-1234567-1', 'required' => true],
                    ['name' => 'mobile', 'label' => 'Active Mobile No (On your CNIC)', 'type' => 'text', 'placeholder' => '03001234567', 'required' => true],
                    ['name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'required' => true],
                    ['name' => 'electricity_consumer_no', 'label' => 'Electricity Bill Consumer No', 'type' => 'text', 'required' => true],
                    ['name' => 'residential_address', 'label' => 'Current Residential Address', 'type' => 'textarea', 'required' => true],
                ],
            ],

            'ntn_business' => [
                'code' => 'ntn_business',
                'category' => 'income',
                'category_name' => 'Income Tax Return',
                'heading' => 'Business NTN Registration (Sole Proprietor)',
                'urdu' => 'Karobari NTN Shamil Karna',
                'icon' => '🏪',
                'fee' => 2500.00,
                'currency' => 'Rs.',
                'turnaround' => '1 - 2 Working Days',
                'popular' => true,
                'desc' => 'Apne existing NTN par dukan ya company ka business name add karwana commercial bank account ke liye.',
                'requirements' => [
                    'Color copy of CNIC',
                    'Rent agreement or property ownership documents',
                    'Commercial electricity bill of business premises',
                    'Business letterhead & rubber stamp',
                ],
                'form_fields' => [
                    ['name' => 'business_name', 'label' => 'Registered Business Name', 'type' => 'text', 'required' => true],
                    ['name' => 'business_address', 'label' => 'Commercial Office / Shop Address', 'type' => 'textarea', 'required' => true],
                    ['name' => 'electricity_consumer_no', 'label' => 'Commercial Bill Consumer No', 'type' => 'text', 'required' => true],
                ],
            ],

            'secp_pvt_ltd' => [
                'code' => 'secp_pvt_ltd',
                'category' => 'company',
                'category_name' => 'Company\'s Registration',
                'heading' => 'Private Limited Company Registration (SECP)',
                'urdu' => 'SECP Private Limited Company Registration',
                'icon' => '🏢',
                'fee' => 25000.00,
                'currency' => 'Rs.',
                'turnaround' => '3 - 7 Working Days',
                'popular' => true,
                'desc' => 'Name reservation, Digital Signatures, Memorandum & Articles of Association, aur Incorporation Certificate.',
                'requirements' => [
                    '3 Proposed company names in order of preference',
                    'CNIC copies of minimum 2 directors / shareholders',
                    'Valid mobile numbers and emails of all directors',
                    'Registered office address of the company',
                ],
                'form_fields' => [
                    ['name' => 'name_option_1', 'label' => 'Proposed Name (1st Choice)', 'type' => 'text', 'required' => true],
                    ['name' => 'name_option_2', 'label' => 'Proposed Name (2nd Choice)', 'type' => 'text', 'required' => true],
                    ['name' => 'name_option_3', 'label' => 'Proposed Name (3rd Choice)', 'type' => 'text', 'required' => true],
                    ['name' => 'principal_business', 'label' => 'Principal Business Activity', 'type' => 'text', 'required' => true],
                    ['name' => 'directors_count', 'label' => 'Number of Directors (Min 2)', 'type' => 'number', 'required' => true],
                    ['name' => 'paid_up_capital', 'label' => 'Authorized Capital (PKR)', 'type' => 'number', 'placeholder' => 'e.g. 100000', 'required' => true],
                ],
            ],

            'usa_llc_formation' => [
                'code' => 'usa_llc_formation',
                'category' => 'llc',
                'category_name' => 'USA LLC & Tax filing',
                'heading' => 'Company Formation in USA (LLC - Wyoming / Delaware)',
                'urdu' => 'America (USA) Mein Company Registration',
                'icon' => '🇺🇸',
                'fee' => 75000.00,
                'currency' => 'Rs.',
                'turnaround' => '3 - 5 Business Days',
                'popular' => true,
                'desc' => 'Wyoming ya Delaware LLC registration, Articles of Organization, Registered Agent 1 year, aur Certificate of Good Standing.',
                'requirements' => [
                    'Valid International Passport copy (Color scan)',
                    'Proof of address (English bank statement or utility bill)',
                    '3 Desired LLC names with "LLC" suffix',
                ],
                'form_fields' => [
                    ['name' => 'desired_llc_name_1', 'label' => 'Desired LLC Name (1st Choice)', 'type' => 'text', 'placeholder' => 'e.g. Apex Tech Solutions LLC', 'required' => true],
                    ['name' => 'desired_llc_name_2', 'label' => 'Desired LLC Name (2nd Choice)', 'type' => 'text', 'required' => true],
                    ['name' => 'state_choice', 'label' => 'State of Formation', 'type' => 'select', 'options' => ['Wyoming (Best for Privacy & Zero State Income Tax)', 'Delaware (Best for Tech Startups)', 'New Mexico'], 'required' => true],
                    ['name' => 'industry_nature', 'label' => 'Nature of Business / E-Commerce / SaaS', 'type' => 'text', 'required' => true],
                    ['name' => 'owner_passport_no', 'label' => 'Passport Number', 'type' => 'text', 'required' => true],
                ],
            ],
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}

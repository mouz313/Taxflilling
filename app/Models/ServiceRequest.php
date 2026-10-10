<?php

namespace App\Models;

use App\Traits\BelongsToConsultancy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ServiceRequest extends Model
{
    use HasFactory, BelongsToConsultancy;

    protected $fillable = [
        'consultancy_id',
        'tracking_number',
        'user_id',
        'client_id',
        'applicant_name',
        'applicant_phone',
        'applicant_email',
        'service_code',
        'service_name',
        'filer_type',
        'fee_amount',
        'payment_status',
        'payment_method',
        'payment_reference',
        'status',
        'data_payload',
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
    ];

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
     * Master Catalog of All 5 Categories Matching Befiler Business Services (Dynamic with CMS)
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
            // CATEGORY 1: INCOME TAX RETURN & NTN
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
                    ['name' => 'business_name', 'label' => 'Business / Trade Name', 'type' => 'text', 'required' => true],
                    ['name' => 'nature_of_business', 'label' => 'Nature of Business (Retail/Services/Trade)', 'type' => 'text', 'required' => true],
                    ['name' => 'business_address', 'label' => 'Business Premises Address', 'type' => 'textarea', 'required' => true],
                    ['name' => 'electricity_consumer_no', 'label' => 'Business Electricity Bill Consumer #', 'type' => 'text', 'required' => true],
                    ['name' => 'premises_status', 'label' => 'Premises Status', 'type' => 'select', 'options' => ['Rented', 'Owned', 'Shared'], 'required' => true],
                ],
            ],

            'annual_salaried_return' => [
                'code' => 'annual_salaried_return',
                'category' => 'income',
                'category_name' => 'Income Tax Return',
                'heading' => 'Annual Income Tax Return (Salaried)',
                'urdu' => 'Tankhwah Daar Afraad Ka Mahana Return',
                'icon' => '💼',
                'fee' => 3000.00,
                'currency' => 'Rs.',
                'turnaround' => '24 - 48 Hours',
                'popular' => true,
                'desc' => 'Salary slips, bank withholding certificates, aur FBR 114(1) Active Filer status CPR filing.',
                'requirements' => [
                    'Annual salary certificate / slips for July-June',
                    'Bank tax deduction certificate (Section 231A/231AA)',
                    'FBR login PIN/Password (or we can recover it for you)',
                    'Details of vehicle / property acquired in tax year',
                ],
                'form_fields' => [
                    ['name' => 'employer_name', 'label' => 'Employer / Organization Name', 'type' => 'text', 'required' => true],
                    ['name' => 'annual_gross_salary', 'label' => 'Annual Gross Salary (PKR)', 'type' => 'number', 'required' => true],
                    ['name' => 'tax_deducted_by_employer', 'label' => 'Tax Deducted by Employer (PKR)', 'type' => 'number', 'required' => false],
                    ['name' => 'bank_accounts_count', 'label' => 'Number of Active Bank Accounts', 'type' => 'number', 'required' => true],
                    ['name' => 'has_fbr_password', 'label' => 'Do you have FBR IRIS Password & PIN?', 'type' => 'select', 'options' => ['Yes', 'No (Need Recovery)'], 'required' => true],
                ],
            ],

            'annual_business_return' => [
                'code' => 'annual_business_return',
                'category' => 'income',
                'category_name' => 'Income Tax Return',
                'heading' => 'Annual Income Tax Filing (Sole Proprietor)',
                'urdu' => 'Karobar & Online Freelancers Ka Return',
                'icon' => '📈',
                'fee' => 5000.00,
                'currency' => 'Rs.',
                'turnaround' => '3 - 5 Working Days',
                'popular' => false,
                'desc' => 'Traders, wholesalers, consultants, aur freelancers ke liye complete P&L aur wealth reconciliation.',
                'requirements' => [
                    'Bank statements for July to June period',
                    'Total annual sales / revenue turnover',
                    'Business expenses summary (Rent, utilities, salaries)',
                    'Opening & Closing stock details (if applicable)',
                ],
                'form_fields' => [
                    ['name' => 'business_name', 'label' => 'Business Name', 'type' => 'text', 'required' => true],
                    ['name' => 'annual_turnover', 'label' => 'Estimated Annual Turnover / Sales (PKR)', 'type' => 'number', 'required' => true],
                    ['name' => 'net_profit', 'label' => 'Estimated Net Profit (PKR)', 'type' => 'number', 'required' => true],
                    ['name' => 'fbr_ntn', 'label' => 'NTN / CNIC Number', 'type' => 'text', 'required' => true],
                ],
            ],

            // CATEGORY 2: SALES TAX REGISTRATION
            'gst_registration' => [
                'code' => 'gst_registration',
                'category' => 'sales',
                'category_name' => 'Sales Tax Registration',
                'heading' => 'GST Registration (Federal FBR STRN)',
                'urdu' => 'General Sales Tax (FBR STRN) Registration',
                'icon' => '📜',
                'fee' => 15000.00,
                'currency' => 'Rs.',
                'turnaround' => '1 - 2 Weeks',
                'popular' => true,
                'desc' => 'Goods manufacturers, importers, exporters, aur distributors ke liye FBR Sales Tax Registration Number.',
                'requirements' => [
                    'Bank account maintenance certificate with IBAN',
                    'Biometric verification on FBR mobile app',
                    'Business premises photos (inside, outside, signboard, electric meter)',
                    'Utility bills of premises under business name',
                ],
                'form_fields' => [
                    ['name' => 'business_name', 'label' => 'Business Name', 'type' => 'text', 'required' => true],
                    ['name' => 'ntn', 'label' => 'Existing NTN Number', 'type' => 'text', 'required' => true],
                    ['name' => 'business_activity', 'label' => 'Business Activity (Manufacturing / Import / Retail)', 'type' => 'text', 'required' => true],
                    ['name' => 'bank_name', 'label' => 'Business Bank Name', 'type' => 'text', 'required' => true],
                    ['name' => 'iban', 'label' => '24-Digit IBAN Number', 'type' => 'text', 'required' => true],
                ],
            ],

            'pst_registration_provincial' => [
                'code' => 'pst_registration_provincial',
                'category' => 'sales',
                'category_name' => 'Sales Tax Registration',
                'heading' => 'Provincial Sales Tax (SRB / PRA / KPRA / BRA)',
                'urdu' => 'Subai Sales Tax (Services) Registration',
                'icon' => '🏛️',
                'fee' => 12000.00,
                'currency' => 'Rs.',
                'turnaround' => '5 - 7 Working Days',
                'popular' => false,
                'desc' => 'IT services, software houses, restaurants, aur consultants ke liye provincial sales tax registration.',
                'requirements' => [
                    'NTN Certificate copy',
                    'CNIC copies of owners / directors',
                    'Office rental agreement and utility bills',
                    'List of services provided',
                ],
                'form_fields' => [
                    ['name' => 'province_authority', 'label' => 'Select Revenue Authority', 'type' => 'select', 'options' => ['SRB (Sindh)', 'PRA (Punjab)', 'KPRA (Khyber Pakhtunkhwa)', 'BRA (Balochistan)'], 'required' => true],
                    ['name' => 'business_name', 'label' => 'Business Name', 'type' => 'text', 'required' => true],
                    ['name' => 'service_nature', 'label' => 'Nature of Services Rendered', 'type' => 'text', 'required' => true],
                ],
            ],

            'monthly_sales_tax_filing' => [
                'code' => 'monthly_sales_tax_filing',
                'category' => 'sales',
                'category_name' => 'Sales Tax Registration',
                'heading' => 'Monthly Sales Tax Return Filing',
                'urdu' => 'Mahana Sales Tax Return (Annex-C)',
                'icon' => '📊',
                'fee' => 5000.00,
                'currency' => 'Rs.',
                'turnaround' => '1 - 2 Working Days (By 18th of month)',
                'popular' => false,
                'desc' => 'Har mahine ki 18 tareekh se pehle Annex-C sales invoices upload karna aur monthly return submit karna.',
                'requirements' => [
                    'Sales invoices summary for the month',
                    'Purchase invoices from registered suppliers',
                    'Import GD copies (if applicable)',
                ],
                'form_fields' => [
                    ['name' => 'business_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true],
                    ['name' => 'strn_number', 'label' => 'STRN / Sales Tax Number', 'type' => 'text', 'required' => true],
                    ['name' => 'tax_month', 'label' => 'Filing Month & Year', 'type' => 'text', 'placeholder' => 'e.g. October 2024', 'required' => true],
                    ['name' => 'sales_amount', 'label' => 'Total Taxable Sales This Month (PKR)', 'type' => 'number', 'required' => true],
                ],
            ],

            // CATEGORY 3: COMPANY'S REGISTRATION (SECP & CORPORATE)
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

            'secp_smc_ltd' => [
                'code' => 'secp_smc_ltd',
                'category' => 'company',
                'category_name' => 'Company\'s Registration',
                'heading' => 'Single Member Company (SMC-Pvt Ltd)',
                'urdu' => 'Single Member Company (1 Director)',
                'icon' => '👤',
                'fee' => 22000.00,
                'currency' => 'Rs.',
                'turnaround' => '3 - 7 Working Days',
                'popular' => false,
                'desc' => 'Sirf ek fard (sole director) ke liye limited liability corporate protection aur SECP license.',
                'requirements' => [
                    '3 Proposed company names',
                    'CNIC of Single Member',
                    'Nominee director details (legal requirement)',
                    'Registered address proof',
                ],
                'form_fields' => [
                    ['name' => 'company_name', 'label' => 'Desired Company Name', 'type' => 'text', 'required' => true],
                    ['name' => 'principal_business', 'label' => 'Business Sector', 'type' => 'text', 'required' => true],
                    ['name' => 'nominee_director_name', 'label' => 'Nominee Director Name (Close Family Member)', 'type' => 'text', 'required' => true],
                ],
            ],

            'partnership_aop_form_c' => [
                'code' => 'partnership_aop_form_c',
                'category' => 'company',
                'category_name' => 'Company\'s Registration',
                'heading' => 'Partnership / AOP Registration (Form C)',
                'urdu' => 'Partnership Deed & Form C Registration',
                'icon' => '🤝',
                'fee' => 20000.00,
                'currency' => 'Rs.',
                'turnaround' => '7 - 14 Working Days',
                'popular' => false,
                'desc' => 'Partnership Deed drafting on stamp paper, Registrar of Firms physical verification aur Form C certificate.',
                'requirements' => [
                    'CNIC copies of all partners',
                    'Partnership deed on stamp paper (we draft it for you)',
                    'Rent agreement and rent receipt of office',
                    'Challan fee receipt paid in National Bank',
                ],
                'form_fields' => [
                    ['name' => 'firm_name', 'label' => 'Partnership Firm Name', 'type' => 'text', 'required' => true],
                    ['name' => 'partners_count', 'label' => 'Number of Partners', 'type' => 'number', 'required' => true],
                    ['name' => 'business_address', 'label' => 'Office Address', 'type' => 'textarea', 'required' => true],
                ],
            ],

            'pseb_registration' => [
                'code' => 'pseb_registration',
                'category' => 'company',
                'category_name' => 'Company\'s Registration',
                'heading' => 'PSEB Registration (IT Freelancer / Company)',
                'urdu' => 'Pakistan Software Export Board (PSEB) License',
                'icon' => '💻',
                'fee' => 8000.00,
                'currency' => 'Rs.',
                'turnaround' => '3 - 5 Working Days',
                'popular' => true,
                'desc' => 'IT software exporters aur freelancing remittances par 100% tax exemption / 0.25% concession ke liye PSEB registration.',
                'requirements' => [
                    'NTN Certificate and active filer status',
                    'Business bank account maintenance certificate',
                    'Foreign inward remittance certificates (PRCs)',
                    'Resume / portfolio of IT services',
                ],
                'form_fields' => [
                    ['name' => 'applicant_type', 'label' => 'Applicant Type', 'type' => 'select', 'options' => ['IT Freelancer / Individual', 'Software House / Company', 'Call Center'], 'required' => true],
                    ['name' => 'nature_of_it_services', 'label' => 'IT Services (Web / Mobile / AI / BPO)', 'type' => 'text', 'required' => true],
                    ['name' => 'bank_name', 'label' => 'Receiving Bank Name', 'type' => 'text', 'required' => true],
                ],
            ],

            // CATEGORY 4: INTELLECTUAL PROPERTY
            'trademark_registration' => [
                'code' => 'trademark_registration',
                'category' => 'intellectual',
                'category_name' => 'Intellectual Property',
                'heading' => 'Trademark / Brand Logo Registration (IPO)',
                'urdu' => 'Brand Name / Logo Legal Registration (IPO Pakistan)',
                'icon' => '®️',
                'fee' => 35000.00,
                'currency' => 'Rs.',
                'turnaround' => '1 - 2 Months (Journal Publication)',
                'popular' => true,
                'desc' => 'Apne brand name aur logo ko chori hone se bachane ke liye IPO Pakistan mein Form TM-1 filing aur trademark certificate.',
                'requirements' => [
                    'High-resolution image of brand logo',
                    'CNIC of applicant / Certificate of Incorporation',
                    'List of goods / services and business sector',
                    'Date of first use of the trademark',
                ],
                'form_fields' => [
                    ['name' => 'brand_name', 'label' => 'Brand / Word Name to Protect', 'type' => 'text', 'required' => true],
                    ['name' => 'trademark_type', 'label' => 'Type of Mark', 'type' => 'select', 'options' => ['Wordmark Only', 'Logo + Wordmark', 'Logo Device Only'], 'required' => true],
                    ['name' => 'goods_description', 'label' => 'Products or Services Sold Under This Brand', 'type' => 'textarea', 'required' => true],
                    ['name' => 'date_of_first_use', 'label' => 'Date of First Use in Pakistan', 'type' => 'text', 'placeholder' => 'e.g. 01/01/2023 or Proposed to be used', 'required' => true],
                ],
            ],

            'copyright_registration' => [
                'code' => 'copyright_registration',
                'category' => 'intellectual',
                'category_name' => 'Intellectual Property',
                'heading' => 'Copyright Registration',
                'urdu' => 'Software Code / Content Copyright (IPO)',
                'icon' => '©️',
                'fee' => 25000.00,
                'currency' => 'Rs.',
                'turnaround' => '2 - 3 Weeks',
                'popular' => false,
                'desc' => 'Software source code, artistic work, books, aur songs ke legal rights protect karwana.',
                'requirements' => [
                    '2 Copies of the creative work / source code',
                    'No-objection certificate (NOC) if work created by employee',
                    'Applicant CNIC copy',
                ],
                'form_fields' => [
                    ['name' => 'work_title', 'label' => 'Title of Creative Work', 'type' => 'text', 'required' => true],
                    ['name' => 'work_category', 'label' => 'Category', 'type' => 'select', 'options' => ['Software Source Code', 'Artistic / Graphic Design', 'Literary / Book', 'Musical Work'], 'required' => true],
                ],
            ],

            // CATEGORY 5: USA LLC & TAX FILING
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

            'usa_ein_number' => [
                'code' => 'usa_ein_number',
                'category' => 'llc',
                'category_name' => 'USA LLC & Tax filing',
                'heading' => 'Employer Identification Number (IRS EIN)',
                'urdu' => 'US Federal Tax ID (IRS EIN Confirmation Letter)',
                'icon' => '🦅',
                'fee' => 30000.00,
                'currency' => 'Rs.',
                'turnaround' => '4 - 10 Business Days',
                'popular' => false,
                'desc' => 'US Internal Revenue Service (IRS) se official EIN letter (Form CP 575) Stripe aur US bank account activate karne ke liye.',
                'requirements' => [
                    'Approved Articles of Organization of your US LLC',
                    'Passport copy of the Responsible Party',
                    'SS-4 form signing authority',
                ],
                'form_fields' => [
                    ['name' => 'llc_name', 'label' => 'Registered LLC Name', 'type' => 'text', 'required' => true],
                    ['name' => 'state_registered', 'label' => 'State of Registration', 'type' => 'text', 'required' => true],
                    ['name' => 'responsible_party_name', 'label' => 'Responsible Party Full Name', 'type' => 'text', 'required' => true],
                ],
            ],

            'usa_bank_account' => [
                'code' => 'usa_bank_account',
                'category' => 'llc',
                'category_name' => 'USA LLC & Tax filing',
                'heading' => 'Open A US Business Bank Account (Mercury / Relay)',
                'urdu' => 'American Business Bank Account Setup',
                'icon' => '💳',
                'fee' => 25000.00,
                'currency' => 'Rs.',
                'turnaround' => '3 - 5 Business Days',
                'popular' => true,
                'desc' => 'FDIC insured US business checking account setup with Mercury, RelayFi ya Wise Business with virtual debit cards.',
                'requirements' => [
                    'IRS EIN Letter (CP 575)',
                    'Approved LLC Certificate & Operating Agreement',
                    'Live company website / active LinkedIn profile',
                    'Valid International Passport',
                ],
                'form_fields' => [
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true],
                    ['name' => 'company_website', 'label' => 'Company Website / Domain URL', 'type' => 'text', 'placeholder' => 'https://yourbrand.com', 'required' => true],
                    ['name' => 'preferred_bank', 'label' => 'Preferred US Bank', 'type' => 'select', 'options' => ['Mercury Bank', 'Relay Financial', 'Wise Business'], 'required' => true],
                ],
            ],

            'usa_tax_filing' => [
                'code' => 'usa_tax_filing',
                'category' => 'llc',
                'category_name' => 'USA LLC & Tax filing',
                'heading' => 'US LLC Federal & State Annual Tax Filing',
                'urdu' => 'IRS Annual Tax Return (Form 1120/5472/1065)',
                'icon' => '📑',
                'fee' => 45000.00,
                'currency' => 'Rs.',
                'turnaround' => '5 - 7 Business Days',
                'popular' => false,
                'desc' => 'Non-resident US LLCs ke liye mandatory IRS Form 5472 & Form 1120 filing penalty ($25,000) se bachane ke liye.',
                'requirements' => [
                    'US bank statements for the calendar year',
                    'Total sales revenue and US expenses',
                    'EIN number and LLC formation documents',
                ],
                'form_fields' => [
                    ['name' => 'llc_name', 'label' => 'LLC Name', 'type' => 'text', 'required' => true],
                    ['name' => 'ein_number', 'label' => 'IRS EIN Number', 'type' => 'text', 'required' => true],
                    ['name' => 'tax_year', 'label' => 'Tax Year to File', 'type' => 'text', 'placeholder' => 'e.g. 2024', 'required' => true],
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

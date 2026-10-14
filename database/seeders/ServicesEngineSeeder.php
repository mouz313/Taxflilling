<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\BusinessServiceItem;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServicesEngineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Categories
        $categories = [
            [
                'name' => 'NTN Registration',
                'slug' => 'ntn-registration',
                'icon' => '🆔',
                'description' => 'FBR National Tax Number registration for Individuals, Salaried persons, and Business enterprises.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Income Tax Return',
                'slug' => 'income-tax-return',
                'icon' => '💼',
                'description' => 'Annual FBR IRIS income tax returns filing for Salaried individuals, Freelancers, and Businesses.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Sales Tax Registration',
                'slug' => 'sales-tax-registration',
                'icon' => '📜',
                'description' => 'Sales Tax (STRN) registration with FBR, PRA, SRB, BRA, and KPRA.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Company Registration',
                'slug' => 'company-registration',
                'icon' => '🏢',
                'description' => 'SECP Private Limited (Pvt Ltd), Single Member Company (SMC), and Partnership (AOP) incorporation.',
                'sort_order' => 4,
            ],
            [
                'name' => 'Intellectual Property',
                'slug' => 'intellectual-property',
                'icon' => '®️',
                'description' => 'Trademark, Copyright, and Logo brand registration with IPO Pakistan.',
                'sort_order' => 5,
            ],
            [
                'name' => 'USA LLC & Global',
                'slug' => 'usa-llc-global',
                'icon' => '🇺🇸',
                'description' => 'US Company formation (Wyoming/Delaware/New Mexico), EIN, ITIN, and US Bank Accounts.',
                'sort_order' => 6,
            ],
        ];

        $categoryModels = [];
        foreach ($categories as $cat) {
            $categoryModels[$cat['slug']] = ServiceCategory::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }

        // 2. Seed Services with Dynamic Field Types (Files & Text)
        $services = [
            [
                'code' => 'ntn_individual',
                'service_category_id' => $categoryModels['ntn-registration']->id,
                'category' => 'income',
                'category_name' => 'NTN Registration',
                'heading' => 'Individual NTN Registration',
                'urdu' => 'Zati / Salaried NTN Registration',
                'icon' => '🆔',
                'fee' => 1500.00,
                'currency' => 'Rs.',
                'turnaround' => '1 - 2 Working Days',
                'popular' => true,
                'is_active' => true,
                'desc' => 'FBR IRIS par naya National Tax Number (NTN) banwayen aur official FBR 181 Registration Certificate hasil karein.',
                'requirements' => [
                    [
                        'label' => 'Color copy of CNIC',
                        'type' => 'file',
                        'file_types' => ['jpg', 'jpeg', 'png', 'pdf'],
                        'required' => true,
                    ],
                    [
                        'label' => 'Latest paid electricity bill',
                        'type' => 'file',
                        'file_types' => ['jpg', 'jpeg', 'png', 'pdf'],
                        'required' => true,
                    ],
                    [
                        'label' => 'Phone Number (Registered on CNIC)',
                        'type' => 'text',
                        'required' => true,
                    ],
                    [
                        'label' => 'Email address',
                        'type' => 'text',
                        'required' => true,
                    ],
                ],
                'sort_order' => 1,
            ],
            [
                'code' => 'ntn_business',
                'service_category_id' => $categoryModels['ntn-registration']->id,
                'category' => 'company',
                'category_name' => 'NTN Registration',
                'heading' => 'NTN Registration – Business',
                'urdu' => 'Karobari Sole Proprietorship NTN',
                'icon' => '🏢',
                'fee' => 2500.00,
                'currency' => 'Rs.',
                'turnaround' => '1 - 2 Working Days',
                'popular' => true,
                'is_active' => true,
                'desc' => 'Sole Proprietorship business name FBR par register karein aur commercial business bank account kholne ke liye certificate hasil karein.',
                'requirements' => [
                    [
                        'label' => 'Color copy of CNIC',
                        'type' => 'file',
                        'file_types' => ['jpg', 'jpeg', 'png', 'pdf'],
                        'required' => true,
                    ],
                    [
                        'label' => 'Rent agreement or ownership docs of Office premises',
                        'type' => 'file',
                        'file_types' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
                        'required' => true,
                    ],
                    [
                        'label' => 'Business Letterhead copy',
                        'type' => 'file',
                        'file_types' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
                        'required' => true,
                    ],
                    [
                        'label' => 'Latest paid electricity bill of premises',
                        'type' => 'file',
                        'file_types' => ['jpg', 'jpeg', 'png', 'pdf'],
                        'required' => true,
                    ],
                    [
                        'label' => 'Phone Number (Active for FBR OTP)',
                        'type' => 'text',
                        'required' => true,
                    ],
                    [
                        'label' => 'Email address',
                        'type' => 'text',
                        'required' => true,
                    ],
                    [
                        'label' => 'Proposed Business Name',
                        'type' => 'text',
                        'required' => true,
                    ],
                ],
                'sort_order' => 2,
            ],
            [
                'code' => 'tax_return_salaried',
                'service_category_id' => $categoryModels['income-tax-return']->id,
                'category' => 'income',
                'category_name' => 'Income Tax Return',
                'heading' => 'Salaried Tax Return Filing',
                'urdu' => 'Job / Tankhwah Tax Return',
                'icon' => '💼',
                'fee' => 3000.00,
                'currency' => 'Rs.',
                'turnaround' => '1 - 2 Working Days',
                'popular' => true,
                'is_active' => true,
                'desc' => 'Mulazmeen ke liye annual FBR return filing, tax refund claims, aur Active Taxpayer List (ATL) status.',
                'requirements' => [
                    [
                        'label' => 'Color copy of CNIC',
                        'type' => 'file',
                        'file_types' => ['jpg', 'jpeg', 'png', 'pdf'],
                        'required' => true,
                    ],
                    [
                        'label' => 'Annual Salary Certificate / Payslips (July to June)',
                        'type' => 'file',
                        'file_types' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
                        'required' => true,
                    ],
                    [
                        'label' => 'Bank Account Statement (as of 30th June)',
                        'type' => 'file',
                        'file_types' => ['pdf'],
                        'required' => false,
                    ],
                    [
                        'label' => 'Mobile Tax Deduction Certificate',
                        'type' => 'file',
                        'file_types' => ['jpg', 'jpeg', 'png', 'pdf'],
                        'required' => false,
                    ],
                ],
                'sort_order' => 3,
            ],
            [
                'code' => 'strn_sales_tax',
                'service_category_id' => $categoryModels['sales-tax-registration']->id,
                'category' => 'sales',
                'category_name' => 'Sales Tax Registration',
                'heading' => 'Sales Tax Registration (STRN)',
                'urdu' => 'FBR Sales Tax Number (GST)',
                'icon' => '📜',
                'fee' => 7500.00,
                'currency' => 'Rs.',
                'turnaround' => '3 - 5 Working Days',
                'popular' => false,
                'is_active' => true,
                'desc' => 'Manufacturers, Importers, Exporters, Wholesalers, aur Service providers ke liye Sales Tax Registration.',
                'requirements' => [
                    [
                        'label' => 'CNIC copies of all Directors / Partners / Owner',
                        'type' => 'file',
                        'file_types' => ['jpg', 'jpeg', 'png', 'pdf'],
                        'required' => true,
                    ],
                    [
                        'label' => 'Bank Maintenance Certificate with IBAN',
                        'type' => 'file',
                        'file_types' => ['jpg', 'jpeg', 'png', 'pdf'],
                        'required' => true,
                    ],
                    [
                        'label' => 'Office Premises Rent Agreement / Ownership',
                        'type' => 'file',
                        'file_types' => ['pdf', 'doc', 'docx'],
                        'required' => true,
                    ],
                    [
                        'label' => 'GPS Bio-metric photos of premises & meter',
                        'type' => 'file',
                        'file_types' => ['jpg', 'jpeg', 'png'],
                        'required' => true,
                    ],
                ],
                'sort_order' => 4,
            ],
            [
                'code' => 'secp_pvt_ltd',
                'service_category_id' => $categoryModels['company-registration']->id,
                'category' => 'company',
                'category_name' => 'Company Registration',
                'heading' => 'Private Limited Company Incorporation',
                'urdu' => 'SECP Pvt Ltd Company Registration',
                'icon' => '🏢',
                'fee' => 15000.00,
                'currency' => 'Rs.',
                'turnaround' => '3 - 5 Working Days',
                'popular' => true,
                'is_active' => true,
                'desc' => 'Securities and Exchange Commission of Pakistan (SECP) se Private Limited company incorporation, Name Reservation aur Certified MOA/AOA.',
                'requirements' => [
                    [
                        'label' => 'Three Proposed Company Names',
                        'type' => 'text',
                        'required' => true,
                    ],
                    [
                        'label' => 'Color copies of CNIC for all Directors',
                        'type' => 'file',
                        'file_types' => ['jpg', 'jpeg', 'png', 'pdf'],
                        'required' => true,
                    ],
                    [
                        'label' => 'Registered Office Address in Pakistan',
                        'type' => 'text',
                        'required' => true,
                    ],
                    [
                        'label' => 'Principal Line of Business description',
                        'type' => 'text',
                        'required' => true,
                    ],
                ],
                'sort_order' => 5,
            ],
        ];

        foreach ($services as $srv) {
            BusinessServiceItem::updateOrCreate(
                ['code' => $srv['code']],
                $srv
            );
        }

        // 3. Seed CMS Pages
        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => '<h2>About TaxFiler Advisory</h2><p>TaxFiler.pk is Pakistan\'s premier corporate advisory and digital tax consultancy firm. We simplify taxation, SECP corporate filings, trademark registration, and accounting for individuals, startups, and expanding enterprises.</p><p>Our dedicated team of chartered tax practitioners and corporate lawyers ensures 100% compliant, seamless, and timely filings with the Federal Board of Revenue (FBR) and Securities & Exchange Commission of Pakistan (SECP).</p>',
                'meta_title' => 'About Us - TaxFiler.pk Pakistan',
                'meta_description' => 'Learn about Pakistan\'s trusted digital tax consultancy and corporate advisory firm.',
                'show_in_header' => false,
                'show_in_footer' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'content' => '<h2>Terms of Service</h2><p>By engaging TaxFiler.pk for tax preparation, NTN registration, corporate filing, or advisory services, you authorize our certified practitioners to prepare and submit official applications based on the accurate data provided by you.</p><p>All client credentials and sensitive financial records are protected under strict 256-bit encryption and non-disclosure obligations.</p>',
                'meta_title' => 'Terms of Service - TaxFiler.pk',
                'meta_description' => 'Read our client terms of service and engagement guidelines.',
                'show_in_header' => false,
                'show_in_footer' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '<h2>Privacy Policy</h2><p>Your privacy and confidential taxpayer identity are paramount. We do not sell, rent, or disclose personal CNIC details, banking statements, or password information to any third parties except as required for official FBR and SECP filing procedures.</p>',
                'meta_title' => 'Privacy Policy - TaxFiler.pk',
                'meta_description' => 'Our commitment to protecting your taxpayer identity and financial records.',
                'show_in_header' => false,
                'show_in_footer' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($pages as $pg) {
            Page::updateOrCreate(['slug' => $pg['slug']], $pg);
        }

        // 4. Seed Dynamic Menus
        $headerMenus = [
            ['location' => 'header', 'title' => 'Home', 'url' => '/', 'sort_order' => 1],
            ['location' => 'header', 'title' => 'Services', 'url' => '/services', 'sort_order' => 2],
            ['location' => 'header', 'title' => 'Track Application', 'url' => '/track', 'sort_order' => 3],
            ['location' => 'header', 'title' => 'Contact Us', 'url' => '/contact', 'sort_order' => 4],
        ];

        foreach ($headerMenus as $hm) {
            MenuItem::updateOrCreate(
                ['location' => $hm['location'], 'title' => $hm['title']],
                $hm
            );
        }

        $footerMenus = [
            ['location' => 'footer', 'title' => 'Corporate Services', 'url' => '/services', 'sort_order' => 1],
            ['location' => 'footer', 'title' => 'Application Tracker', 'url' => '/track', 'sort_order' => 2],
            ['location' => 'footer', 'title' => 'About Us', 'url' => '/page/about-us', 'sort_order' => 3],
            ['location' => 'footer', 'title' => 'Terms of Service', 'url' => '/page/terms-of-service', 'sort_order' => 4],
            ['location' => 'footer', 'title' => 'Privacy Policy', 'url' => '/page/privacy-policy', 'sort_order' => 5],
        ];

        foreach ($footerMenus as $fm) {
            MenuItem::updateOrCreate(
                ['location' => $fm['location'], 'title' => $fm['title']],
                $fm
            );
        }

        // 5. Seed Central App Settings (Branding, Gateways, Email, Firebase)
        $defaultSettings = [
            // Branding
            'firm_name' => 'TaxFiler Advisory Practice',
            'firm_phone' => '0300-1234567',
            'firm_whatsapp' => '923001234567',
            'firm_email' => 'contact@taxfiler.pk',
            'firm_address' => 'Suite 402, Business Executive Heights, Gulberg III, Lahore, Pakistan',
            
            // Payment Gateways
            'bank_title' => 'Apex Tax Advisory Services',
            'bank_name' => 'Meezan Bank Limited',
            'bank_account_no' => '0101-0203040506',
            'bank_iban' => 'PK45MEZN0001234567890101',
            'raast_id' => '03001234567',
            'jazzcash_account' => '03001234567',
            'jazzcash_merchant_id' => 'MC12345',
            'jazzcash_password' => 'pass123',
            'jazzcash_salt' => 'salt123',
            
            // Mail SMTP
            'mail_host' => 'smtp.mailtrap.io',
            'mail_port' => '2525',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'support@taxfiler.pk',
            'mail_from_name' => 'TaxFiler Support',

            // Firebase / Google Social Login
            'google_client_id' => '',
            'google_client_secret' => '',
            'firebase_project_id' => '',
        ];

        foreach ($defaultSettings as $key => $val) {
            $group = match(true) {
                str_starts_with($key, 'firm_') => 'branding',
                str_starts_with($key, 'bank_') || str_starts_with($key, 'raast_') || str_starts_with($key, 'jazzcash_') => 'payment',
                str_starts_with($key, 'mail_') => 'mail',
                str_starts_with($key, 'google_') || str_starts_with($key, 'firebase_') => 'firebase',
                default => 'general',
            };

            AppSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $val, 'group' => $group]
            );
        }
    }
}

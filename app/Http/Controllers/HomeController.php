<?php

namespace App\Http\Controllers;

use App\Models\BusinessServiceItem;
use App\Models\Consultancy;
use App\Models\Faq;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the dynamic landing page with rich SEO metadata
     */
    public function index()
    {
        $consultancy = null;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('consultancies')) {
                $consultancy = Consultancy::first();
            }
        } catch (\Throwable $e) {}

        $firmName = setting('firm_name') ?? ($consultancy?->name ?? 'Apex Tax Advisory');
        $firmPhone = setting('firm_phone') ?? ($consultancy?->phone ?? '0300-1234567');
        $firmWhatsapp = setting('firm_whatsapp') ?? '923001234567';
        $firmEmail = setting('firm_email') ?? ($consultancy?->email ?? 'info@taxadvisory.pk');
        $firmAddress = setting('firm_address') ?? ($consultancy?->address ?? 'Suite 402, Business Executive Heights, Gulberg III, Lahore, Pakistan');

        // Dynamic Categories
        $categories = collect();
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('service_categories')) {
                $categories = ServiceCategory::where('is_active', true)
                    ->orderBy('sort_order')
                    ->get();
            }
        } catch (\Throwable $e) {}

        // Dynamic Services
        $services = collect();
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('business_services')) {
                $services = BusinessServiceItem::where('is_active', true)
                    ->orderBy('sort_order')
                    ->get();
            }
        } catch (\Throwable $e) {}

        // Fallback to ServiceRequest catalog if table unpopulated
        if ($services->isEmpty()) {
            $catalog = ServiceRequest::getCatalog();
            $services = collect(array_values($catalog))->map(function ($item) {
                return (object) [
                    'code' => $item['code'],
                    'category' => $item['category'],
                    'category_name' => $item['category_name'],
                    'heading' => $item['heading'],
                    'urdu' => $item['urdu'] ?? null,
                    'icon' => $item['icon'] ?? '💼',
                    'fee' => $item['fee'] ?? 0,
                    'currency' => $item['currency'] ?? 'PKR',
                    'turnaround' => $item['turnaround'] ?? '1 - 2 Days',
                    'popular' => $item['popular'] ?? false,
                    'desc' => $item['desc'] ?? '',
                    'requirements' => $item['requirements'] ?? [],
                    'form_fields' => $item['form_fields'] ?? [],
                ];
            });
        }

        // Dynamic FAQs
        $faqs = collect();
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('faqs')) {
                $faqs = Faq::where('is_active', true)
                    ->orderBy('sort_order')
                    ->take(8)
                    ->get();
            }
        } catch (\Throwable $e) {}

        // SEO Metadata Object (Fully dynamic from Admin SEO & Marketing settings)
        $metaTitle = setting('seo_meta_title') ?: "{$firmName} | Certified Tax Consultant & Corporate Advisory Services Pakistan";
        $metaDescription = setting('seo_meta_description') ?: "Fastest online Tax Filing in Pakistan. Individual & Business NTN Registration, Active Taxpayer List (ATL) verification, SECP Company Incorporation, Sales Tax (STRN), and USA LLC setup by certified tax advocates.";
        $metaKeywords = setting('seo_meta_keywords') ?: "tax return filing pakistan, ntn registration fbr, atl status check, fbr iris login, secp company registration, strn registration, sales tax consultant, trademark registration ipo, usa llc pakistan, befiler alternative, tax consultant lahore karachi islamabad";
        $canonicalUrl = setting('seo_canonical_url') ?: route('home');
        $robotsMeta = setting('seo_robots') ?: "index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1";
        
        $ogTitle = setting('seo_og_title') ?: $metaTitle;
        $ogDescription = setting('seo_og_description') ?: $metaDescription;
        $ogImage = setting('seo_og_image');
        if ($ogImage && !str_starts_with($ogImage, 'http')) {
            $ogImage = asset('storage/' . $ogImage);
        }

        $twitterHandle = setting('seo_twitter_handle') ?: '@TaxFilerPk';
        $twitterCard = setting('seo_twitter_card') ?: 'summary_large_image';

        // Schema.org JSON-LD Structured Data
        $businessType = setting('schema_business_type') ?: 'TaxConsultant';
        $priceRange = setting('schema_price_range') ?: 'PKR';
        $areaServed = setting('schema_area_served') ?: 'Pakistan';

        $schemaGraph = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => $businessType,
                    '@id' => $canonicalUrl . '#organization',
                    'name' => $firmName,
                    'url' => $canonicalUrl,
                    'telephone' => $firmPhone,
                    'email' => $firmEmail,
                    'priceRange' => $priceRange,
                    'areaServed' => $areaServed,
                    'address' => [
                        '@type' => 'PostalAddress',
                        'streetAddress' => $firmAddress,
                        'addressCountry' => 'PK',
                    ],
                    'openingHours' => 'Mo,Tu,We,Th,Fr,Sa 09:00-18:00',
                    'description' => $metaDescription,
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => $canonicalUrl . '#website',
                    'url' => $canonicalUrl,
                    'name' => $firmName,
                    'description' => $metaDescription,
                    'publisher' => ['@id' => $canonicalUrl . '#organization'],
                ],
            ],
        ];

        if ($faqs->isNotEmpty()) {
            $faqItems = [];
            foreach ($faqs as $faq) {
                $faqItems[] = [
                    '@type' => 'Question',
                    'name' => $faq->question,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => strip_tags($faq->answer),
                    ],
                ];
            }
            $schemaGraph['@graph'][] = [
                '@type' => 'FAQPage',
                '@id' => $canonicalUrl . '#faq',
                'mainEntity' => $faqItems,
            ];
        }

        return view('welcome', compact(
            'consultancy',
            'firmName',
            'firmPhone',
            'firmWhatsapp',
            'firmEmail',
            'firmAddress',
            'categories',
            'services',
            'faqs',
            'metaTitle',
            'metaDescription',
            'metaKeywords',
            'canonicalUrl',
            'robotsMeta',
            'ogTitle',
            'ogDescription',
            'ogImage',
            'twitterHandle',
            'twitterCard',
            'schemaGraph'
        ));
    }
}

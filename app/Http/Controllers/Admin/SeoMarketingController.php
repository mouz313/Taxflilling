<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Consultancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SeoMarketingController extends Controller
{
    /**
     * Display the SEO & Marketing configuration console.
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'seo');

        $consultancy = Consultancy::first();
        $firmName = setting('firm_name', $consultancy?->name ?? 'TaxFiler Pakistan');

        // All saved settings as key-value pairs
        $settings = AppSetting::all()->pluck('value', 'key')->toArray();

        // SEO Health / Readiness Checklist
        $checklist = [
            'meta_title' => !empty($settings['seo_meta_title'] ?? null),
            'meta_description' => !empty($settings['seo_meta_description'] ?? null),
            'meta_keywords' => !empty($settings['seo_meta_keywords'] ?? null),
            'og_image' => !empty($settings['seo_og_image'] ?? null),
            'google_analytics' => !empty($settings['marketing_ga4_id'] ?? null) || !empty($settings['marketing_gtm_id'] ?? null),
            'meta_pixel' => !empty($settings['marketing_meta_pixel_id'] ?? null),
            'verification' => !empty($settings['seo_google_verification'] ?? null),
            'schema_ready' => !empty($settings['schema_business_type'] ?? null),
        ];

        $completedCount = count(array_filter($checklist));
        $totalChecks = count($checklist);
        $readinessScore = round(($completedCount / $totalChecks) * 100);

        return view('admin.cms.seo.index', compact(
            'tab',
            'settings',
            'firmName',
            'checklist',
            'readinessScore'
        ));
    }

    /**
     * Save or update SEO & Marketing settings.
     */
    public function update(Request $request)
    {
        $section = $request->input('section', 'seo');

        switch ($section) {
            case 'seo':
                $validated = $request->validate([
                    'seo_meta_title' => ['required', 'string', 'max:150'],
                    'seo_meta_description' => ['required', 'string', 'max:320'],
                    'seo_meta_keywords' => ['nullable', 'string', 'max:500'],
                    'seo_robots' => ['required', 'string', Rule::in(['index, follow', 'noindex, nofollow', 'index, nofollow', 'noindex, follow'])],
                    'seo_canonical_url' => ['nullable', 'url', 'max:255'],
                    'seo_google_verification' => ['nullable', 'string', 'max:255'],
                    'seo_bing_verification' => ['nullable', 'string', 'max:255'],
                ]);

                foreach ($validated as $key => $val) {
                    AppSetting::set($key, $val ?? '', 'seo');
                }

                return redirect()->route('admin.cms.seo.index', ['tab' => 'seo'])
                    ->with('status', 'SEO Meta Tags & Search Console verification settings saved successfully!');

            case 'social':
                $validated = $request->validate([
                    'seo_og_title' => ['nullable', 'string', 'max:150'],
                    'seo_og_description' => ['nullable', 'string', 'max:320'],
                    'seo_og_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                    'seo_og_image_url' => ['nullable', 'url', 'max:500'],
                    'seo_twitter_card' => ['required', 'string', 'in:summary_large_image,summary'],
                    'seo_twitter_handle' => ['nullable', 'string', 'max:100'],
                    'seo_facebook_url' => ['nullable', 'url', 'max:255'],
                    'seo_linkedin_url' => ['nullable', 'url', 'max:255'],
                    'seo_youtube_url' => ['nullable', 'url', 'max:255'],
                ]);

                if ($request->hasFile('seo_og_image_file')) {
                    $existingOg = AppSetting::get('seo_og_image');
                    if ($existingOg && !str_starts_with($existingOg, 'http') && Storage::disk('public')->exists($existingOg)) {
                        Storage::disk('public')->delete($existingOg);
                    }
                    $path = $request->file('seo_og_image_file')->store('seo', 'public');
                    AppSetting::set('seo_og_image', $path, 'seo');
                } elseif (!empty($validated['seo_og_image_url'])) {
                    AppSetting::set('seo_og_image', $validated['seo_og_image_url'], 'seo');
                }

                unset($validated['seo_og_image_file'], $validated['seo_og_image_url']);

                foreach ($validated as $key => $val) {
                    AppSetting::set($key, $val ?? '', 'seo');
                }

                return redirect()->route('admin.cms.seo.index', ['tab' => 'social'])
                    ->with('status', 'Open Graph & Social Media marketing preview saved successfully!');

            case 'tracking':
                $validated = $request->validate([
                    'marketing_ga4_id' => ['nullable', 'string', 'max:50'],
                    'marketing_gtm_id' => ['nullable', 'string', 'max:50'],
                    'marketing_meta_pixel_id' => ['nullable', 'string', 'max:50'],
                    'marketing_tiktok_pixel_id' => ['nullable', 'string', 'max:50'],
                    'marketing_google_ads_id' => ['nullable', 'string', 'max:50'],
                ]);

                // Sanitize IDs (trim whitespace, remove unwanted quotes)
                foreach ($validated as $key => $val) {
                    $cleaned = trim((string) $val);
                    AppSetting::set($key, $cleaned, 'marketing');
                }

                return redirect()->route('admin.cms.seo.index', ['tab' => 'tracking'])
                    ->with('status', 'Ad Pixels & Analytics Tracking IDs saved successfully!');

            case 'schema':
                $validated = $request->validate([
                    'schema_business_type' => ['required', 'string', 'in:TaxConsultant,AccountingService,LegalService,FinancialService'],
                    'schema_price_range' => ['nullable', 'string', 'max:100'],
                    'schema_area_served' => ['nullable', 'string', 'max:255'],
                    'schema_geo_latitude' => ['nullable', 'numeric'],
                    'schema_geo_longitude' => ['nullable', 'numeric'],
                ]);

                foreach ($validated as $key => $val) {
                    AppSetting::set($key, $val ?? '', 'schema');
                }

                return redirect()->route('admin.cms.seo.index', ['tab' => 'schema'])
                    ->with('status', 'Local SEO & Schema.org Rich Snippets configuration saved!');

            case 'scripts':
                $validated = $request->validate([
                    'marketing_custom_head_scripts' => ['nullable', 'string'],
                    'marketing_custom_body_scripts' => ['nullable', 'string'],
                ]);

                AppSetting::set('marketing_custom_head_scripts', $validated['marketing_custom_head_scripts'] ?? '', 'marketing');
                AppSetting::set('marketing_custom_body_scripts', $validated['marketing_custom_body_scripts'] ?? '', 'marketing');

                return redirect()->route('admin.cms.seo.index', ['tab' => 'scripts'])
                    ->with('status', 'Custom Header & Body marketing scripts updated successfully!');

            default:
                return redirect()->route('admin.cms.seo.index')->with('status', 'Settings updated.');
        }
    }
}

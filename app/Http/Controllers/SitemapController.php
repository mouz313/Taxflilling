<?php

namespace App\Http\Controllers;

use App\Models\BusinessServiceItem;
use App\Models\Page;
use App\Models\ServiceCategory;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate dynamic XML sitemap for search engines.
     */
    public function index(): Response
    {
        $urls = [];

        // 1. Core Landing & Public Hubs
        $urls[] = [
            'loc' => route('home'),
            'lastmod' => now()->startOfDay()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ];

        $urls[] = [
            'loc' => route('services.index'),
            'lastmod' => now()->startOfWeek()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.9',
        ];

        $urls[] = [
            'loc' => route('track'),
            'lastmod' => now()->startOfMonth()->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.8',
        ];

        $urls[] = [
            'loc' => route('faq'),
            'lastmod' => now()->startOfMonth()->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.7',
        ];

        $urls[] = [
            'loc' => route('contact'),
            'lastmod' => now()->startOfMonth()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.7',
        ];

        // 2. Active Service Categories
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('service_categories')) {
                $categories = ServiceCategory::where('is_active', true)->get();
                foreach ($categories as $cat) {
                    $urls[] = [
                        'loc' => route('services.index', ['category' => $cat->slug]),
                        'lastmod' => $cat->updated_at ? $cat->updated_at->toAtomString() : now()->toAtomString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.8',
                    ];
                }
            }
        } catch (\Throwable $e) {}

        // 3. Dynamic CMS Pages (Terms, Privacy, About, Guides)
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('pages')) {
                $pages = Page::where('is_active', true)->get();
                foreach ($pages as $page) {
                    $urls[] = [
                        'loc' => route('pages.show', $page->slug),
                        'lastmod' => $page->updated_at ? $page->updated_at->toAtomString() : now()->toAtomString(),
                        'changefreq' => 'monthly',
                        'priority' => '0.6',
                    ];
                }
            }
        } catch (\Throwable $e) {}

        $xml = view('sitemap', compact('urls'))->render();

        return response($xml, 200, [
            'Content-Type' => 'text/xml; charset=utf-8',
        ]);
    }
}

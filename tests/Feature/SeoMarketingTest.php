<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Consultancy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMarketingTest extends TestCase
{
    use RefreshDatabase;

    private User $companyUser;
    private User $clientUser;

    protected function setUp(): void
    {
        parent::setUp();

        $consultancy = Consultancy::create([
            'name' => 'Apex Tax Advisory',
            'slug' => 'apex-tax-advisory',
            'phone' => '+92 300 1234567',
            'whatsapp' => '923001234567',
            'email' => 'info@taxadvisory.pk',
            'address' => 'Suite 402, Gulberg III, Lahore',
        ]);

        $this->companyUser = User::create([
            'name' => 'Senior Partner',
            'email' => 'partner@taxadvisory.pk',
            'password' => bcrypt('password'),
            'role' => 'company',
            'consultancy_id' => $consultancy->id,
        ]);

        $this->clientUser = User::create([
            'name' => 'Client Taxpayer',
            'email' => 'client@example.pk',
            'password' => bcrypt('password'),
            'role' => 'client',
        ]);
    }

    public function test_guest_and_client_cannot_access_seo_admin(): void
    {
        $guestResponse = $this->get(route('admin.cms.seo.index'));
        $guestResponse->assertRedirect(route('login'));

        $clientResponse = $this->actingAs($this->clientUser)->get(route('admin.cms.seo.index'));
        $clientResponse->assertStatus(403);
    }

    public function test_admin_can_view_seo_marketing_console(): void
    {
        $response = $this->actingAs($this->companyUser)->get(route('admin.cms.seo.index'));
        $response->assertStatus(200);
        $response->assertSee('Search Engine Optimization &amp; Ad Tracking', false);
        $response->assertSee('Google Search SEO');
        $response->assertSee('WhatsApp & Social Share');
        $response->assertSee('Ad Pixels & Analytics');
        $response->assertSee('Local SEO & Schema');
    }

    public function test_admin_can_update_seo_meta_tags(): void
    {
        $payload = [
            'section' => 'seo',
            'seo_meta_title' => 'Top Tax Consultants in Pakistan | FBR IRIS 2.0',
            'seo_meta_description' => 'Fastest verified tax filing service by certified advocates in Lahore, Karachi and Islamabad.',
            'seo_meta_keywords' => 'tax return, ntn registration, fbr iris, secp',
            'seo_robots' => 'index, follow',
            'seo_canonical_url' => 'https://taxadvisory.pk',
            'seo_google_verification' => 'google-token-xyz-123',
            'seo_bing_verification' => 'bing-token-456',
        ];

        $response = $this->actingAs($this->companyUser)->post(route('admin.cms.seo.update'), $payload);
        $response->assertRedirect(route('admin.cms.seo.index', ['tab' => 'seo']));
        $response->assertSessionHas('status');

        $this->assertEquals('Top Tax Consultants in Pakistan | FBR IRIS 2.0', AppSetting::get('seo_meta_title'));
        $this->assertEquals('google-token-xyz-123', AppSetting::get('seo_google_verification'));
    }

    public function test_admin_can_update_social_and_open_graph(): void
    {
        $payload = [
            'section' => 'social',
            'seo_og_title' => 'File Taxes with High Court Advocates',
            'seo_og_description' => 'Instant NTN and FBR returns with 100% active filer guarantee.',
            'seo_twitter_card' => 'summary_large_image',
            'seo_twitter_handle' => '@ApexTaxPk',
            'seo_facebook_url' => 'https://facebook.com/apextaxpk',
        ];

        $response = $this->actingAs($this->companyUser)->post(route('admin.cms.seo.update'), $payload);
        $response->assertRedirect(route('admin.cms.seo.index', ['tab' => 'social']));

        $this->assertEquals('File Taxes with High Court Advocates', AppSetting::get('seo_og_title'));
        $this->assertEquals('@ApexTaxPk', AppSetting::get('seo_twitter_handle'));
    }

    public function test_admin_can_update_tracking_ad_pixels(): void
    {
        $payload = [
            'section' => 'tracking',
            'marketing_ga4_id' => 'G-ABC1234567',
            'marketing_gtm_id' => 'GTM-XYZ7890',
            'marketing_meta_pixel_id' => '987654321098765',
            'marketing_tiktok_pixel_id' => 'C1234567890TT',
            'marketing_google_ads_id' => 'AW-11223344',
        ];

        $response = $this->actingAs($this->companyUser)->post(route('admin.cms.seo.update'), $payload);
        $response->assertRedirect(route('admin.cms.seo.index', ['tab' => 'tracking']));

        $this->assertEquals('G-ABC1234567', AppSetting::get('marketing_ga4_id'));
        $this->assertEquals('987654321098765', AppSetting::get('marketing_meta_pixel_id'));
    }

    public function test_admin_can_update_local_seo_and_schema(): void
    {
        $payload = [
            'section' => 'schema',
            'schema_business_type' => 'TaxConsultant',
            'schema_price_range' => 'PKR 2,500 - 45,000',
            'schema_area_served' => 'Pakistan (Karachi, Lahore, Islamabad)',
            'schema_geo_latitude' => '31.5204',
            'schema_geo_longitude' => '74.3587',
        ];

        $response = $this->actingAs($this->companyUser)->post(route('admin.cms.seo.update'), $payload);
        $response->assertRedirect(route('admin.cms.seo.index', ['tab' => 'schema']));

        $this->assertEquals('TaxConsultant', AppSetting::get('schema_business_type'));
        $this->assertEquals('PKR 2,500 - 45,000', AppSetting::get('schema_price_range'));
    }

    public function test_xml_sitemap_returns_valid_xml(): void
    {
        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $this->assertStringContainsString('text/xml', $response->headers->get('Content-Type'));
        $response->assertSee('<urlset', false);
        $response->assertSee(route('home'), false);
        $response->assertSee(route('services.index'), false);
    }

    public function test_homepage_reflects_updated_seo_and_pixel_tags(): void
    {
        AppSetting::set('seo_meta_title', 'Custom SEO Title for Pakistan Tax Filer', 'seo');
        AppSetting::set('seo_meta_description', 'Custom meta description for testing landing page.', 'seo');
        AppSetting::set('marketing_meta_pixel_id', '999888777666', 'marketing');
        AppSetting::set('seo_google_verification', 'test-verification-code', 'seo');

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('<title>Custom SEO Title for Pakistan Tax Filer</title>', false);
        $response->assertSee('content="Custom meta description for testing landing page."', false);
        $response->assertSee('999888777666', false);
        $response->assertSee('test-verification-code', false);
    }
}

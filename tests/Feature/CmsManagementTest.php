<?php

namespace Tests\Feature;

use App\Models\BusinessServiceItem;
use App\Models\Consultancy;
use App\Models\Faq;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $companyUser;

    protected function setUp(): void
    {
        parent::setUp();

        $consultancy = Consultancy::create([
            'name' => 'TaxFiler Law Firm',
            'slug' => 'taxfiler-law-firm',
            'phone' => '+92 300 1234567',
            'whatsapp' => '923001234567',
            'email' => 'support@taxfiler.pk',
            'address' => 'Main Shahrah-e-Faisal, Karachi',
        ]);

        $this->companyUser = User::create([
            'name' => 'Admin Advocate',
            'email' => 'admin@taxfiler.pk',
            'password' => bcrypt('password'),
            'role' => 'company',
            'consultancy_id' => $consultancy->id,
        ]);
    }

    public function test_admin_can_view_cms_services_list(): void
    {
        $response = $this->actingAs($this->companyUser)->get(route('admin.cms.services.index'));
        $response->assertStatus(200);
        $response->assertSee('Business Services Catalog');
    }

    public function test_admin_can_create_new_business_service(): void
    {
        $payload = [
            'heading' => 'Custom SECP Filing Service',
            'urdu' => 'Khaas SECP Filing',
            'category' => 'company',
            'icon' => '🏢',
            'fee' => 18000,
            'currency' => 'Rs.',
            'turnaround' => '3 Days',
            'popular' => 1,
            'is_active' => 1,
            'desc' => 'Custom description for SECP filing.',
            'requirements_text' => "CNIC Copy\nRent Agreement",
        ];

        $response = $this->actingAs($this->companyUser)->post(route('admin.cms.services.store'), $payload);
        $response->assertRedirect(route('admin.cms.services.index'));

        $this->assertDatabaseHas('business_services', [
            'heading' => 'Custom SECP Filing Service',
            'fee' => 18000.00,
        ]);
    }

    public function test_admin_can_toggle_service_status(): void
    {
        $service = BusinessServiceItem::create([
            'code' => 'test_service',
            'category' => 'income',
            'category_name' => 'Income Tax Return',
            'heading' => 'Test Service',
            'fee' => 5000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->companyUser)->post(route('admin.cms.services.toggle', $service));
        $response->assertRedirect();

        $this->assertFalse($service->fresh()->is_active);
    }

    public function test_admin_can_create_and_manage_faq(): void
    {
        $payload = [
            'category' => 'salaried',
            'question' => 'How to declare freelance income?',
            'urdu_question' => 'Freelance income ka return kaise file karein?',
            'answer' => '<p>Export remittances under 100% tax exemption or 0.25% concession rate.</p>',
            'sort_order' => 10,
            'is_active' => 1,
        ];

        $response = $this->actingAs($this->companyUser)->post(route('admin.cms.faqs.store'), $payload);
        $response->assertRedirect(route('admin.cms.faqs.index'));

        $this->assertDatabaseHas('faqs', [
            'question' => 'How to declare freelance income?',
        ]);
    }

    public function test_admin_can_update_site_contact_content(): void
    {
        $payload = [
            'name' => 'TaxFiler Law Firm Updated',
            'phone' => '+92 300 9999999',
            'whatsapp' => '923009999999',
            'email' => 'contact@taxfiler.pk',
            'address' => 'Suite 500, Clifton, Karachi',
            'default_return_fee' => 3500,
            'default_ntn_fee' => 2000,
            'default_wealth_statement_fee' => 4000,
        ];

        $response = $this->actingAs($this->companyUser)->post(route('admin.cms.site.update'), $payload);
        $response->assertRedirect(route('admin.cms.site.index'));

        $this->assertDatabaseHas('consultancies', [
            'name' => 'TaxFiler Law Firm Updated',
            'phone' => '+92 300 9999999',
        ]);
    }
}

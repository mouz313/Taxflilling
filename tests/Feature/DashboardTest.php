<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Consultancy;
use App\Models\Query;
use App\Models\ServiceRequest;
use App\Models\TaxReturn;
use App\Models\TaxYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Consultancy $consultancy;
    protected TaxYear $taxYear2025;
    protected TaxYear $taxYear2026;
    protected User $companyUser;
    protected User $clientUser;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->consultancy = Consultancy::create([
            'name' => 'Apex Advisory',
            'slug' => 'apex',
            'default_return_fee' => 3000,
        ]);

        $this->taxYear2025 = TaxYear::create([
            'name' => '2025',
            'start_date' => '2024-07-01',
            'end_date' => '2025-06-30',
            'filing_deadline' => '2025-09-30',
            'is_active' => false,
        ]);

        $this->taxYear2026 = TaxYear::create([
            'name' => '2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'filing_deadline' => '2026-09-30',
            'is_active' => true,
        ]);

        $this->companyUser = User::create([
            'name' => 'Apex Admin',
            'email' => 'admin@apex.test',
            'password' => Hash::make('password123'),
            'role' => 'company',
            'consultancy_id' => $this->consultancy->id,
        ]);

        $this->clientUser = User::create([
            'name' => 'Tariq Mehmood',
            'email' => 'tariq@client.test',
            'password' => Hash::make('password123'),
            'role' => 'client',
            'consultancy_id' => $this->consultancy->id,
        ]);

        $this->client = Client::create([
            'user_id' => $this->clientUser->id,
            'consultancy_id' => $this->consultancy->id,
            'name' => 'Tariq Mehmood',
            'cnic' => '3520112345671',
            'email' => 'tariq@client.test',
            'phone' => '03001234567',
        ]);
    }

    private function createExtraClient(string $name, string $cnic, string $email): Client
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => 'client',
            'consultancy_id' => $this->consultancy->id,
        ]);

        return Client::create([
            'user_id' => $user->id,
            'consultancy_id' => $this->consultancy->id,
            'name' => $name,
            'cnic' => $cnic,
            'email' => $email,
            'phone' => '03001234568',
        ]);
    }

    public function test_company_dashboard_attention_list_includes_in_review_and_ready_to_file_and_excludes_draft(): void
    {
        $client2 = $this->createExtraClient('Bilal Ahmed', '3520112345672', 'bilal@client.test');
        $client3 = $this->createExtraClient('Usman Ali', '3520112345673', 'usman@client.test');

        // in_review return
        $returnInReview = TaxReturn::create([
            'consultancy_id' => $this->consultancy->id,
            'client_id' => $this->client->id,
            'tax_year_id' => $this->taxYear2026->id,
            'status' => 'in_review',
            'fee_amount' => 3000,
            'fee_status' => 'pending',
        ]);

        // ready_to_file return
        $returnReady = TaxReturn::create([
            'consultancy_id' => $this->consultancy->id,
            'client_id' => $client2->id,
            'tax_year_id' => $this->taxYear2026->id,
            'status' => 'ready_to_file',
            'fee_amount' => 3000,
            'fee_status' => 'paid',
        ]);

        // draft return
        $returnDraft = TaxReturn::create([
            'consultancy_id' => $this->consultancy->id,
            'client_id' => $client3->id,
            'tax_year_id' => $this->taxYear2026->id,
            'status' => 'draft',
            'fee_amount' => 3000,
            'fee_status' => 'pending',
        ]);

        $response = $this->actingAs($this->companyUser)->get(route('dashboard'));
        $response->assertOk();

        $attention = $response->viewData('attention');
        $this->assertNotNull($attention);

        $attentionIds = $attention->pluck('id')->all();
        $this->assertContains($returnInReview->id, $attentionIds);
        $this->assertContains($returnReady->id, $attentionIds);
        $this->assertNotContains($returnDraft->id, $attentionIds);
    }

    public function test_unread_client_query_increments_badge_count_on_return_in_attention_list(): void
    {
        $taxReturn = TaxReturn::create([
            'consultancy_id' => $this->consultancy->id,
            'client_id' => $this->client->id,
            'tax_year_id' => $this->taxYear2026->id,
            'status' => 'in_review',
            'fee_amount' => 3000,
            'fee_status' => 'pending',
        ]);

        // Create 2 unread queries from client
        Query::create([
            'tax_return_id' => $taxReturn->id,
            'user_id' => $this->clientUser->id,
            'message' => 'Maine salary slip attach kar di hai.',
            'is_read' => false,
        ]);
        Query::create([
            'tax_return_id' => $taxReturn->id,
            'user_id' => $this->clientUser->id,
            'message' => 'Filing kab tak hogi?',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->companyUser)->get(route('dashboard'));
        $response->assertOk();

        $attention = $response->viewData('attention');
        $item = $attention->firstWhere('id', $taxReturn->id);
        $this->assertNotNull($item);
        $this->assertEquals(2, $item->unread_queries_count);
    }

    public function test_revenue_calculation_only_counts_paid_returns_and_services(): void
    {
        $client2 = $this->createExtraClient('Zahid Hussain', '3520112345674', 'zahid@client.test');
        $client3 = $this->createExtraClient('Kashif Munir', '3520112345675', 'kashif@client.test');

        // Paid return
        TaxReturn::create([
            'consultancy_id' => $this->consultancy->id,
            'client_id' => $this->client->id,
            'tax_year_id' => $this->taxYear2026->id,
            'status' => 'filed',
            'fee_amount' => 5000,
            'fee_status' => 'paid',
        ]);

        // Pending fee return (non-draft)
        TaxReturn::create([
            'consultancy_id' => $this->consultancy->id,
            'client_id' => $client2->id,
            'tax_year_id' => $this->taxYear2026->id,
            'status' => 'in_review',
            'fee_amount' => 4000,
            'fee_status' => 'pending',
        ]);

        // Draft return (should not count in outstanding or collected)
        TaxReturn::create([
            'consultancy_id' => $this->consultancy->id,
            'client_id' => $client3->id,
            'tax_year_id' => $this->taxYear2026->id,
            'status' => 'draft',
            'fee_amount' => 2000,
            'fee_status' => 'pending',
        ]);

        // Paid service request
        ServiceRequest::create([
            'consultancy_id' => $this->consultancy->id,
            'user_id' => $this->clientUser->id,
            'applicant_name' => 'Tariq Mehmood',
            'applicant_phone' => '03001234567',
            'applicant_email' => 'tariq@client.test',
            'service_code' => 'ntn_individual',
            'service_name' => 'NTN Registration',
            'fee_amount' => 2500,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        // Pending service request
        ServiceRequest::create([
            'consultancy_id' => $this->consultancy->id,
            'user_id' => $this->clientUser->id,
            'applicant_name' => 'Tariq Mehmood',
            'applicant_phone' => '03001234567',
            'applicant_email' => 'tariq@client.test',
            'service_code' => 'gst_registration',
            'service_name' => 'GST Registration',
            'fee_amount' => 8000,
            'status' => 'submitted',
            'payment_status' => 'pending',
        ]);

        $response = $this->actingAs($this->companyUser)->get(route('dashboard'));
        $response->assertOk();

        $money = $response->viewData('money');
        $this->assertEquals(5000.0, $money['collected_returns']);
        $this->assertEquals(4000.0, $money['outstanding']);
        $this->assertEquals(2500.0, $money['collected_services']);
        $this->assertEquals(7500.0, $money['collected_returns'] + $money['collected_services']);
    }

    public function test_tax_year_filter_scopes_stats_and_attention_list(): void
    {
        // 2025 Return
        TaxReturn::create([
            'consultancy_id' => $this->consultancy->id,
            'client_id' => $this->client->id,
            'tax_year_id' => $this->taxYear2025->id,
            'status' => 'in_review',
            'fee_amount' => 2500,
            'fee_status' => 'paid',
        ]);

        // 2026 Return (same client, different tax year is allowed)
        TaxReturn::create([
            'consultancy_id' => $this->consultancy->id,
            'client_id' => $this->client->id,
            'tax_year_id' => $this->taxYear2026->id,
            'status' => 'ready_to_file',
            'fee_amount' => 3500,
            'fee_status' => 'paid',
        ]);

        // Filter by 2025
        $response2025 = $this->actingAs($this->companyUser)->get(route('dashboard', ['year' => $this->taxYear2025->id]));
        $response2025->assertOk();
        $stats2025 = $response2025->viewData('stats');
        $money2025 = $response2025->viewData('money');
        $this->assertEquals(1, $stats2025['in_review']);
        $this->assertEquals(0, $stats2025['ready_to_file']);
        $this->assertEquals(2500.0, $money2025['collected_returns']);

        // Default or Filter by 2026
        $response2026 = $this->actingAs($this->companyUser)->get(route('dashboard', ['year' => $this->taxYear2026->id]));
        $response2026->assertOk();
        $stats2026 = $response2026->viewData('stats');
        $money2026 = $response2026->viewData('money');
        $this->assertEquals(0, $stats2026['in_review']);
        $this->assertEquals(1, $stats2026['ready_to_file']);
        $this->assertEquals(3500.0, $money2026['collected_returns']);
    }

    public function test_client_cnic_is_masked(): void
    {
        $this->assertEquals('*****-*******-1', $this->client->masked_cnic);
    }

    public function test_client_dashboard_todo_list_shows_pending_docs_and_unread_queries(): void
    {
        $return = TaxReturn::create([
            'consultancy_id' => $this->consultancy->id,
            'client_id' => $this->client->id,
            'tax_year_id' => $this->taxYear2026->id,
            'status' => 'docs_pending',
            'fee_amount' => 3000,
            'fee_status' => 'pending',
        ]);

        // Consultant sent a message
        Query::create([
            'tax_return_id' => $return->id,
            'user_id' => $this->companyUser->id,
            'message' => 'Please provide bank statement for June 2026.',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->clientUser)->get(route('dashboard'));
        $response->assertOk();

        $todo = $response->viewData('todo');
        $this->assertNotEmpty($todo);

        $titles = collect($todo)->pluck('title')->all();
        $this->assertTrue(collect($titles)->contains(fn ($t) => str_contains($t, 'Consultant ka naya sawal')));
        $this->assertTrue(collect($titles)->contains('Documents chahiye'));
    }

    public function test_client_cannot_see_another_clients_service_requests_on_dashboard(): void
    {
        // Client 1 service request
        ServiceRequest::create([
            'consultancy_id' => $this->consultancy->id,
            'user_id' => $this->clientUser->id,
            'applicant_name' => 'Tariq Mehmood',
            'applicant_phone' => '03001234567',
            'applicant_email' => 'tariq@client.test',
            'service_code' => 'ntn_individual',
            'service_name' => 'Tariqs NTN',
            'fee_amount' => 2500,
            'status' => 'submitted',
            'payment_status' => 'paid',
        ]);

        // Client 2 and service request
        $otherUser = User::create([
            'name' => 'Farhan Qureshi',
            'email' => 'farhan@other.test',
            'password' => Hash::make('password123'),
            'role' => 'client',
            'consultancy_id' => $this->consultancy->id,
        ]);

        ServiceRequest::create([
            'consultancy_id' => $this->consultancy->id,
            'user_id' => $otherUser->id,
            'applicant_name' => 'Farhan Qureshi',
            'applicant_phone' => '03009876543',
            'applicant_email' => 'farhan@other.test',
            'service_code' => 'llc_incorporation',
            'service_name' => 'Farhans Secret Company',
            'fee_amount' => 15000,
            'status' => 'submitted',
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->clientUser)->get(route('dashboard'));
        $response->assertOk();

        $services = $response->viewData('services');
        $this->assertCount(1, $services);
        $this->assertEquals('Tariqs NTN', $services->first()->service_name);
        $response->assertDontSee('Farhans Secret Company');
    }

    public function test_wizard_step_10_does_not_save_plaintext_fbr_passwords(): void
    {
        $taxReturn = TaxReturn::create([
            'consultancy_id' => $this->consultancy->id,
            'client_id' => $this->client->id,
            'tax_year_id' => $this->taxYear2026->id,
            'status' => 'draft',
            'step_completed' => 9,
            'fee_amount' => 3000,
            'fee_status' => 'pending',
            'internal_notes' => null,
        ]);

        $response = $this->actingAs($this->clientUser)->post(route('wizard.filing.save', ['return' => $taxReturn, 'step' => 10]), [
            'consent_consultant_files' => '1',
            'fbr_password' => 'SuperSecretPass123!',
            'fbr_pin' => '9988',
        ]);

        $response->assertRedirect(route('wizard.filing', ['return' => $taxReturn, 'step' => 11]));

        $taxReturn->refresh();
        $notes = $taxReturn->internal_notes ?? '';

        $this->assertStringNotContainsString('SuperSecretPass123!', $notes);
        $this->assertStringNotContainsString('9988', $notes);
        $this->assertDatabaseHas('return_status_logs', [
            'tax_return_id' => $taxReturn->id,
            'notes' => 'Client ne consultant ko apni taraf se file karne ki ijazat di.',
        ]);
    }

    public function test_wizard_step_12_leaves_fee_status_as_pending(): void
    {
        $taxReturn = TaxReturn::create([
            'consultancy_id' => $this->consultancy->id,
            'client_id' => $this->client->id,
            'tax_year_id' => $this->taxYear2026->id,
            'status' => 'draft',
            'step_completed' => 11,
            'fee_amount' => 3000,
            'fee_status' => 'pending',
        ]);

        $response = $this->actingAs($this->clientUser)->post(route('wizard.filing.save', ['return' => $taxReturn, 'step' => 12]), [
            'payment_method' => 'bank_transfer',
            'transaction_reference' => 'TXN-998877',
        ]);

        $response->assertRedirect(route('dashboard'));

        $taxReturn->refresh();
        $this->assertEquals('in_review', $taxReturn->status);
        $this->assertEquals('pending', $taxReturn->fee_status);
        $this->assertDatabaseHas('return_status_logs', [
            'tax_return_id' => $taxReturn->id,
            'to_status' => 'in_review',
        ]);
    }

    public function test_company_can_confirm_fee_and_client_cannot(): void
    {
        $taxReturn = TaxReturn::create([
            'consultancy_id' => $this->consultancy->id,
            'client_id' => $this->client->id,
            'tax_year_id' => $this->taxYear2026->id,
            'status' => 'in_review',
            'fee_amount' => 3000,
            'fee_status' => 'pending',
        ]);

        // Client attempt should be forbidden (403)
        $clientResponse = $this->actingAs($this->clientUser)->post(route('returns.confirm-fee', $taxReturn));
        $clientResponse->assertForbidden();

        $taxReturn->refresh();
        $this->assertEquals('pending', $taxReturn->fee_status);

        // Company attempt should succeed
        $companyResponse = $this->actingAs($this->companyUser)->post(route('returns.confirm-fee', $taxReturn));
        $companyResponse->assertRedirect();
        $companyResponse->assertSessionHas('success');

        $taxReturn->refresh();
        $this->assertEquals('paid', $taxReturn->fee_status);

        // Verify status log was created in return_status_logs
        $this->assertDatabaseHas('return_status_logs', [
            'tax_return_id' => $taxReturn->id,
            'changed_by_user_id' => $this->companyUser->id,
            'notes' => 'Fee payment company ne confirm ki.',
        ]);
    }

    public function test_client_can_start_tax_return_from_dashboard(): void
    {
        $response = $this->actingAs($this->clientUser)->post(route('client.returns.start'));
        $createdReturn = TaxReturn::where('client_id', $this->client->id)->first();
        $this->assertNotNull($createdReturn);
        $response->assertRedirect(route('wizard.filing', ['return' => $createdReturn->id, 'step' => 1]));
    }

    public function test_client_can_delete_draft_return(): void
    {
        $return = TaxReturn::create([
            'consultancy_id' => $this->consultancy->id,
            'client_id' => $this->client->id,
            'tax_year_id' => $this->taxYear2026->id,
            'status' => 'draft',
            'fee_amount' => 3000,
            'fee_status' => 'pending',
        ]);

        $response = $this->actingAs($this->clientUser)->delete(route('client.returns.destroy', $return));
        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseMissing('tax_returns', ['id' => $return->id]);
    }

    public function test_client_cannot_delete_submitted_return(): void
    {
        $return = TaxReturn::create([
            'consultancy_id' => $this->consultancy->id,
            'client_id' => $this->client->id,
            'tax_year_id' => $this->taxYear2026->id,
            'status' => 'in_review',
            'fee_amount' => 3000,
            'fee_status' => 'pending',
        ]);

        $response = $this->actingAs($this->clientUser)->delete(route('client.returns.destroy', $return));
        $response->assertForbidden();

        $this->assertDatabaseHas('tax_returns', ['id' => $return->id]);
    }

    public function test_client_can_view_dedicated_my_returns_page(): void
    {
        $return = TaxReturn::create([
            'consultancy_id' => $this->consultancy->id,
            'client_id' => $this->client->id,
            'tax_year_id' => $this->taxYear2026->id,
            'status' => 'in_review',
            'fee_amount' => 3000,
            'fee_status' => 'paid',
        ]);

        $response = $this->actingAs($this->clientUser)->get(route('client.returns'));
        $response->assertOk();
        $response->assertViewIs('client.returns.index');
        $response->assertViewHas('returns');
        $response->assertSee('My Tax Returns');
        $response->assertSee('Tax Year 2026');
    }

    public function test_client_can_view_dedicated_my_applications_page(): void
    {
        ServiceRequest::create([
            'consultancy_id' => $this->consultancy->id,
            'user_id' => $this->clientUser->id,
            'applicant_name' => $this->clientUser->name,
            'applicant_phone' => '03001234567',
            'applicant_email' => $this->clientUser->email,
            'service_code' => 'individual_ntn',
            'service_name' => 'Individual NTN Registration',
            'fee_amount' => 1500,
            'status' => 'submitted',
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->clientUser)->get(route('client.applications'));
        $response->assertOk();
        $response->assertViewIs('client.applications.index');
        $response->assertViewHas('services');
        $response->assertSee('Corporate & NTN Applications');
        $response->assertSee('Individual NTN Registration');
    }
}


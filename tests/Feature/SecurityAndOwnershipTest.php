<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Consultancy;
use App\Models\ServiceRequest;
use App\Models\TaxReturn;
use App\Models\TaxYear;
use App\Models\User;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityAndOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected Consultancy $consultancy;
    protected TaxYear $taxYear;

    protected function setUp(): void
    {
        parent::setUp();

        $this->consultancy = Consultancy::create([
            'name' => 'Apex Advisory',
            'slug' => 'apex',
            'default_return_fee' => 2500,
        ]);

        $this->taxYear = TaxYear::create([
            'name' => '2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'filing_deadline' => '2026-09-30',
            'is_active' => true,
        ]);
    }

    public function test_unauthenticated_user_cannot_mark_service_request_paid(): void
    {
        $order = ServiceRequest::create([
            'consultancy_id' => $this->consultancy->id,
            'applicant_name' => 'Ali Khan',
            'applicant_phone' => '03001234567',
            'applicant_email' => 'ali@example.com',
            'service_code' => 'ntn_individual',
            'service_name' => 'NTN Registration',
            'fee_amount' => 2500,
            'status' => 'submitted',
            'payment_status' => 'pending',
        ]);

        // Attempting to post to old /services/{id}/pay should fail (route removed)
        $response = $this->post("/services/{$order->id}/pay", [
            'payment_method' => 'bank_transfer',
        ]);
        $response->assertStatus(404);

        // Attempting mark-paid without company authentication fails with 302/403
        $markPaidResponse = $this->post("/services/{$order->id}/mark-paid");
        $markPaidResponse->assertRedirect('/login');

        // Order remains pending
        $this->assertEquals('pending', $order->fresh()->payment_status);
    }

    public function test_submitting_payment_proof_does_not_mark_order_paid(): void
    {
        $order = ServiceRequest::create([
            'consultancy_id' => $this->consultancy->id,
            'applicant_name' => 'Ali Khan',
            'applicant_phone' => '03001234567',
            'applicant_email' => 'ali@example.com',
            'service_code' => 'ntn_individual',
            'service_name' => 'NTN Registration',
            'fee_amount' => 2500,
            'status' => 'submitted',
            'payment_status' => 'pending',
        ]);

        $response = $this->post("/services/{$order->id}/proof", [
            'payment_method' => 'bank_transfer',
            'payment_reference' => 'IBFT-123456',
        ]);

        $response->assertSessionHas('success');
        $freshOrder = $order->fresh();
        $this->assertEquals('pending_verification', $freshOrder->payment_status);
        $this->assertEquals('IBFT-123456', $freshOrder->payment_reference);
        $this->assertNotEquals('paid', $freshOrder->payment_status);
    }

    public function test_client_cannot_access_or_edit_another_clients_return_idor(): void
    {
        // Client A
        $userA = User::create([
            'name' => 'Client A',
            'email' => 'clienta@example.com',
            'phone' => '03001111111',
            'role' => 'client',
            'password' => Hash::make('Password123!'),
            'consultancy_id' => $this->consultancy->id,
            'is_active' => true,
        ]);
        $clientA = Client::create([
            'consultancy_id' => $this->consultancy->id,
            'user_id' => $userA->id,
            'name' => 'Client A',
            'cnic' => '42101-1111111-1',
            'phone' => '03001111111',
            'email' => 'clienta@example.com',
        ]);
        $returnA = TaxReturn::create([
            'consultancy_id' => $this->consultancy->id,
            'client_id' => $clientA->id,
            'tax_year_id' => $this->taxYear->id,
            'filing_type' => 'original',
            'status' => 'draft',
        ]);

        // Client B
        $userB = User::create([
            'name' => 'Client B',
            'email' => 'clientb@example.com',
            'phone' => '03002222222',
            'role' => 'client',
            'password' => Hash::make('Password123!'),
            'consultancy_id' => $this->consultancy->id,
            'is_active' => true,
        ]);
        $clientB = Client::create([
            'consultancy_id' => $this->consultancy->id,
            'user_id' => $userB->id,
            'name' => 'Client B',
            'cnic' => '42101-2222222-2',
            'phone' => '03002222222',
            'email' => 'clientb@example.com',
        ]);
        $returnB = TaxReturn::create([
            'consultancy_id' => $this->consultancy->id,
            'client_id' => $clientB->id,
            'tax_year_id' => $this->taxYear->id,
            'filing_type' => 'original',
            'status' => 'draft',
        ]);

        // Client A tries to view Client B's wizard
        $this->actingAs($userA);
        $wizardResponse = $this->get("/returns/{$returnB->id}/wizard");
        $wizardResponse->assertStatus(403);

        // Client A tries to add income to Client B's return
        $incomeResponse = $this->post("/returns/{$returnB->id}/income", [
            'source_type' => 'salary',
            'gross_amount' => 500000,
        ]);
        $incomeResponse->assertStatus(403);

        // Client A tries to download Client B's PDF dossier
        $pdfResponse = $this->get("/returns/{$returnB->id}/pdf");
        $pdfResponse->assertStatus(403);

        // Client A can view their own return wizard
        $ownResponse = $this->get("/returns/{$returnA->id}/wizard");
        $ownResponse->assertStatus(200);
    }

    public function test_payment_callback_rejects_invalid_hash_signature(): void
    {
        $order = ServiceRequest::create([
            'consultancy_id' => $this->consultancy->id,
            'applicant_name' => 'Test Applicant',
            'applicant_phone' => '03009999999',
            'applicant_email' => 'applicant@example.com',
            'service_code' => 'ntn_individual',
            'service_name' => 'NTN Registration',
            'fee_amount' => 2500,
            'status' => 'submitted',
            'payment_status' => 'pending',
        ]);

        // Attempting with wrong HMAC signature
        $response = $this->postJson('/payment/callback/jazzcash', [
            'pp_BillReference' => $order->tracking_number,
            'pp_ResponseCode' => '000',
            'pp_Amount' => 250000,
            'pp_TxnRefNo' => 'TXN-FAKE-123',
            'pp_SecureHash' => 'INVALID_HASH_VALUE_HERE',
        ]);

        $response->assertStatus(403);
        $this->assertEquals('pending', $order->fresh()->payment_status);
    }

    public function test_payment_callback_verifies_valid_hash_signature(): void
    {
        $order = ServiceRequest::create([
            'consultancy_id' => $this->consultancy->id,
            'applicant_name' => 'Test Applicant',
            'applicant_phone' => '03009999999',
            'applicant_email' => 'applicant@example.com',
            'service_code' => 'ntn_individual',
            'service_name' => 'NTN Registration',
            'fee_amount' => 2500,
            'status' => 'submitted',
            'payment_status' => 'pending',
        ]);

        $payload = [
            'pp_Amount' => '250000',
            'pp_BillReference' => $order->tracking_number,
            'pp_ResponseCode' => '000',
            'pp_TxnRefNo' => 'TXN-REAL-789',
        ];

        // Generate authentic hash matching PaymentGatewayService
        $salt = config('services.jazzcash.salt') ?? 'sandbox_salt_key_123';
        ksort($payload);
        $hashString = $salt;
        foreach ($payload as $k => $v) {
            if ($v !== '' && str_starts_with($k, 'pp_')) {
                $hashString .= '&' . $v;
            }
        }
        $payload['pp_SecureHash'] = strtoupper(hash_hmac('sha256', $hashString, $salt));

        $response = $this->postJson('/payment/callback/jazzcash', $payload);

        $response->assertStatus(200);
        $freshOrder = $order->fresh();
        $this->assertEquals('paid', $freshOrder->payment_status);
        $this->assertEquals('TXN-REAL-789', $freshOrder->payment_reference);
    }

    public function test_public_ajax_search_masks_pii(): void
    {
        ServiceRequest::create([
            'consultancy_id' => $this->consultancy->id,
            'applicant_name' => 'Kashif Mehmood',
            'applicant_phone' => '03001234567',
            'applicant_email' => 'kashif@example.com',
            'service_code' => 'secp_pvt_ltd',
            'service_name' => 'Company Registration',
            'fee_amount' => 15000,
            'status' => 'submitted',
            'payment_status' => 'pending',
        ]);

        $response = $this->getJson('/track/search?q=Kashif');
        $response->assertStatus(200);

        $results = $response->json('results');
        $this->assertNotEmpty($results);

        // Name, email, and phone must not be plain text
        $first = $results[0];
        $this->assertNotEquals('Kashif Mehmood', $first['applicant_name']);
        $this->assertStringContainsString('*', $first['applicant_name']);
        $this->assertNotEquals('kashif@example.com', $first['applicant_email']);
        $this->assertStringContainsString('*', $first['applicant_email']);
        $this->assertNotEquals('03001234567', $first['applicant_phone']);
        $this->assertStringContainsString('*', $first['applicant_phone']);
    }
}
